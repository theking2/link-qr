<?php declare(strict_types=1);
require_once 'inc/utils.inc.php';
require_once 'inc/settings.inc.php';
// Allow these characters
$charset ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNAOPQRSTUVWXYZ1234567890-_.!~*'()";

function getCode(): string {
  global $charset;
  $length = strlen($charset)-1;

  // short code is 5 characters. When changed, also change the database!
  $result
	= $charset[rand(0,$length)]
 	. $charset[rand(0,$length)]
	. $charset[rand(0,$length)]
	. $charset[rand(0,$length)]
	. $charset[rand(0,$length)]
	;
  return $result;
}

require_once 'inc/connect.inc.php';
$insert = $db-> prepare("
insert into
    code( code,  url,  last_used, hits)
  values(:code, null, '0000-00-00', default)");
$code = getCode();
$insert->bindParam(':code',$code);

for($i=1000; $i>0; $i--) {
  $code = getCode();
  $insert-> execute() or die($insert->errorInfo()[2]);
}
echo $code;
