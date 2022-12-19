<?php declare(strict_types=1);

namespace Link;

use DateTime;

class Code extends \Persist\Base
  implements \Persist\IPersist
{
  use \Persist\DB\DBPersistTrait;

  protected ?int $user_id;
  protected ?string $code;
  protected ?string $url;
  protected ?\DateTime $last_used;
  protected ?int $hits;


  static public function getPrimaryKey(): string { return 'code'; }
  static public function getTableName(): string { return 'code'; }
  static public function getFields(): array
  {
    return [
      'user_id' => ['int', 10],
      'code' => ['string', 5],
      'url' => ['string', 4096],
      'last_used' => ['\DateTime',0],
      'hits' => ['int', 10],
    ];
  }
  public function __toString()
  {
    return $this-> code;
  }
}