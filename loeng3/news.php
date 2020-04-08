<?php
require "../inc/fnc.php";
require "fnc_news.php";


// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = 'news.php';
    $_SESSION['error'] = 'Uudiste vaatamiseks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}


if (!isset($_SESSION["controlNum"])) $_SESSION["controlNum"] = random_int(101, 999);


// Postituse kustutamisel kasutan AJAX tehnoloogiat.
if (isset($_POST["delNews"])) {
    $n = filter_input(INPUT_POST, "delId", FILTER_SANITIZE_NUMBER_INT);
    $controlNum = substr($n, 0, 3);

    // testib kontrollarvu kehtivust
    if ($controlNum == $_SESSION["controlNum"]) {
        deleteNews(substr($n, 3));
    } else {
        echo 'WRONG_CONTROL_NUMBER!';
    }
    exit;
}

// Kuvatavate uudiste arv
$newsLimits = [1, 3, 5, 8, 10, 15];
$limitNews = $_SESSION["limitNews"] ?? 5;  // kasutaja valik või vaikimisi 5

// Kontrollib, kas on valitud uudiste arv, kui on,
// siis taaslaeb lehe, et postitus "üles rippuma" ei jääks
if (isset($_POST["newsCount"])) {
    $_SESSION["limitNews"] = filter_input(INPUT_POST, "newsCount", FILTER_SANITIZE_NUMBER_INT);
    session_write_close();
    header("Location: ". $_self);
    exit;
}

$newsHTML = readNews($_SESSION["controlNum"], $limitNews);


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => '',
    'style' => "
.newsItem {border:1px solid #ccc; border-radius:5px; margin:10px 0; padding:8px 16px}
.closeBtn,.closeBtn:hover {text-decoration:none; border-radius:5px}
.closeBtn {border:1px solid white; float:right; text-decoration:none; margin-top:-8px; margin-right:-16px}
.closeBtn:hover {background-color:#fcc;border-color:#f77}
.badge {opacity:.3; font-size:.65em}
.date {font-size:.9em; color:#9a9; margin-top:-10px}
",
    'script' => "
var i,$ = function(i) {return document.getElementById(i);},
post = function (id) {
    // Loob päringu objekti
    var xhr = new XMLHttpRequest();

    // Määrab tegevuse päringu sündmustele
    xhr.onreadystatechange = function() {
        // Kui päring teostatud ja server vastas staatusega 200 ehk \"OK\",
        // alles siis tegeleb vastavalt serveri vastusega edasi
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'OK!') {
                //var o = $(i); o.parentNode.removeChild(o);
    
                // Kuna soovime, et kuvatavate uudiste arv jääks samas,
                // siis laeb lehte uuesti (Node eemaldamise asemel)
                location.reload();
                }
            else alert('Uudise kustutamisega tekkis viga!');
        }
    };

    // Lõpuks teostab postituse (avab, seab päise ja saadab)
    xhr.open('POST', '". $_self ."', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send('delNews&delId='+id);
},
deleteNews = function (obj,title) {
    i = obj.getAttribute('data-id');
    if (i.substring(0,3) != ". $_SESSION["controlNum"] .") return false;
    if (confirm('Kas oled kindel, et soovid uudise \"'+ title +'\" kustutada')) post(i);
    return false;
};
",
    'current' => 'news.php',
    'pages' => [['page.php', 'Loengu leht'], ['addnews.php', 'Lisa uudis'], ['news.php', 'Loe uudised']]
);

require '../inc/_header.inc';
?>

    <form method="post" action="<?= $_self ?>" style="float:right">
        <label for="newsCount">Uudiste arv:</label>
        <select name="newsCount" id="newsCount"><?php
foreach ($newsLimits as $limit):
?>

            <option value="<?= $limit ?>"<?= $limit==$limitNews ? ' selected' : ''
                ?>><?= $limit ?></option><?php
endforeach;
?>

        </select>
        &nbsp;
        <button type="submit" class="btn btn-primary">Vali</button>
    </form>
    <h1 class="mb-3">Uudised</h1>
<?= $newsHTML ?>


<?php
require '../inc/_footer.inc';
