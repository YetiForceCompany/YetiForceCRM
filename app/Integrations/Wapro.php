<?php
/**
 * WAPRO ERP main integration file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations;

/**
 * WAPRO ERP main integration class.
 */
class Wapro
{
	/** @var string Basic table name */
	public const TABLE_NAME = 'i_#__wapro';

	/** @var string Basic table name */
	public const LOG_TABLE_NAME = 'l_#__wapro';

	/** @var string Map relation table name */
	public const RECORDS_MAP_TABLE_NAME = 'u_#__wapro_records_map';

	/** @var array Database config. */
	public $config;

	/** @var array Custom configuration, enables extension of mappings for synchronization. */
	public $customConfig;

	/** @var \App\CronHandler The cron task object available when the timing is called by CRON. */
	public $cron;

	/** @var \App\Db Database instance. */
	private $db;

	/**
	 * Wapro instance constructor.
	 *
	 * @param int                   $serverId
	 * @param \App\CronHandler|null $cron
	 */
	public function __construct(int $serverId, ?\App\CronHandler $cron = null)
	{
		$this->config = self::getById($serverId);
		$this->config['synchronizer'] = $this->config['synchronizer'] ? (\App\Json::decode($this->config['synchronizer']) ?? []) : [];
		$this->customConfig = \App\Config::component('IntegrationWapro', 'config', []);
		$this->db = self::connectToDatabase($this->config['server'], $this->config['database'], $this->config['username'], $this->config['password'], $this->config['port']);
		if ($cron) {
			$this->cron = $cron;
		}
	}

	/**
	 * Get WAPRO ERP configuration by id.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getById(int $id): array
	{
		if (\App\Cache::has(__METHOD__, $id)) {
			return \App\Cache::get(__METHOD__, $id);
		}
		$row = (new \App\Db\Query())->from(self::TABLE_NAME)->where(['id' => $id])->one(\App\Db::getInstance('admin')) ?: [];
		if ($row) {
			$row['password'] = \App\Encryption::getInstance()->decrypt($row['password']);
		}
		\App\Cache::save(__METHOD__, $id, $row);
		return $row;
	}

	/**
	 * Connect to WAPRO ERP SQL Server through PDO.
	 *
	 * @param string $server
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @param int    $port
	 *
	 * @return \App\Db
	 */
	public static function connectToDatabase(string $server, string $database, string $user, string $password, int $port): \App\Db
	{
		$port = $port ?: 1433;
		\App\Db::setConfig([
			'driverName' => 'sqlsrv',
			'dsn' => "sqlsrv:Server={$server},{$port};Database={$database};",
			'username' => $user,
			'password' => $password,
			'port' => $port,
			'charset' => 'utf8',
		], 'wapro');
		$db = \App\Db::getInstance('wapro');
		$db->open();
		return $db;
	}

	/**
	 * Verify access to the WAPRO ERP system database.
	 *
	 * @param string $server
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @param int    $port
	 *
	 * @return array
	 */
	public static function verifyDatabaseAccess(string $server, string $database, string $user, string $password, int $port): array
	{
		try {
			$db = self::connectToDatabase($server, $database, $user, $password, $port);
			if (0 === \count($db->getSchema()->getSchemaNames())) {
				$response = [
					'status' => false,
					'message' => 'There are no schemas in the database',
				];
			} else {
				$row = (new \App\Db\Query())->from('dbo.WAPRODBSTATE')->one($db);
				$response = [
					'status' => !empty($row),
					'message' => empty($row) ? 'No data' : '',
				];
			}
		} catch (\yii\db\Exception $th) {
			$message = $th->getMessage();
			if ($th->errorInfo) {
				foreach ($th->errorInfo as $value) {
					if ($value && !is_numeric($value) && false === strpos($message, $value)) {
						$message .= PHP_EOL . $value;
					}
				}
			}
			$response = [
				'status' => false,
				'message' => $message,
				'code' => $th->getCode(),
			];
		}
		return $response;
	}

	/**
	 * Get information about WAPRO ERP.
	 *
	 * @return string
	 */
	public function getInfo(): string
	{
		$info = '';
		$pdo = $this->db->getSlavePdo();
		$info .= "dbo.WAPRODBSTATE:\n";
		foreach ((new \App\Db\Query())->from('dbo.WAPRODBSTATE')->all($this->db) as $row) {
			$info .= " {$row['PRGNAZWA']}, {$row['PRGWER']}, {$row['DBWER']}, {$row['WARIANT']}\n";
		}
		$info .= "dbo.FIRMA:\n";
		foreach ((new \App\Db\Query())->from('dbo.FIRMA')->all($this->db) as $row) {
			$info .= " {$row['NAZWA_PELNA']}, NIP: {$row['NIP']}, REGON: {$row['REGON']}\n";
		}
		foreach (array_merge($pdo->getAttribute(\PDO::ATTR_SERVER_INFO), $pdo->getAttribute(\PDO::ATTR_CLIENT_VERSION)) as $key => $value) {
			$info .= "$key: $value \n";
		}
		return trim($info);
	}

	/**
	 * Get synchronizers.
	 *
	 * @return Wapro\Synchronizer[]
	 */
	public function getAllSynchronizers(): array
	{
		$synchronizers = [];
		$iterator = new \DirectoryIterator(__DIR__ . '/Wapro/Synchronizer');
		foreach ($iterator as $item) {
			if ($item->isFile() && 'php' === $item->getExtension() && $synchronizer = self::getSynchronizer($item->getBasename('.php'))) {
				$synchronizers[$synchronizer::SEQUENCE] = $synchronizer;
			}
		}
		ksort($synchronizers);
		return $synchronizers;
	}

	/**
	 * Get synchronizer by name.
	 *
	 * @param string $name
	 *
	 * @return Wapro\Synchronizer|null
	 */
	public function getSynchronizer(string $name): ?Wapro\Synchronizer
	{
		$className = "\\App\\Integrations\\Wapro\\Synchronizer\\{$name}";
		return class_exists($className) ? new $className($this) : null;
	}

	/**
	 * Get synchronizers.
	 *
	 * @return Wapro\Synchronizer[]
	 */
	public function getSynchronizers(): array
	{
		$synchronizers = [];
		foreach ($this->config['synchronizer'] as $name) {
			$synchronizer = $this->getSynchronizer($name);
			$synchronizers[$synchronizer::SEQUENCE] = $synchronizer;
		}
		ksort($synchronizers);
		return $synchronizers;
	}

	/**
	 * Database connection instance.
	 *
	 * @return \App\Db
	 */
	public function getDb(): \App\Db
	{
		return $this->db;
	}
}
