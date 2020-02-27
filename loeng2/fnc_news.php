<?php
(@include "../../../.sql.php") or die("<b>Ei pääse andmebaasi!</b>");


function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function saveNews($newsTitle, $newsContent) {
    $userid = 1;
    $response = null;

    //~ Loob andmebaasiühenduse
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);

    //~ Valmistab ette SQL päringu
    $stmt = $conn->prepare("INSERT INTO vr20_news (userid, title, content) VALUES (?, ?, ?)");
    //echo $conn->error;

    //~ Seob päringuga tegelikud andmed
    //~ i - integer,  s - string,  d - decimal
    $stmt->bind_param("iss", $userid, $newsTitle, $newsContent);

    if ($stmt->execute()) {
        $response = 1;
    }
    else {
        $response = 0;
        echo $stmt->error;
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return $response;
}

function readNews() {
    $response = null;

    //~ Loob andmebaasiühenduse ja valmistab ette SQL päringu
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $stmt = $conn->prepare("SELECT title, content FROM vr20_news");
    //echo $conn->error;

    $stmt->bind_result($titleFromDB, $contentFromDB);
    $stmt->execute();
    //if ($stmt->fetch())

    while ($stmt->fetch()) {
        $response .= "\n<div style=\"border:1px solid lightgrey;padding:5px;border-radius:5px\"><h2>". $titleFromDB ."</h2>";
        $response .= "<p>". $contentFromDB ."</p></div>";
    }
    if ($response == null) {
        $response = "<P>Kahjuks uudised puuduvad!</p>";
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return $response;
}
