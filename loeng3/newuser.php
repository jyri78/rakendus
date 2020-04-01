<?php
//  require("../../../../configuration.php");
require "../inc/fnc.php";
require "fnc_users.php";

SessionManager::sessionStart(SESSION_NAME, 0, SESSION_PATH, SESSION_DOMAIN);

//~ Ei luba sisseloginud kasutajal uut kasutajat luua
if (isset($_SESSION['userid'])) {
    $_SESSION['error'] = "Uue kasutajakonto loomiseks logi esmalt välja.";
    header("Location: page.php");
    exit;
}

    
  $notice = $_SESSION['notice'] ?? null;
  // Kui olemas, siis kustutab sessiooni väärtuse, ei jääks teadet kuvama
  unset($_SESSION['notice']);

  $name = null;
  $surname = null;
  $email = null;
  $gender = null;
  $birthMonth = null;
  $birthYear = null;
  $birthDay = null;
  $birthDate = null;
  $monthNamesET = ["jaanuar", "veebruar", "märts", "aprill", "mai", "juuni","juuli", "august", "september", "oktoober", "november", "detsember"];
  
  //muutujad võimalike veateadetega
  $nameError = null;
  $surnameError = null;
  $birthMonthError = null;
  $birthYearError = null;
  $birthDayError = null;
  $birthDateError = null;
  $genderError = null;
  $emailError = null;
  $passwordError = null;
  $confirmpasswordError = null;
  
  //kui on uue kasutaja loomise nuppu vajutatud
  if(isset($_POST["submitUserData"])){
	//kui on sisestatud nimi
	if(isset($_POST["firstName"]) and !empty($_POST["firstName"])){
		$name = test_input($_POST["firstName"]);
	} else {
		$nameError = "Palun sisestage eesnimi!";
	} //eesnime kontrolli lõpp
	
	if (isset($_POST["surName"]) and !empty($_POST["surName"])){
		$surname = test_input($_POST["surName"]);
	} else {
		$surnameError = "Palun sisesta perekonnanimi!";
	}
	
	if(isset($_POST["gender"])){
	    $gender = intval($_POST["gender"]);
	} else {
		$genderError = "Palun märgi sugu!";
	}

	//kontrollime, kas sünniaeg sisestati ja kas on korrektne
	  if(isset($_POST["birthDay"]) and !empty($_POST["birthDay"])){
		  $birthDay = intval($_POST["birthDay"]);
	  } else {
		  $birthDayError = "Palun vali sünnikuupäev!";
	  }
	  
	  if(isset($_POST["birthMonth"]) and !empty($_POST["birthMonth"])){
		  $birthMonth = intval($_POST["birthMonth"]);
	  } else {
		  $birthMonthError = "Palun vali sünnikuu!";
	  }
	  
	  if(isset($_POST["birthYear"]) and !empty($_POST["birthYear"])){
		  $birthYear = intval($_POST["birthYear"]);
	  } else {
		  $birthYearError = "Palun vali sünniaasta!";
	  }
	  
	  //vaja ka kuupäeva valiidsust kontrollida ja kuupäev kokku panna
	  if (empty($birthDayError) and empty($birthMonthError) and empty($birthYearError)) {
		  if (checkdate($birthMonth, $birthDay, $birthYear)) {
			  $tempDate = new DateTime($birthYear .'-'. $birthMonth .'-'. $birthDay);
			  $birthDate = $tempDate->format("Y-m-d");
		  } else {
			  $birthDateError = "Valitud kuupäev on vigane!";
		  }
	  }
	  
	//email ehk kasutajatunnus
	
	  if (isset($_POST["email"]) and !empty($_POST["email"])){
		$email = test_input($_POST["email"]);
		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		if ($email === false) {
			$emailError = "Palun sisesta korrektne e-postiaadress!";
		}
	  } else {
		  $emailError = "Palun sisesta e-postiaadress!";
	  }
	  
	  //parool ja selle kaks korda sisestamine
	  
	  if (!isset($_POST["password"]) or empty($_POST["password"])){
		$passwordError = "Palun sisesta salasõna!";
	  } else {
		  if(strlen($_POST["password"]) < 8){
			  $passwordError = "Liiga lühike salasõna (sisestasite ainult " .strlen($_POST["password"]) ." märki).";
		  }
	  }
	  
	  if (!isset($_POST["confirmpassword"]) or empty($_POST["confirmpassword"])){
		$confirmpasswordError = "Palun sisestage salasõna kaks korda!";  
	  } else {
		  if($_POST["confirmpassword"] != $_POST["password"]){
			  $confirmpasswordError = "Sisestatud salasõnad ei olnud ühesugused!";
		  }
	  }

	
	//Kui kõik on korras, salvestame
	if(empty($nameError) and empty($surnameError) and empty($birthMonthError) and empty($birthYearError) and empty($birthDayError)and empty($birthDateError) and empty($genderError) and empty($emailError) and empty($passwordError) and empty($confirmpasswordError)){
		$notice = signUp($name, $surname, $email, $gender, $birthDate, $_POST["password"]);

		/*if ($notice == "OK") {
			$notice = "Uus kasutaja on loodud!";
			$name = null;
			$surname = null;
			$email = null;
			$gender = null;
			$birthMonth = null;
			$birthYear = null;
			$birthDay = null;
			$birthDate = null;
		} else {
			$notice = "Uue kasutaja salvestamisel tekkis tõrge: ". $notice;
		}*/
	}//kui kõik korras
	
  } //kui on nuppu vajutatud


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Loo endale kasutajakonto',
    'current' => 'newuser.php',
    'pages' => [['page.php', 'Avalehele'], ['newuser.php', 'Lisa kasutaja']]
);

require '../inc/_header.inc';
?>

    <p>See leht on valminud õppetöö raames!</p>

    <div class="card p-3 my-5">
        <form class="was-validated" method="POST" action="<?= $_self ?>">
            <div class="input-group my-3">
                <div class="input-group-prepend">
					<label class="input-group-text" for="firstName">Eesnimi:</label>
				</div>
				<input type="text" class="form-control" id="firstName" name="firstName" value="<?=
						$name ?>" autofocus required><?php
if ($nameError) echo "\n" .'                <div class="alert alert-error">'. $nameError .'</div>';
?>

            </div>
            <div class="input-group my-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="surNaame">Perekonnanimi:</label>
                </div>
                <input type="text" class="form-control" id="surName" name="surName" value="<?=
                        $surname ?>" required><?php
if ($surnameError) echo "\n" .'             <div class="alert alert-error">'. $surnameError .'</div>';
?>

			</div>
            <div class="container my-3">
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" class="custom-control-input" id="g1" name="gender" value="1"<?= $gender == "1" 
                            ? ' checked' : '' ?>>
                    <label class="custom-control-label" for="g1">Mees</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" class="custom-control-input" id="g2" name="gender" value="2"<?= $gender == "2"
                            ? ' checked' : '' ?>>
                    <label class="custom-control-label" for="g2">Naine</label>
                </div>
            </div><?php
if ($genderError) echo "\n" .'                <div class="alert alert-error">'. $genderError .'</div>';
?>

            <div class="row">
                <div class="col">
                    <!-- label class="custom-control-label" for="birthDay">Sünnikuupäev: </label -->
                    <?php
                //sünnikuupäev
                echo '<select class="custom-select" id="birthDay" name="birthDay" required>' ."\n";
                echo "\t \t" .'<option value="" selected disabled>Sünnikuupäev</option>' ."\n";
                for($i = 1; $i < 32; $i ++){
                    echo "\t \t" .'<option value="' .$i .'"';
                    if($i == $birthDay){
                        echo " selected";
                    }
                echo ">" .$i ."</option> \n";
            }
            echo "\t </select> \n";
?>

                </div>
                <div class="col">
                    <!-- label for="birthMonth">Sünnikuu: </label -->
                    <?php
                echo '<select class="custom-select" id="birthMonth" name="birthMonth" required>' ."\n";
                echo "\t \t" .'<option value="" selected disabled>Sünnikuu</option>' ."\n";
                for ($i = 1; $i < 13; $i ++){
                    echo "\t \t" .'<option value="' .$i .'"';
                    if ($i == $birthMonth){
                        echo " selected ";
                    }
                    echo ">" .$monthNamesET[$i - 1] ."</option> \n";
                }
                echo "</select> \n";
?>

                </div>
                <div class="col">
                    <!-- label for="birthYear">Sünniaasta: </label -->
                    <?php
                echo '<select class="custom-select" id="birthYear" name="birthYear" required>' ."\n";
                echo "\t \t" .'<option value="" selected disabled>Sünniaasta</option>' ."\n";
                for ($i = date("Y") - 15; $i >= date("Y") - 110; $i --){
                    echo "\t \t" .'<option value="' .$i .'"';
                    if ($i == $birthYear){
                        echo " selected ";
                    }
                    echo ">" .$i ."</option> \n";
                }
                echo "</select> \n";
?>

                </div>
            </div><?php
        if ($birthDateError || $birthDayError || $birthMonthError || $birthYearError) 
            echo "\n" .'                <div class="alert alert-error">'. $birthDateError ." "
                    . $birthDayError ." ". $birthMonthError ." ". $birthYearError .'</div>';
?>

        <div class="input-group my-3">
            <div class="input-group-prepend">
                <label class="input-group-text" for="email">E-mail (kasutajatunnus):</label>
            </div>
            <input type="email" class="form-control" id="email" name="email" value="<?=
                    $email ?>" required>
        </div><?php
if ($emailError) echo "\n" .'                <div class="alert alert-error">'. $emailError .'</div>';
?>

        <div class="input-group my-3">
            <div class="input-group-prepend">
                <label class="input-group-text" for="password">Salasõna (min 8 tähemärki):</label>
            </div>
		    <input type="password" class="form-control" id="password" name="password" required>
        </div><?php
if ($passwordError) echo "\n" .'                <div class="alert alert-error">'. $passwordError .'</div>';
?>

        <div class="input-group my-3">
            <div class="input-group-prepend">
		        <label class="input-group-text" for="confirmpassword">Korrake salasõna:</label>
            </div>
		    <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" required>
        </div><?php
if ($confirmpasswordError) echo "\n" .'                <div class="alert alert-error">'. $confirmpasswordError .'</div>';
?>

        <input type="submit" class="btn btn-primary btn-block" name="submitUserData" value="Loo kasutaja"><?php
if ($notice) echo '<div class="alert alert-info my-3">'. $notice .'</div>';
?>

        </form>
		<hr>
		<p>Tagasi <a href="page.php">avalehele</a></p>
	</div>

<?php
require '../inc/_footer.inc';
