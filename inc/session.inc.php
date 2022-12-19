<?php declare(strict_types=1);

session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'],
	'samesite' => 'Strict',
]);
session_start([
	'name' => 'SESSION',
	'sid_length' => 96,
	'sid_bits_per_character' => 6,
  'use_strict_mode'=> true,
  'referer_check'=> $_SERVER['HTTP_HOST'],
]);