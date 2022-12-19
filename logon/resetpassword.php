<?php

declare(strict_types=1);

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
    $user = \Link\User::find(where: ['USERNAME' => $_SESSION['username']]);
    if( is_null($user) ) {
      $messages[] = "Benutzername nicht gefunden";

    } elseif( !empty($_POST['email']) and !check_email_unique($_POST['email']) ) {
      $messages[] = "Email Adresse ist bereits vergeben";

    } else {
      try {
        $user-> createUUID();
        $user->freeze();
        $_SESSION['uuid'] = $user-> uuid;
        $_SESSION['email'] = $user-> email;

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
    $user = \Link\User::find(where: ['email' => $_POST['email']]);
    if (is_null($user)) {
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

  $check_user = \Link\User::find(where: ['email' => trim(strtolower($email))]);
  if (is_null($check_user)) {
    $user-> email = trim(strtolower($email));
    return true;
  } else {
    /** email found, should be the same */
    return $user-> ID === $check_user-> ID;
  }
}


