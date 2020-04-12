<?php

/* ****************************************************************************
    "Privaatsed" abifunktsioonid kursuse/tegevuse salvestamiseks/lugemiseks
   ****************************************************************************
*/
function _saveCA($field, $table, $value) {
    $response = null;
    $errorMessage = null;
    $sql = "INSERT INTO vr20__study_${table} (${field}) VALUES (?)";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    //echo $db->error;
    $db->query($sql, [$value]);

    if ($db->affectedRows() != -1) {
        $response = 1;
    }
    else {
        $response = 0;
        $errorMessage = $db->stmtError;
    }

    return [$response, $errorMessage];
}


function _readCA($field, $table, $word, $outType) {
    // Kuna andmeid loetakse kahelt lehelt, aga ühel esitatakse rippmenüüs (valik)
    // ja teises hoopis loetelus, siis määrab siin vastavalt väljundirea alguse ja lõpu
    if ($outType == 'option') {
        $out1 = '<option value="';
        $out2 = '</option>';
    } else {
        $out1 = '<li class="list-group-item" data-'. substr($field, 0, 1) .'id="';
        $out2 = '</li>';
    }

    $response = null;
    $sql = "SELECT id, ${field} FROM vr20__study_${table} ORDER BY id";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    //echo $db->error;
    $result = $db->query($sql)->fetchAll();

    foreach ($result as $row) {
        $response .= $out1 . $row['id'] .'">'. $row[$field] . $out2 . "\n";
    }
    if ($response == null) {
        $response = $out1 .'0"><div class="alert alert-warning"'
                .'>Kahjuks '. $word .' puuduvad!'. $out2 .'</li>'. "\n";
    }

    return $response;
}


/* ****************************************************************************
    "Globaalsed" väljakutsutavad funktsioonid
   ****************************************************************************
*/


function saveCourse($course) {
    return _saveCA('course', 'courses', $course);
}

function saveActivity($activity) {
    return _saveCA('activity', 'activities', $activity);
}

function readCourses($outType = 'option') {
    return _readCA('course', 'courses', 'kursused', $outType);
}

function readActivities($outType = 'option') {
    return _readCA('activity', 'activities', 'tegevused', $outType);
}


function saveStudy($course, $activity, $time) {
    $response = null;
    $errorMessage = null;
    $sql = "INSERT INTO vr20__studylog (course, activity, `time`, userid) VALUES (?, ?, ?, ?)";

    //~ Loob andmebaasiühenduse ja teeb SQL päringu
    $db = new DB();
    //echo $db->error;
    $db->query($sql, [$course, $activity, $time, $_SESSION['userid']], 'iidi');

    if ($db->affectedRows() != -1) {
        $response = 1;
    }
    else {
        $response = 0;
        $errorMessage = $db->stmtError;
    }

    return [$response, $errorMessage];
}

function readStudy() {
    $response = null;
    $pr = TABLE_PREFIX;

    // Kuna kursused ja tegevused eraldi tabelites ja õppelogi tabelis võõrvõti,
    // siis tuleb id-väljad päringus vastavalt ühendada (käsuga INNER JOIN),
    // nüüd tuleb selekteerimisel lisada ette ka tabelinimi (eraldajaks punkt)
    $sql = "SELECT ${pr}studylog.time AS `time`, ${pr}studylog.day AS `day`, ${pr}studylog.userid AS `uid`,
                ${pr}study_courses.course AS course, ${pr}study_activities.activity AS activity
            FROM ${pr}studylog
            INNER JOIN ${pr}study_courses ON ${pr}studylog.course = ${pr}study_courses.id
            INNER JOIN ${pr}study_activities ON ${pr}studylog.activity = ${pr}study_activities.id
            WHERE ${pr}studylog.userid = ?
            ORDER BY `day` DESC";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    //echo $db->error;
    $result = $db->query($sql, $_SESSION['userid'], 'i')->fetchAll();

    foreach ($result as $row) {
        // Kuna ei soovi tabelis päeva järel kellaaega kuvada, siis teisendab selle;
        // tabelist tuleb aeg sõnena, seega tuleb enne teisendada numbriliseks ajaks
        // ja alles seejärel saab kasutada date() funktsiooni väljundi vormindamiseks
        $response .= "\n".'<tr><td>'. date("Y-m-d", strtotime($row['day']))
                .'</td><td>'. $row['course'] .'</td><td>'. $row['activity']
                .'</td><td class="text-right">'. number_format($row['time'], 2, ',', ' ')
                .' h</td></tr>';
    }

    if ($response == null) {
        $response = '<tr><td colspan="4"><div class="alert alert-warning">'
                .'Ühtegi õppimist ei ole veel lisatud!</div></td></tr>';
    }

    return $response;
}
