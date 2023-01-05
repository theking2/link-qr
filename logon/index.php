<?php

declare(strict_types=1);
require_once '../inc/session.inc.php';
$retry = $_SESSION['failed attempt'] ?? 0;
session_regenerate_id(true);
session_unset();
$_SESSION['failed attempt'] = ++$retry;

require_once "../inc/utils.inc.php";
require_once "../inc/header.inc.php"; ?>
<main>

	<h1>Code-Generator</h1>

	<h2>LOGIN</h2>
	<form action="logon.php" method="post" id="form-container">
		<label for="username">Username</label>
		<input id="username" name="username" type="text" placeholder="Username" autofocus="autofocus" autocomplete="off" required>
		<label for="password">Password</label>
		<div id="password-input">
			<input type='password' name='password' id='password' required>
			<span id="show-toggle">🔒</span>
			<span id="capslock-on">Feststelltaste aktiviert!</span>
		</div>
		<span></span>
		<input name="action" type="submit" value="Login">
		<?php if ($retry > 3) { ?>
			<span></span>
			<p><a href="resetpassword.php">Passwort vergessen?</a></p>
		<?php } ?>

		<span></span>
		<p><a href="register.php">Konto erstellen!</a></p>
	</form>

</main>
</body>
<script defer src="/assets/password.js"></script>

</html>