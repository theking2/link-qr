<?php

declare(strict_types=1);

use Persist\DB\Database;

require_once '../inc/session.inc.php';
require_once '../inc/utils.inc.php';
require_once '../inc/settings.inc.php';

$messages = [];

if( isset($_POST['action']) ) {
  if( isset($_SESSION['username']) ) {
    /**
     * a user was logged on use the username to find the user
     * In this case we can change the email adress
     * @var \Link\User $user
     */
    $user = \Link\User::find(where: ['username' => $_SESSION['username']]);
    if( is_null($user) ) {
      $messages[] = "Benutzername nicht gefunden";

    } elseif( !empty($_POST['email']) and !check_email_unique($_POST['email']) ) {
      $messages[] = "Email Adresse ist bereits vergeben";

    } else {
      try {
        $user_email = new \Link\UserEmail();
        $user_email-> username = $_SESSION['username'];
        $user_email-> email = $_POST['email'];
        $user_email-> createUUID();
        $user_email-> confirm_date = null;
        $user_email-> register_date = new \DateTime();
        $user_email->freeze();
        $_SESSION['uuid'] = $user_email-> uuid;
        $_SESSION['email'] = $user_email-> email;

        header('Location:sendpasswordemail.php');
        exit(0);
      } catch ( \Exception $e ) {
        $messages[] = "Fehler beim Password setzen. Bitte Administrator kontaktieren.{$e->getMessage()}";
      }
    }
  } else {
    /**
     * find user by email if provided
     * @var \Link\User $user
     */
    if (is_null( $user = \Link\UserEmail::find(where: ['email'=> $_POST['email']]) )) {
      $messages[] = "Zuerst anmelden mit aktuellem Benutzernamen";

    } else {
      $user-> createUUID();
      $user-> freeze();
      /** for set password email we need these */
      $_SESSION['username'] = $user-> username;
      $_SESSION['uuid'] = $user-> uuid;
      $_SESSION['email'] = $user-> email;

      header('Location:sendpasswordemail.php');
      exit(0);
    }
  }
}
require_once "../inc/header.inc.php";?>
<nav id="reports">
  <h2>Password setzen</h2>
  <form method="post" id="form-container">
    <label for="username">Username</label>
    <input type="text" disabled name="username" id="username" value="<?= $_SESSION['username'] ?? '' ?>">
    <label for="email">Email-Addresse</label>
    <input id="email" name="email" type="email" placeholder="E-mail" pattern="^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$" autofocus="autofocus" required>

    <p><input name="action" type="submit" value="Senden"></p>
    <p><a href="index.php">Anmelden</a></p>
  </form>
  <?php
  if ($messages) {

    foreach ($messages as $message) {
      echo '<h2>' . $message . '</h2>';
    }
  } ?>
<main>


</main>
</body>

</html><?php

/**
 * Check if the email adress is unique
 * @return bool
 */
function check_email_unique(string $email):bool {
  global $user;

  $check_user = \Link\UserEmail::find(where: ['email' => trim(strtolower($email))]);
  if (is_null($check_user)) {
    return true;
  } else {
    /** email found, should be the same */
    return $user-> username === $check_user-> username;
  }
}


