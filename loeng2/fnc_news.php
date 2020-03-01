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
    $errorMessage = null;

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
        $errorMessage = $stmt->error;
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return [$response, $errorMessage];
}

function readNews() {
    global $num;
    $response = null;

    //~ Loob andmebaasiühenduse ja valmistab ette SQL päringu
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $stmt = $conn->prepare("SELECT id, title, content, created, deleted
                            FROM vr20_news WHERE deleted IS NULL ORDER BY created DESC");
    //echo $conn->error;

    $stmt->bind_result($idFromDB, $titleFromDB, $contentFromDB, $createdFromDB, $deletedFromDB);
    $stmt->execute();
    //if ($stmt->fetch())

    while ($stmt->fetch()) {
        $response .= "\n".'<div id="'. $num . $idFromDB .'" class="newsItem"><a data-id="'
                . $num . $idFromDB .'" href="#" onclick="return deleteNews(this,\''
                . $titleFromDB .'\')" title="Delete news" class="closeBtn"'
                .'>❌</a><h2 class="card-title">'. $titleFromDB .'</h2><div class="date">'
                . $createdFromDB .'</div><p class="card-text">'. $contentFromDB .'</p></div>';
    }
    if ($response == null) {
        $response = '<div class="alert alert-warning">Kahjuks uudised puuduvad!</div>';
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    return $response;
}

function deleteNews($id) {
    $ok = true;
    $response = null;

    //~ Loob andmebaasiühenduse
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);

    //~ Valmistab ette SQL päringu
    $stmt = $conn->prepare("UPDATE vr20_news SET deleted=NOW() WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute() === false) {
        $ok = false;
    }

    //~ Sulgeb päringu ja andmebaasi ühenduse
    $stmt->close();
    $conn->close();

    if ($ok) echo 'OK!';
    else     echo 'ERROR!';
}
