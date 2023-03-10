<?php declare(strict_types=1);
namespace Persist\DB;

/**
 * Database – Singelton class for database access
 */
final class Database
{
	/** @param \DB\Database $db */
	private static $db;      // Instance of Database
	/** @param \PDO $connection */
	private \PDO $connection;     // PDO Connection

  /**
	 * constructor
	 * the class is a singleton, and the constructor should not be called from
	 * the outside, hence private
	 */
	private function __construct()
	{
		global $settings;
		
		$dsn = "mysql:host=localhost;dbname={$settings['db']['name']}";
		$db_user = $settings['db']['user'];
		$db_pass = $settings['db']['passwort'];
		$db_options = 
			[ \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
			, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_BOUND
			, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT
			, \PDO::ATTR_PERSISTENT => true
			, \PDO::ATTR_EMULATE_PREPARES => false
			];

		try {
			$this-> connection =  new \PDO( $dsn, $db_user, $db_pass, $db_options );
		} catch( \PDOException $e ) {
			throw new DatabaseException( DatabaseException::ERR_CONNECTION, null, $e-> getMessage() );
		}
	}

	/**
	 * Retrieve the connection object
	 * construct the first time
	 * @return \PDO
	 */
	public static function getConnection(): \PDO
	{
		if( static::$db == null ) {
			static::$db = new Database();
		}
		return static::$db->connection;
	}

  /**
	 * create a customized Exception
	 * @return DatabaseException
	 */
	public function getException(): DatabaseException
	{
		return new DatabaseException( 0x2100, null, $this->connection->errorInfo()[2] );
	}
}
