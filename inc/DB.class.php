<?php
/* Mõned juhendid alustamiseks:

https://websitebeaver.com/prepared-statements-in-php-mysqli-to-prevent-sql-injection
https://coderwall.com/p/mx468a/simple-mysql-wrapper-using-mysqli_
https://github.com/lodev09/php-mysqli-wrapper-class
*/
(@include "../../../.sql.php") or die("<b>Ei pääse andmebaasi!</b>");


class DB {
    private $_mysqli = null;
    private $_stmt = null;
    private $_stmtResult = null;

    public $error = null;
    public $stmtError = null;


    function __construct() {
        //~ Kuigi klassil ei peaks otse andmed sisestama, siis kursuse raames teen erandi (andmete varjamiseks)
        $this->_mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
        $this->_mysqli->set_charset("utf8mb4");
        $this->error = $this->_mysqli->error;
    }
    function __destruct() {
        $this->close();
        if ($this->_mysqli) $this->_mysqli->close();
    }


    private function _execute($values, $types) {
        if(!is_array($values)) $values = [$values]; //Convert scalar to array
        if(!$types) $types = str_repeat('s', count($values)); //String type for all variables if not specified

        $this->_stmt->bind_param($types, ...$values);
        $this->_stmt->execute();
        $this->_stmtResult = $this->_stmt->get_result();
        $this->stmtError = $this->_stmt->error;
    }


    function values($inArr): string {
        return implode(',', array_fill(0, count($inArr), '?')); //create question marks
    }

    function execute($values = [], $types = ''): self {
        $this->_execute($values, $types);

        return $this;
    }

    function query ($sql, $values = [], $types = ''): self {
        $this->close();  //~ Sulgeb vajadusel eelneva päringu

        if(!$values) {
            $this->_stmtResult = $this->_mysqli->query($sql); //Use non-prepared query if no values to bind for efficiency
        } else {
            $this->_stmt = $this->_mysqli->prepare($sql);
            $this->_execute($values, $types);
        }

        return $this;
    }

    function numRows(): int {
        return $this->_stmtResult->num_rows;
    }
    function affectedRows(): int {
        return $this->_mysqli->affected_rows;
    }
    function insertId(): int {
        return $this->_mysqli->insert_id;
    }

    function fetch ($fetchType = 'assoc', $className = 'stdClass', $classParams = []) {
        $row = [];

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

    function fetchAll ($fetchType = 'assoc', $className = 'stdClass', $classParams = []): array {
        $arr = [];

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
            while($row = $this->_stmtResult->fetch_assoc()) {
                $firstColVal = $row[$firstColName];
                unset($row[$firstColName]);

                if($fetchType === 'group') { $arr[$firstColVal][] = $row; }
                else { $arr[$firstColVal] = $row; }
            }
        }

        return $arr;
    }

    function close(): self {
        if ($this->_stmtResult) $this->_stmtResult->free();
        if ($this->_stmt) $this->_stmt->close();
        $this->error = null;
        $this->stmtError = null;
        return $this;
    }
}
