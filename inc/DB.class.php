<?php
(@include "../../../.sql.php") or die("<b>Ei pääse andmebaasi!</b>");


class DB {
    private $_tblPrefix = null;
    private $_sql = null;
    private $_sqlCloseBr = false;

    private $_mysqli = null;
    private $_stmt = null;
    private $_stmtResult = null;

    public $error = null;
    public $stmtError = null;


    function __construct($host = SQL_HOST, $user = SQL_USER, $password = SQL_PWD
                        , $database = SQL_DB, $table_prefix = SQL_TABLE_PREFIX) {
        $this->_tblPrefix = $table_prefix;
        $this->_mysqli = new mysqli($host, $user, $password, $database);
        $this->_mysqli->set_charset("utf8mb4");
        $this->error = $this->_mysqli->error;
    }
    function __destruct() {
        $this->close();
        if ($this->_mysqli) $this->_mysqli->close();
    }


    /**
     * Loob küsimärkide loetelu, vastavalt massiivi elementide arvule.
     *
     * @param   array  $inArr
     *
     * @return  string
     */
    function valuesToQM($inArr): string {
        return implode(', ', array_fill(0, count($inArr), '?')); //create question marks
    }

    /**
     * Teostab sama päringu uute andmetega.
     *
     * @param   array   $values  Andmed, mida esitatakse funktsioonile bind_param()
     * @param   string  $types   Andmete tüübid
     *
     * @return  self             Viide andmebaasi objektile
     */
    function execute($values = [], $types = null): self {
        $this->_execute($values, $types);
        return $this;
    }

    /**
     * Teostab SQL-päringu.
     *
     * @return  self  Viide andmebaasi objektile
     */
    function query ($sql, $values = [], $types = null): self {
        $this->close();  //~ Sulgeb vajadusel eelneva päringu

        if(!$values) {
            $this->_stmtResult = $this->_mysqli->query($sql); //Use non-prepared query if no values to bind for efficiency
        } else {
            if ($this->_stmt = $this->_mysqli->prepare($sql)) {
               $this->_execute($values, $types);
            } else {  //kui viga SQL päringus
                $this->error = $this->_mysqli->errno .' '. $this->_mysqli->error;
            }
            
        }
        return $this;
    }




    /* =========================================================================================
       Abimeetodid SQL päringu konstrueerimiseks (LINQ-i sarnases stiilis)
       =========================================================================================
    */

    // SQL päringu algused (kui midagi oli eelnevalt, asendab nüüd need uuega)
    function select($cols = []): self {
        $this->_sql = 'SELECT '. (!$cols ? '' : $this->_arrangeCols($cols));
        return $this;
    }
    function insert($table, $cols = []): self {
        $this->_sql = 'INSERT INTO `'. $this->_tblPrefix . trim($table) .'`';
        if ($cols) $this->_sql .= ' ('. $this->_arrangeCols($cols) .')';
        return $this;
    }
    function update($table): self {
        $this->_sql = 'UPDATE `'. $this->_tblPrefix . trim($table) .'`';
        return $this;
    }
    function delete(): self {
        $this->_sql = 'DELETE';
        return $this;
    }

    // Päringu täpsustused, tabeli valimine jms
    function distinct($cols = []): self {
        $this->_sql .= ' DISTINCT '. $this->_arrangeCols($cols);
        return $this;
    }
    function from($table): self {
        // Kui 'select'-ile pole veerunimed antud, siis lisab tärni automaatselt
        if (substr($this->_sql, -3) == 'CT ') $this->_sql .= '*';
        $this->_sql .= ' FROM `'. $this->_tblPrefix . trim($table) .'`';
        return $this;
    }
    function values($values): self {
        if (is_string($values)) $values = explode(',', $values);
        $this->_sql .= ' VALUES ('. $this->valuesToQM($values) .')';
        return $this;
    }
    function set($options): self {
        if (is_string($options)) $options = explode(',', $options);
        $this->_sql .= ' SET';

        if (is_array($options[0])) {  // kui muudetakse mitu veergu
            $r = [];
            foreach ($options as $opt) {
                if (is_string($opt)) $opt = explode('=', $opt);
                $r[] = $this->_equation($opt, '=');
            }
            $this->_sql .= ' '. implode(', ', $r);
        } else {
            $this->eq($options);
        }
        return $this;
    }

    // Päringud mitmest erinevast tabelist
    function join($table, $on = null): self {
        if ($on) $table = [$table, $on];
        $this->_join($table, 'i');  // vaikimisi INNER JOIN
        return $this;
    }
    function innerJoin($table, $on = null): self {
        if ($on) $table = [$table, $on];
        $this->_join($table, 'i');
        return $this;
    }
    function leftJoin($table, $on = null): self {
        if ($on) $table = [$table, $on];
        $this->_join($table, 'l');
        return $this;
    }
    function rightJoin($table, $on = null): self {
        if ($on) $table = [$table, $on];
        $this->_join($table, 'r');
        return $this;
    }
    function fullJoin($table, $on = null): self {
        if ($on) $table = [$table, $on];
        $this->_join($table, 'f');
        return $this;
    }
    function on($on1, $on2 = null): self {
        if ($on2) $on1 = [$on1, $on2];
        $this->_sql .= ' ON ';
        $this->eq($on1);
        return $this;
    }

    // Päringu tingimused (tühjad funktsioonid)
    function where(): self {
        $this->_sql .= ' WHERE';
        return $this;
    }
    function not(): self {
        $this->_sql .= ' NOT';
        return $this;
    }
    function and($openBracket = false): self {
        $this->_sql .= ' AND';
        if ($openBracket) $this->_sql .= ' (';  //TODO: Add multilevel support
        return $this;
    }
    function or($closeBracket = null): self {
        $this->_sql .= ' OR';
        $this->_sqlCloseBr = $closeBracket ?? true;
        return $this;
    }

    // Päringu võrdlustehted
    function like($col) {
        $this->_sql .= ' '. $this->_tblCol($col) .' LIKE ?';
        return $this;
    }
    function in($col, $values) {
        if (is_string($values)) $values = explode(',', $values);
        /*$r = [];
        foreach ($values as $val) {
            $r[] = "'". trim($val) ."'";
        }*/
        $this->_sql .= ' '. $this->_tblCol($col) .' IN ('. $this->valuesToQM($values) .')';
        return $this;
    }
    function notIn($col, $values) {
        if (is_string($values)) $values = explode(',', $values);
        /*$r = [];
        foreach ($values as $val) {
            $r[] = "'". trim($val) ."'";
        }*/
        $this->_sql .= ' '. $this->_tblCol($col) .' NOT IN ('. $this->valuesToQM($values) .')';
        return $this;
    }
    function between($col) {
        $this->_sql .= ' '. $this->_tblCol($col) .' BETWEEN ? AND ?';
        return $this;
    }
    function notBetween($col) {
        $this->_sql .= ' '. $this->_tblCol($col) .' NOT BETWEEN ? AND ?';
        return $this;
    }
    function isNull($col): self {
        $this->_sql .= ' '. $this->_tblCol($col) .' IS NULL' . $this->_closeBr();
        return $this;
    }
    function notNull($col): self {
        $this->_sql .= ' '. $this->_tblCol($col) .' IS NOT NULL' . $this->_closeBr();
        return $this;
    }
    function eq($col1, $col2 = null): self {
        if ($col2) $col1 = [$col1, $col2];
        $this->_sql .= ' '. $this->_equation($col1, '=');
        return $this;
    }
    function ne($col1, $col2 = null): self {
        if ($col2) $col1 = [$col1, $col2];
        $this->_sql .= ' '. $this->_equation($col1, '<>');
        return $this;
    }
    function lt($col1, $col2 = null): self {
        if ($col2) $col1 = [$col1, $col2];
        $this->_sql .= ' '. $this->_equation($col1, '<');
        return $this;
    }
    function lte($col1, $col2 = null): self {
        if ($col2) $col1 = [$col1, $col2];
        $this->_sql .= ' '. $this->_equation($col1, '<=');
        return $this;
    }
    function gt($col1, $col2 = null): self {
        if ($col2) $col1 = [$col1, $col2];
        $this->_sql .= ' '. $this->_equation($col1, '>');
        return $this;
    }
    function gte($col1, $col2 = null): self {
        if ($col2) $col1 = [$col1, $col2];
        $this->_sql .= ' '. $this->_equation($col1, '>=');
        return $this;
    }

    // Päringu tulemuste sorteerimine ja arvu piiramine
    function order($cond): self {
        if (is_string($cond)) $cond = [[$cond, false]];  // vaikimisi ASC
        if (!is_array($cond[0])) $cond = [$cond];
        $r = [];
        foreach ($cond as $con) {
            $desc = $con[1] ?? false;
            $r[] = $this->_tblCol($con[0]) . ($desc ? ' DESC' : ' ASC');
        }
        $this->_sql .= ' ORDER BY '. implode(', ', $r);
        return $this;
    }
    function limit() {
        $this->_sql .= 'LIMIT ?';
        return $this;
    }

    // Lõpuks teostab SQL päringu
    function q($values = [], $types = '') {
        return $this->query($this->_sql, $values, $types);
    }

    // Tagastab SQL stringi (kui tekib vajadus)
    function sql(): string {
        return $this->_sql;
    }




    /* =========================================================================================
       SQL-päringu tulemused, andmete toomine
       =========================================================================================
    */

    /**
     * Tagastab SQL-päringu tulemusel saadud ridade arv.
     *
     * @return  int Ridade arv
     */
    function numRows(): int {
        return $this->_stmtResult->num_rows;
    }

    /**
     * Tagastab SQL-päringu mõjutatud ridade arv.
     *
     * @return  int Mõjutatud ridade arv
     */
    function affectedRows(): int {
        return $this->_mysqli->affected_rows;
    }

    /**
     * Tagastab SQL-päringuga lisatud uue rea number.
     *
     * @return  int Uue rea ID
     */
    function insertId(): int {
        return $this->_mysqli->insert_id;
    }


    /**
     * Toob SQL-päringu tulemuse esimese rea andmed.
     *
     * @return  mixed  Esimese rea andmed
     */
    function fetch ($fetchType = 'assoc', $className = 'stdClass', $classParams = []) {
        $row = [];

        // Kui päringutulemusi ei ole, tagastab tühja massiivi
        if (!isset($this->_stmtResult)) return [];

        if (!in_array($fetchType, ['assoc', 'num', 'obj'])) {
            $fetchType = 'assoc';
        }

        if ($fetchType == 'num') {
            $row = $this->_stmtResult->fetch_row();
        } elseif ($fetchType == 'assoc') {
            $row = $this->_stmtResult->fetch_assoc();
        } else {
            if ($classParams) {
                $row = $this->_stmtResult->fetch_object($className, $classParams);
            } else {
                $row = $this->_stmtResult->fetch_object($className);
            }
        }

        return $row;
    }

    /**
     * Toob SQL-päringu tulemuse kõik read.
     *
     * @return  mixed
     */
    function fetchAll ($fetchType = 'assoc', $className = 'stdClass', $classParams = []): array {
        $arr = [];

        // Kui päringutulemusi ei ole, tagastab tühja massiivi
        if (!isset($this->_stmtResult)) return [];

        if (!in_array($fetchType, ['assoc', 'num', 'obj', 'keyPairArr', 'group'])) {
            $fetchType = 'assoc';
        }

        if ($fetchType == 'num') {
            $arr = $this->_stmtResult->fetch_all(MYSQLI_NUM);
        } elseif ($fetchType == 'assoc') {
            $arr = $this->_stmtResult->fetch_all(MYSQLI_ASSOC);
        } elseif ($fetchType == 'obj') {
            if ($classParams) {
                while ($row = $this->_stmtResult->fetch_object($className, $classParams)) { $arr[] = $row; }
            } else {
                while($row = $this->_stmtResult->fetch_object($className)) { $arr[] = $row; }
            }
        } else {
            $firstColName = $this->_stmtResult->fetch_field_direct(0)->name;
            while($row = $this->_stmtResult->fetch_assoc()) {
                $firstColVal = $row[$firstColName];
                unset($row[$firstColName]);

                if($fetchType === 'group') { $arr[$firstColVal][] = $row; }
                else { $arr[$firstColVal] = $row; }
            }
        }

        return $arr;
    }


    /**
     * Vabastab ressursid ja sulgeb päringu.
     *
     * @return  self  Viide andmebaasi objektile
     */
    function close(): self {
        if ($this->_stmtResult) $this->_stmtResult->free();
        if ($this->_stmt) $this->_stmt->close();
        $this->error = null;
        $this->stmtError = null;
        return $this;
    }




    /* =========================================================================================
       Privaatsed abimeetodid
       =========================================================================================
    */

    private function _getTypes($values) {
        $r = '';
        foreach ($values as $val) {
            if (is_string($val))    $r .= 's';
            elseif (is_float($val)) $r .= 'f';
            elseif (is_int($val))   $r .= 'i';
            else                    $r .= 'b';
        }
        return $r;
    }

    private function _execute($values, $types) {
        if (!is_array($values)) $values = [$values]; //Convert scalar to array
        //if(!$types) $types = str_repeat('s', count($values)); //String type for all variables if not specified
        if (!$types) $types = $this->_getTypes($values);

        $this->_stmt->bind_param($types, ...$values);
        $this->_stmt->execute();
        $this->_stmtResult = $this->_stmt->get_result();
        $this->stmtError = $this->_stmt->errno .' '. $this->_stmt->error;
    }

    // Korrastab tabeli- ja/või veerunimed ning lisab sql-jutumärgid
    private function _arrangeCols($cols): string {
        if (!$cols) return '';
        $result = [];
        if (is_string($cols)) $cols = explode(',', $cols);

        if (strpos($cols[0], '.')===false) {  // lihtne, ainult veerunimedega loetelu
            foreach ($cols as $col) {
                if (strpos($col, '(') !== false) $result[] = $this->_func(trim($col));
                else $result[] = '`'. trim($col) .'`';
            }
        } else {  // keerulisem SQL päring erinevatest tabelitest
            $cnt = [];
            $rslt = [];

            foreach ($cols as $col) {
                $c = explode('.', $col);
                $c[1] = trim($c[1]);
                $r = $this->_tblCol($c) .' AS `'. $c[1];

                // Kui veerunimi juba olemas, lisab numbri lõppu (al. 2-st)
                if (in_array($c[1], $rslt)) {
                    if (!isset($cnt[$c[1]])) $cnt[$c[1]] = 2;
                    $r .= $cnt[$c[1]]++;
                }
                $r .= '` ';
                $rslt[] = $c[1];  // jätab veerunime meelde, et vältida selle dubleerimist
                $result[] = $r;
            }
        }
        return implode(', ', $result);
    }
    private function _func($str): string {
        $p = strpos($str, '(');  //TODO: Add multilevel support
        return strtoupper(substr($str, 0, $p)) . substr($str, $p);
    }
    private function _tblCol($tc): string {
        if (is_string($tc)) $tc = explode('.', $tc);
        $tc[0] = trim($tc[0]);
        if ($tc[0] == '?') return '?'; // pole mõtet jätkata
        if (strpos($tc[0], '(') !== false) return $this->_func($tc[0]);  // funktsioon

        if (!isset($tc[1])) $r = '`'. trim($tc[0]) .'`';
        else $r = '`'. $this->_tblPrefix . trim($tc[0]) .'`.`'. trim($tc[1]) .'`';
        return $r;
    }
    private function _equation($cols, $eq): string {
        if (is_string($cols)) $cols = explode($eq, $cols);
        return $this->_tblCol($cols[0]) .' '. $eq .' '. $this->_tblCol($cols[1]) . $this->_closeBr();
    }
    private function _closeBr(): string {
        //TODO: Add multilevel support
        $br = '';
        if ($this->_sqlCloseBr) {
            $br .= ')';
            $this->_sqlCloseBr = false;
        }
        return $br;
    }
    // Abimeetod, mis väärtust ei tagasta
    private function _join($table, $type) {
        $jt = array('i'=>' INNER', 'l'=>' LEFT', 'r'=>'RIGHT', 'f'=>'FULL OUTER');
        $on = [];
        if (is_array($table)) {
            $on = $table[1];
            $table = $table[0];
        }
        if (is_string($on)) $on = explode('=', $on);
        $this->_sql .= $jt[$type] .' JOIN `'. $this->_tblPrefix . trim($table) .'`';
        if ($on) {
            $this->_sql .= ' ON ';
            $this->eq($on);
        }

    }
}
