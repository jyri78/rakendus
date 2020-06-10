<?php
require "../inc/fnc.php";
//require "fnc_users.php";
require "../inc/User.class.php";


/* =========================================================================
    Väljalogimine
    =========================================================================
*/
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: ". $_self);
}


/* =========================================================================
    Sisselogimine
    =========================================================================
*/
$notice = null;
$email = null;
$emailError = null;
$passwordError = null;

if(isset($_POST["login"])){
    $email = test_input($_POST["email"] ?? '');

    if (empty($email)) {
        $emailError = "Palun sisesta kasutajatunnusena e-posti aadress!";
    }
    
    if (!isset($_POST["password"]) or strlen($_POST["password"]) < 8){
        $passwordError = "Palun sisesta parool, vähemalt 8 märki!";
    }
    
    if(empty($emailError) and empty($passwordError)){
        $notice = User::signIn($email, $_POST["password"]);
    } else {
        $notice = "Ei saa sisse logida!";
    }
}


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 6',
    'h1' => '6. loengu leht',
    'current' => $_self,
    'pages' => [['page.php', 'Loengu leht'],  ['photoUpload.php', 'Fotode üleslaadimine'], ['gallery.php', 'Fotogalerii']]
);

require '../inc/_header.inc';
?>

    <p>See leht on valminud õppetöö raames!</p><?php
if (isset($_SESSION['error'])) {
    echo '        <div class="alert alert-danger mb-5"><b class="text-danger">Viga!</b> '. $_SESSION['error'] .'</div>';
    unset($_SESSION['error']);
}
?>

    <div class="container text-center">
        <div class="d-flex justify-content-center col-md-5 d-md-inline-block">

<div class="card p-3 my-5"><?php
// Kui kasutaja on sisse loginud, siis kuvab nime ja väljalogimise nuppu
if (isset($_SESSION['userid'])):
?>

    <h3><?= $_SESSION['userFirstName'] .' '. $_SESSION['userLastName'] ?></h3>
    <hr>
    <div><a class="btn btn-primary btn-block my-3" role="button" href="<?= $_self ?>?logout=1">Logi välja</a></div>
<?php
else:
?>

<div class="container bg-light rounded-lg p-3">
<h3>Logi sisse</h3>
</div>
<hr>
<form class="was-validated" method="POST" action="<?= $_self ?>">
    <div class="input-group">
        <div class="input-group-prepend">
            <label class="input-group-text" for="email"><svg class="bi bi-envelope-fill" width="1em" height="1em"
                    viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path
                            d="M.05 3.555L8 8.414l7.95-4.859A2 2 0 0014 2H2A2 2 0 00.05 3.555zM16 4.697l-5.875 3.59L16 11.743V4.697zm-.168 8.108L9.157 8.879 8 9.586l-1.157-.707-6.675 3.926A2 2 0 002 14h12a2 2 0 001.832-1.195zM0 11.743l5.875-3.456L0 4.697v7.046z"/></svg>
            </label>
        </div>
        <input type="email" class="form-control" id="email" name="email" value="<?=
$email ?>" placeholder="E-mail (kasutajatunnus)" autofocus required>
    </div><?php
if ($emailError) echo "\n" .'    <div class="alert alert-error">'. $emailError .'</div>';
?>

    <div class="input-group my-3">
        <div class="input-group-prepend">
            <label class="input-group-text" for="password"><svg class="bi bi-lock-fill" width="1em" height="1em"
                    viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <rect width="11" height="9" x="2.5" y="7" rx="2"/><path fill-rule="evenodd"
                        d="M4.5 4a3.5 3.5 0 117 0v3h-1V4a2.5 2.5 0 00-5 0v3h-1V4z" clip-rule="evenodd"/></svg>
            </label>
        </div>
        <input type="password" class="form-control" id="password" name="password" placeholder="Salasõna" required>
    </div><?php
if ($passwordError) echo "\n" .'    <div class="alert alert-error">'. $passwordError .'</div>';
?>

    <input type="submit" class="btn btn-primary btn-block" name="login" value="Logi sisse"><?php
if ($notice) echo '<div class="alert alert-danger my-3"><b class="text-danger">Viga!</b> '. $notice .'</div>';
?>

</form><?php
endif;
?>

    <hr>
    <p>Loo endale <a href="newuser.php">kasutajakonto</a>!</p>
</div>

        </div>
    </div>

<?php
require '../inc/_footer.inc';
