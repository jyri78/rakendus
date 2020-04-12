<?php

function signUp($name, $surname, $email, $gender, $birthDate, $password) {
    $notice = null;
    $sql = "INSERT INTO vr20__users (firstname, lastname, birthdate, gender, email, password) VALUES (";

    /*$conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $stmt = $conn->prepare("INSERT INTO vr20_users (firstname, lastname, birthdate, gender, email, password)
                            VALUES (?, ?, ?, ?, ?, ?)");
    echo $conn->error;*/

    // Krüpteerib parooli
    $options = ["cost" => 12, "salt" => substr(sha1(rand()), 0, 22)];
    $pwdhash = password_hash($password, PASSWORD_BCRYPT, $options);

    //~ Abimuutuja parameetrite lisamiseks;
    $params = [$name, $surname, $birthDate, $gender, $email, $pwdhash];

    //$stmt->bind_param("sssiss", $name, $surname, $birthDate, $gender, $email, $pwdhash);
    $db = new DB();
    //echo $db->error;

    //~ Esmalt kontroll, ega email juba ei ole ja kui on, siis tagastab veateate
    if ($db->query("SELECT id FROM vr20__users WHERE email=?", [$email])->fetch()) {
        return "Sellise emailiga kasutajakonto on juba olemas!";
    }

    //~ Lisab vajaliku arvu küsimärke (käsitsi lisamisel võib viga tekkida)
    $val = $db->values($params);
    $db->query($sql . $val .')', $params, 'sssiss');

    if (/*$stmt->execute()*/$db->affectedRows() != -1) {
        $notice = "Uus kasutaja on loodud!";
    } else {
        $notice = $db->stmtError;
    }

    /*$stmt->close();
    $conn->close();
    return $notice;*/

    //~ Lehe uuesti laadimine on parem (et vältida topeltkandeid)
    $_SESSION['notice'] = $notice;
    header("Location: newuser.php");
}

function signIn($email, $password) {
    $notice = null;
    $sql = "SELECT id, firstname, lastname, password FROM vr20__users WHERE email=?";

    $db = new DB();
    echo $db->error;

    $result = $db->query($sql, [$email])->fetch();

    /*$conn = new mysqli(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
    $stmt = $conn->prepare("SELECT id, firstname, lastname, password FROM vr20_users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->bind_result($idFromDB, $firstnameFromDB, $lastnameFromDB, $passwordFromDB);
    echo $conn->error;
    $stmt->execute();*/

    if (/*$stmt->fetch()*/$result) {
        if (password_verify($password, $result['password'])) {
            $_SESSION["userid"] = $result['id'];
            $_SESSION["userFirstName"] = $result['firstname'];
            $_SESSION["userLastName"] = $result['lastname'];

            /*$stmt->close();
            $conn->close();*/
            $page = $_SESSION['errPage'] ?? 'page.php';
            unset($_SESSION['errPage']);
            header("Location: ". $page);
            exit;
        } else {
            $notice = "Vale salasõna!";
        }
    } else {
        $notice = "Sellist kasutajat (". $email .") ei leitud";
    }

    /*$stmt->close();
    $conn->close();*/
    return $notice;
}
