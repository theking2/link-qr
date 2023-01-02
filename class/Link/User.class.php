<?php declare(strict_types=1);

namespace Link;

use DateTime;

class User extends \Persist\Base
  implements \Persist\IPersist
{
  use \Persist\DB\DBPersistTrait;

  protected ?int $id;
  protected ?string $username;
  protected ?string $vorname;
  protected ?string $nachname;
  protected ?string $hash;
  protected ?\DateTime $last_login;


  static public function getPrimaryKey(): string { return 'id'; }
  static public function getTableName(): string { return 'user'; }
  static public function getFields(): array
  {
    return [
      'id' => ['int', 10],
      'username' => ['string', 50],
      'vorname' => ['string', 30],
      'nachname' => ['string', 30],
      'hash' => ['string', 255],
      'last_login' => ['\DateTime'],
    ];
  }
  public function __toString()
  {
    return sprintf( '%s [%s %s]',
      $this->username, $this-> vorname, $this-> nachname
    );
  }

  /**
   * Have the database create a new UUID for this user
   * @return bool
   */
  public function createUUID()
  {
    $this-> __set( 'uuid', base64url_encode(random_bytes(48)) );
  }
  /**
   * Set the hash for this user's password
   */
  public function setPasswordHash(string $password) {
    // use the setter to mark as dirty
    $this-> __set('hash', password_hash($password, PASSWORD_ARGON2ID) );
  }
  /**
   * Check the password against the hash or to the old style password
   */
  public function checkPassword(?string $password): bool
  {
    return password_verify($password, $this->hash);
  }

}