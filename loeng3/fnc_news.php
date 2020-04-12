<?php

function saveNews($newsTitle, $newsContent) {
    $userid = $_SESSION['userid'];  // Uudise lisaja ID
    $response = null;
    $errorMessage = null;
    $sql = "INSERT INTO vr20__news (userid, title, content) VALUES (?, ?, ?)";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $db->query($sql, [$userid, $newsTitle, $newsContent], "iss");

    if ($db->affectedRows() != -1) {
        $response = 1;
    }
    else {
        $response = 0;
        $errorMessage = $stmt->error;
    }

    return [$response, $errorMessage];
}

function readNews($num, $limit) {
    $response = null;
    $pr = TABLE_PREFIX;
    $sql = "SELECT ${pr}news.id AS id, ${pr}news.title AS title, ${pr}news.content AS content,
                ${pr}news.created AS created, ${pr}news.deleted AS deleted,
                ${pr}users.firstname AS fname, ${pr}users.lastname AS lname
            FROM ${pr}news
            INNER JOIN ${pr}users ON ${pr}news.userid = ${pr}users.id
            WHERE deleted IS NULL
            ORDER BY created DESC LIMIT ?";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $result = $db->query($sql, $limit, 'i')->fetchAll();

    foreach ($result as $row) {
        $response .= "\n".'<div id="'. $num . $row['id'] .'" class="newsItem">'
                // Sulgemisnupp
                .'<a data-id="'. $num . $row['id'] .'" href="#" '
                .'onclick="return deleteNews(this,\''. $row['title'] .'\')" '
                .'title="Delete news" class="closeBtn">❌</a>'
                // Uudise pealkiri
                .'<h2 class="card-title">'. $row['title'] .'</h2>'
                // Uudise kuupäev, aga ette veel uudise ID (kontrolliks)
                .'<div class="date"><span class="badge badge-pill badge-info align-top">'
                . $row['id'] .'</span> '. $row['created']
                // Uudise postitaja
                .' &nbsp; <span style="opacity:.3">👤</span> '. $row['fname'] .' '. $row['lname'] .'</div>'
                // Viimaseks uudise sisu
                .'<p class="card-text mt-3">'. $row['content'] .'</p></div>';
    }
    if ($response == null) {
        $response = '<div class="alert alert-warning">Kahjuks uudised puuduvad!</div>';
    }

    return $response;
}

function deleteNews($id) {
    $ok = true;
    $response = null;
    $sql = "UPDATE vr20__news SET deleted=NOW() WHERE id=?";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $db->query($sql, $id, 'i');
    if ($db->affectedRows() == -1) $ok = false;

    //~ Väljastab kas kustutamine läks korda või mitte
    if ($ok) echo 'OK!';
    else     echo 'ERROR!';
}
