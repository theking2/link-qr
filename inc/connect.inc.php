<?php declare(strict_types=1);

$db = new \PDO(
	'mysql:server='.$settings['db']['server'].';dbname='.$settings['db']['name'],	
	$settings['db']['user'],
	$settings['db']['passwort'],
	[PDO::ATTR_PERSISTENT => true]
) or exit('Could not connect to database');

