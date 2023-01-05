<?php declare(strict_types=1);

use Persist\DB\Database;

require_once '../inc/session.inc.php';
require_once '../inc/utils.inc.php';
require_once '../inc/settings.inc.php';

$messages = [];

if( isset($_POST['action']) ) {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $user = \Link\User::find(where: ['username' => $username]);
  $user_email = \Link\UserEmail::find(where: ['email' => $email]);
  if( !is_null($user) ) {
    $messages[] = "Benutzername bereits vergeben";
  } elseif( !is_null($user_email) ) {
    $messages[] = "Email Adresse ist bereits vergeben";

  } else {
    try {
      $user_email = new \Link\UserEmail();
      $user_email-> username = $username;
      $user_email-> email = $email;
      $user_email-> createUUID();
      $user_email-> confirm_date = null;
      $user_email-> register_date = new \DateTime();
      $user_email->freeze();
      $_SESSION['username'] = $user_email-> username;
      $_SESSION['uuid'] = $user_email-> uuid;
      $_SESSION['email'] = $user_email-> email;

      header('Location:sendpasswordemail.php');
      exit(0);
    } catch ( \Exception $e ) {
      $messages[] = "Fehler beim Password setzen. Bitte Administrator kontaktieren.{$e->getMessage()}";
    }
  }
}
require_once "../inc/header.inc.php";?>
<main>

  <h1>go321</h1>
  <h2>Konto erstellen</h2>
  <dialog open>
  <form method="post" id="form-container">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" value="<?= $_SESSION['username'] ?? '' ?>" autofocus required>
    <label for="email">Email-Addresse</label>
    <input id="email" name="email" type="email" placeholder="E-mail" pattern="^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$" required>
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

</html>