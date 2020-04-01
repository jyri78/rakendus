<?php

function saveNews($newsTitle, $newsContent) {
    $userid = $_SESSION['userid'];  // Uudise lisaja ID
    $response = null;
    $errorMessage = null;
    $sql = "INSERT INTO vr20_news (userid, title, content) VALUES (?, ?, ?)";

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
    $sql = "SELECT vr20_news.id AS id, vr20_news.title AS title, vr20_news.content AS content,
                vr20_news.created AS created, vr20_news.deleted AS deleted,
                vr20_users.firstname AS fname, vr20_users.lastname AS lname
            FROM vr20_news
            INNER JOIN vr20_users ON vr20_news.userid = vr20_users.id
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
    $sql = "UPDATE vr20_news SET deleted=NOW() WHERE id=?";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $db->query($sql, $id, 'i');
    if ($db->affectedRows() == -1) $ok = false;

    //~ Väljastab kas kustutamine läks korda või mitte
    if ($ok) echo 'OK!';
    else     echo 'ERROR!';
}
