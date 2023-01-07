<?php declare(strict_types=1);

use Persist\DB\Database;

require_once '../inc/session.inc.php';
require_once '../inc/utils.inc.php';
require_once '../inc/settings.inc.php';

$messages = [];
$email = '';
$username = '';

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
      header('Location:sendpasswordemail.php');
      exit(0);
    }
  } else {
    /**
     * find user by email if provided
     * @var \Link\User $user
     */
    if (is_null( $user = \Link\UserEmail::find(where: ['email'=> $_POST['email']]) )) {
      $messages[] = "Email Adresse nicht gefunden";

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
} else {
  // if logged on find the email
  if( array_key_exists('username',$_SESSION) ) {
    $last = Database::getConnection()->prepare("select email from vw_user_email where username=:username");
    $last-> execute(['username'=> $_SESSION['username'] ]);
    $email = $last-> fetchColumn(0)??'';
  }
}
require_once "../inc/header.inc.php";?>
<main>
<h1>go321</h1>
<dialog open>
  <h2>Password setzen</h2>
  <form method="post" id="form-container">
    <label for="username">Username</label>
    <input type="text" disabled name="username" id="username" value="<?= $_SESSION['username'] ?? '' ?>">
    <label for="email">Email-Addresse</label>
    <input id="email" name="email" type="email"
      value="<?=$email?>"
      placeholder="E-mail" pattern="^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$"
      autofocus="autofocus" required
    >

    <span></span>
    <input name="action" type="submit" value="Senden">

    <span></span>
    <p><a href="index.php">Anmelden</a></p>
  </form>
  <?php
  if ($messages) {

    foreach ($messages as $message) {
      echo '<h2>' . $message . '</h2>';
    }
  } ?>

</dialog>
</main>
</body>

</html><?php

/**
 * Check if the email adress is unique
 * @return bool
 */
function check_email_unique(string $email):bool {
  global $user;

  // check if email is currently used by the user
  $last = Database::getConnection()->prepare("select email from vw_user_email where username=:username");
  $last-> execute(['username'=> $_SESSION['username'] ]);
  if($email === $last-> fetchColumn() ) {
    $user_email = \Link\UserEmail::find(where: ['username'=> $_SESSION['username'],'email' => $email]);
    $user_email-> createUUID();
    $user_email-> freeze();

    $_SESSION['uuid'] = $user_email-> uuid;
    $_SESSION['email'] = $user_email-> email;

    return true;
  }

  // check if email was in use by current user
  $user_email = \Link\UserEmail::find(
    where: ['username'=> $user->username,'email' => trim(strtolower($email))]
  );
  if ($user_email) {
    // start reuse of email
    $user_email-> createUUID();
    $user_email-> confirm_date = null;
    $user_email-> register_date = new \DateTime();
    $user_email-> freeze();

    $_SESSION['uuid'] = $user_email-> uuid;
    $_SESSION['email'] = $user_email-> email;
    
    return true;
  }

  // check if email is in use by another user
  $user_email = \Link\UserEmail::find(where: ['email' => trim(strtolower($email))]);
  return !is_null($user_email); // if we found one it is not unique
}


