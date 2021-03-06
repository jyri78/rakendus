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
    $conn->set_charset("utf8mb4");

    //~ Valmistab ette SQL päringu
    $stmt = $conn->prepare("INSERT INTO vr20__news (userid, title, content) VALUES (?, ?, ?)");
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

function readNews($num, $limit) {
    $response = null;

    //~ Loob andmebaasiühenduse ja valmistab ette SQL päringu
    $conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $conn->set_charset("utf8mb4");
    $stmt = $conn->prepare("SELECT id, title, content, created, deleted
                            FROM vr20__news WHERE deleted IS NULL ORDER BY created DESC LIMIT ?");
    //echo $conn->error;

    $stmt->bind_param("i", $limit);
    $stmt->bind_result($idFromDB, $titleFromDB, $contentFromDB, $createdFromDB, $deletedFromDB);
    $stmt->execute();
    //if ($stmt->fetch())

    while ($stmt->fetch()) {
        $response .= "\n".'<div id="'. $num . $idFromDB .'" class="newsItem">'
                // Sulgemisnupp
                .'<a data-id="'. $num . $idFromDB .'" href="#" '
                .'onclick="return deleteNews(this,\''. $titleFromDB .'\')" '
                .'title="Delete news" class="closeBtn">❌</a>'
                // Uudise pealkiri
                .'<h2 class="card-title">'. $titleFromDB .'</h2>'
                // Uudise kuupäev, aga ette veel uudise ID (kontrolliks)
                .'<div class="date"><span class="badge badge-pill badge-info align-top">'
                . $idFromDB .'</span> '. $createdFromDB .'</div>'
                // Viimaseks uudise sisu
                .'<p class="card-text">'. $contentFromDB .'</p></div>';
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
    $conn->set_charset("utf8mb4");

    //~ Valmistab ette SQL päringu
    $stmt = $conn->prepare("UPDATE vr20__news SET deleted=NOW() WHERE id=?");
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
