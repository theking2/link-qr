<?php declare(strict_types=1);

namespace Link;

use DateTime;

class UserEmail extends \Persist\Base
  implements \Persist\IPersist
{
  use \Persist\DB\DBPersistTrait;

  protected ?string $username;
  protected ?string $email;
  protected ?string $uuid;
  protected ?\DateTime $confirm_date;
  protected ?\DateTime $register_date;


  static public function getPrimaryKey(): string { return 'username'; }
  static public function getTableName(): string { return 'user_email'; }
  static public function getFields(): array
  {
    return [
      'username' => ['string', 50],
      'email' => ['string', 50],
      'uuid' => ['string', 64],
      'confirm_date' => ['\DateTime'],
      'register_date' => ['\DateTime'],
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
}