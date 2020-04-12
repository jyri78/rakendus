<?php

function signUp($name, $surname, $email, $gender, $birthDate, $password) {
    // Krüpteerib parooli
    $options = ["cost" => 12, "salt" => substr(sha1(rand()), 0, 22)];
    $pwdhash = password_hash($password, PASSWORD_BCRYPT, $options);

    //~ Abimuutuja parameetrite lisamiseks;
    $params = [$name, $surname, $birthDate, $gender, $email, $pwdhash];

    $db = new DB();
    $db->select('id')->from('users')->where()->eq(['email', '?']);

    //~ Esmalt kontroll, ega email juba ei ole ja kui on, siis tagastab veateate
    if ($db->q($email)->fetch()) return "Sellise emailiga kasutajakonto on juba olemas!";

    $db->insert('users', ['firstname', 'lastname', 'birthdate', 'gender', 'email', 'password'])
            ->values($params)
            ->q($params, 'sssiss');

    if ($db->affectedRows() == -1) $notice = $db->stmtError;
    else $notice = "Uus kasutaja on loodud!";

    //~ Lehe uuesti laadimine on parem (et vältida topeltkandeid)
    $_SESSION['notice'] = $notice;
    header("Location: newuser.php");
}

function signIn($email, $password) {
    $notice = null;

    $result = (new DB())
            ->select(['id', 'firstname', 'lastname', 'password'])
            ->from('users')
            ->where()->eq(['email', '?'])
            ->q($email)->fetch();

    if ($result) {
        if (password_verify($password, $result['password'])) {
            $_SESSION["userid"] = $result['id'];
            $_SESSION["userFirstName"] = $result['firstname'];
            $_SESSION["userLastName"] = $result['lastname'];

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

    return $notice;
}
