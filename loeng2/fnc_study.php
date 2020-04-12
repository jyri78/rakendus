<?php
(@include "../../../.sql.php") or die("<b>Ei pääse andmebaasi!</b>");


/* ****************************************************************************
    "Privaatsed" abifunktsioonid kursuse/tegevuse salvestamiseks/lugemiseks
   ****************************************************************************
*/
function _saveCA($field, $table, $value) {
    $response = null;
    $errorMessage = null;

    //~ Loob andmebaasiühenduse
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $conn->set_charset("utf8mb4");

    //~ Valmistab ette SQL päringu
    // Kuigi muutuja kasutamine otse päringus ei ole soovitatav, siis antud
    // juhul tegemist teises funktsioonis väljakutsutava funktsiooniga, kus
    // antakse ette väärtused (mitte kasutajalt!), siis erandina kasutab seda
    $stmt = $conn->prepare("INSERT INTO vr20__study_${table} (${field}) VALUES (?)");
    //echo $conn->error;

    //~ Seob päringuga tegelikud andmed
    //~ i - integer,  s - string,  d - decimal
    $stmt->bind_param("s", $value);

    if ($stmt->execute()) {
        $response = 1;
    }
    else {
        $response = 0;
        $errorMessage = $stmt->error;
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

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

    //~ Loob andmebaasiühenduse ja valmistab ette SQL päringu
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $conn->set_charset("utf8mb4");
    $stmt = $conn->prepare("SELECT id, ${field} FROM vr20__study_${table} ORDER BY id");
    //echo $conn->error;

    $stmt->bind_result($idFromDB, $courseFromDB);
    $stmt->execute();
    //if ($stmt->fetch())

    while ($stmt->fetch()) {
        $response .= $out1 . $idFromDB .'">'. $courseFromDB . $out2 . "\n";
    }
    if ($response == null) {
        $response = $out1 .'0"><div class="alert alert-warning"'
                .'>Kahjuks '. $word .' puuduvad!'. $out2 .'</li>'. "\n";
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return $response;
}


/* ****************************************************************************
    "Globaalsed" väljakutsutavad funktsioonid
   ****************************************************************************
*/

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


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

    //~ Loob andmebaasiühenduse
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $conn->set_charset("utf8mb4");

    //~ Valmistab ette SQL päringu
    $stmt = $conn->prepare(
            "INSERT INTO vr20__studylog (course, activity, `time`) VALUES (?, ?, ?)"
        );
    //echo $conn->error;

    //~ Seob päringuga tegelikud andmed
    //~ i - integer,  s - string,  d - decimal
    $stmt->bind_param("iid", $course, $activity, $time);

    if ($stmt->execute()) {
        $response = 1;
    }
    else {
        $response = 0;
        $errorMessage = $stmt->error;
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return [$response, $errorMessage];
}

function readStudy() {
    $response = null;

    //~ Loob andmebaasiühenduse ja valmistab ette SQL päringu
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $conn->set_charset("utf8mb4");

    // Kuna kursused ja tegevused eraldi tabelites ja õppelogi tabelis võõrvõti,
    // siis tuleb id-väljad päringus vastavalt ühendada (käsuga INNER JOIN),
    // nüüd tuleb selekteerimisel lisada ette ka tabelinimi (eraldajaks punkt)
    $stmt = $conn->prepare("SELECT vr20__studylog.time, vr20__studylog.day AS `day`,
                                vr20__study_courses.course, vr20__study_activities.activity
                            FROM vr20__studylog
                            INNER JOIN vr20__study_courses
                                ON vr20__studylog.course = vr20__study_courses.id
                            INNER JOIN vr20__study_activities
                                ON vr20__studylog.activity = vr20__study_activities.id
                            ORDER BY `day` DESC"
            );
    //echo $conn->error;

    $stmt->bind_result($timeFromDB, $dayFromDB, $courseFromDB, $activityFromDB);
    $stmt->execute();
    //if ($stmt->fetch())

    while ($stmt->fetch()) {
        // Kuna ei soovi tabelis päeva järel kellaaega kuvada, siis teisendab selle;
        // tabelist tuleb aeg sõnena, seega tuleb enne teisendada numbriliseks ajaks
        // ja alles seejärel saab kasutada date() funktsiooni väljundi vormindamiseks
        $response .= "\n".'<tr><td>'. date("Y-m-d", strtotime($dayFromDB))
                .'</td><td>'. $courseFromDB .'</td><td>'. $activityFromDB
                .'</td><td class="text-right">'. number_format($timeFromDB, 2, ',', ' ')
                .' h</td></tr>';
    }

    if ($response == null) {
        $response = '<tr><td rowspan="4"><div class="alert alert-warning">'
                .'Kahjuks uudised puuduvad!</div></td></tr>';
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return $response;
}
