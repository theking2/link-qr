<?php declare(strict_types=1);
require_once '../inc/session.inc.php';
require_once '../inc/settings.inc.php';
require_once '../inc/connect.inc.php';
require_once '../inc/utils.inc.php';

const SEND_PWD_TEMPLATE = '<html>
<head>
<style type="text/css">
p {color:#1d1d1b;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:0.8em;}
h1{color:#ff9f35;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:1.3em;}
</style>
</head><body>
<h1>Benutzerzugang bestätigen</h1>
<p>Klicken Sie bitte auf den untenstehenden Link um Ihr Zugangskonto zu aktivieren.</p>
<p><a href="%s">Kennwort setzen</a></p>
<p>Mit freundlichen Grüssen<br />
</body></html>
';

/***
 * Create a return path
 */
function getCurrentUrlPath()
{
	$server = $_SERVER['HTTP_HOST'];

	$path = $_SERVER['REQUEST_URI'];
	$path = explode( '/', $path) ;
	array_pop( $path );
	$path = implode( '/', $path );
	return $_SERVER['REQUEST_SCHEME'].'://'. $server . $path;
}

if( !isset($_SESSION['uuid']) ) {
	error_log("missing uuid in session");
	header ('Location:../');
	exit(-1);
}

$username = $_SESSION['username'];
$uuid = $_SESSION['uuid'];
$email = $_SESSION['email'];

if( strlen($uuid) == 0 ) {
	error_log('no uuid');
	exit(0);
}

$url = getCurrentUrlPath() . "/setpassword.php?vc=$uuid&username=$username";

$to      = $email;
$subject = "LINK Kennwort zurücksetzen";
$headers = ''
.'From: ' . 'info@dwh.guerbet.ch' . PHP_EOL
.'MIME-Version: 1.0'.PHP_EOL
.'Content-type: text/html; charset=utf-8'.PHP_EOL
;

require_once '../inc/header.inc.php';

$message = sprintf(SEND_PWD_TEMPLATE, $url);

if(!DEBUG) {
	mail( $to, $subject, $message, $headers );
}
$messages[] = "Eine E-Mail wurde gesendet.";
?><nav id="reports">
  <h2>Password setzen</h2>

  <?php
  if ($messages) {

    foreach ($messages as $message) {
      echo '<label>' . $message . '</hlabel';
    }
  } ?>
</nav>
<main>
	<h2>E-Mail gesendet</h2>
	<li>Wir haben Ihnen eine E-Mail mit einem Link zum Setzen Ihres Kennworts gesendet.</li>
	<?php if(DEBUG) { ?>
		<a href="<?=$url?>"$url><?=$url?></a>
	<?php } ?>
</main>
</body>

</html>
