<?php
    require "../inc/fnc.php";
    require "fnc_users.php";

    SessionManager::sessionStart(SESSION_NAME, 0, SESSION_PATH, SESSION_DOMAIN);


    /* =========================================================================
        Sisselogimine
       =========================================================================
    */
    $notice = null;
    $email = null;
    $emailError = null;
    $passwordError = null;

    if(isset($_POST["login"])){
		if (isset($_POST["email"]) and !empty($_POST["email"])){
		  $email = test_input($_POST["email"]);
		} else {
		  $emailError = "Palun sisesta kasutajatunnusena e-posti aadress!";
		}
	  
		if (!isset($_POST["password"]) or strlen($_POST["password"]) < 8){
		  $passwordError = "Palun sisesta parool, vähemalt 8 märki!";
		}
	  
		if(empty($emailError) and empty($passwordError)){
		   $notice = signIn($email, $_POST["password"]);
		} else {
			$notice = "Ei saa sisse logida!";
		}
    }


    /* =========================================================================
        1. loengu asjad
       =========================================================================
    */
    $my_name = "Jüri Kormik";
    $full_time_now = date("d.m.Y H:i:s");
    $time_HTML = '<p>Lehe avamise hetkel oli: <b>'. $full_time_now ."</b>.</p>\n";

    $hour_now = date("H");
    $part_of_day = "hägune aeg";

    //~ Ajaline kontroll
    if ($hour_now < 10) $part_of_day = "hommik";
    elseif ($hour_now < 18) $part_of_day = "aeg aktiivselt tegutseda";
    $part_of_day_HTML = "<p>Käes on <b>". $part_of_day ."</b>!</p>\n";

    $semester_start = new DateTime("2020-01-27");
    $semester_end = new DateTime("2020-06-22");
    $semester_duration = $semester_start->diff($semester_end);
    //var_dump($semester_duration);
    $today = new DateTime("now");
    $from_semester_start = $semester_start->diff($today, FALSE);
    //var_dump($from_semester_start);

    //~ Kui semester pole veel alanud või juba läbi, siis teavitab sellest,
    //~ muidu väljastab tag'i meter
    if ($from_semester_start->format("%r%a") < 1) {
        $semester_duration_HTML = "<p>Semester pole veel alanud!</p>";
    }
    elseif ($from_semester_start->days > $semester_duration->days) {
        $semester_duration_HTML = "<p>Semester on juba läbi!</p>";
    }
    else {
        $semester_duration_HTML = '<p>Semester on hoos: <meter value="'
                . $from_semester_start->days .'" min="0" max="'
                . $semester_duration->days .'">'
                . $from_semester_start->format("%r%a") .'/'
                . $semester_duration->format("%r%a") .'</meter></p>'
                . "\n";
    }

    /* -----------------------------------------------------------------
        Fotode lugemine kaustast
       -----------------------------------------------------------------
    */
    $pics_dir = "../pics/";
    $photo_types_allowed = ["image/jpeg", "image/png"];
    $photo_list = [];

    $all_files = array_slice(scandir($pics_dir), 2);
    //var_dump($all_files);

    foreach ($all_files as $file) {
        $file_info = getimagesize($pics_dir . $file);

        //~ Kas on pilt
        if (in_array($file_info["mime"], $photo_types_allowed)) {
            $photo_list[] = $file;
            //array_push($photo_list, $file);
        }
    }
    //var_dump($photo_list);
    $photo_count = count($photo_list);
    $photo_num = [];

    foreach ([0,1,2] as $i) {
        do {
            $num = mt_rand(0, $photo_count-1);
        } while (in_array($num, $photo_num));  //~ esitab ainult unikaalsed pildid

        $photo_num[] = $num;
    }

    $random_image_HTML = '';
    foreach ($photo_num as $num) {
        $random_image_HTML .= '    <img class="border rounded-lg mx-2" src="'. $pics_dir . $photo_list[$num]
                .'" alt="Juhuslik pilt Haapsalust '. $num .'" width="320" />'. "\n";
    }


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);
$_page = array(
    'title' => 'Loeng 3',
    'h1' => '3. loengu leht',
    'current' => 'page.php',
    'pages' => [['page.php', 'Loengu leht'], ['home.php', 'Äge leht']]
);

if (!isset($_SESSION['userid'])) $_page['pages'][] = ['newuser.php', 'Lisa kasutaja'];

require '../inc/_header.inc';
?>

    <p>See leht on valminud õppetöö raames!</p>

    <div class="card p-3 my-5"><?php
if (isset($_SESSION['error'])) {
    echo '        <div class="alert alert-danger mb-5"><b class="text-danger">Viga!</b> '. $_SESSION['error'] .'</div>';
    unset($_SESSION['error']);
}

// Kui kasutaja on sisse loginud, siis kuvab nime ja väljalogimise nuppu
if (isset($_SESSION['userid'])):
?>

        <h3><?= $_SESSION['userFirstName'] .' '. $_SESSION['userLastName'] ?></h3>
        <div><a class="btn btn-primary btn-block my-3" role="button" href="home.php?logout=1">Logi välja</a></div>
<?php
else:
?>

        <h3>Logi sisse</h3>
        <form class="was-validated" method="POST" action="<?= $_self ?>">
            <div class="input-group my-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="email">E-mail (kasutajatunnus):</label>
                </div>
                <input type="email" class="form-control" id="email" name="email" value="<?=
$email ?>" autofocus required>
            </div><?php
if ($emailError) echo "\n" .'            <div class="alert alert-error">'. $emailError .'</div>';
?>

            <div class="input-group my-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="password">Salasõna:</label>
                </div>
                <input type="password" class="form-control" id="password" name="password" required>
            </div><?php
if ($passwordError) echo "\n" .'            <div class="alert alert-error">'. $passwordError .'</div>';
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

    <?= $time_HTML ?>
    <?= $part_of_day_HTML ?>
    <?= $semester_duration_HTML ?>
<?= $random_image_HTML ?>

<?php
require '../inc/_footer.inc';
