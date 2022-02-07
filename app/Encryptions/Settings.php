<?php
/**
 * Encryption for configuration items.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Encryptions;

/**
 * Class for encrypting configuration items.
 */
class Settings extends \App\Encryption
{
	/** @var array Passwords to encrypt */
	private static $mapPasswords = [
		'roundcube_users' => ['columnName' => ['password'], 'index' => 'user_id', 'db' => 'base'],
		's_#__mail_smtp' => ['columnName' => ['password', 'smtp_password'], 'index' => 'id', 'db' => 'admin'],
		'a_#__smsnotifier_servers' => ['columnName' => ['api_key'], 'index' => 'id', 'db' => 'admin'],
		'w_#__api_user' => ['columnName' => ['auth'], 'index' => 'id', 'db' => 'webservice'],
		'w_#__portal_user' => ['columnName' => ['auth'], 'index' => 'id', 'db' => 'webservice'],
		'w_#__servers' => ['columnName' => ['pass', 'api_key'], 'index' => 'id', 'db' => 'webservice'],
		'dav_users' => ['columnName' => ['key'], 'index' => 'id', 'db' => 'base'],
		\App\MeetingService::TABLE_NAME => ['columnName' => ['secret'], 'index' => 'id', 'db' => 'admin'],
		'i_#__magento_servers' => ['columnName' => ['password'], 'index' => 'id', 'db' => 'admin'],
	];

	/** {@inheritdoc} */
	public static function getInstance(int $target = self::TARGET_SETTINGS)
	{
		if ($target !== static::TARGET_SETTINGS) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||target', 406);
		}
		$row = (new \App\Db\Query())->from(static::TABLE_NAME)->where(['target' => $target])->one(\App\Db::getInstance('admin'));
		$instance = new static();
		if ($row) {
			$instance->set('method', $row['method']);
			$instance->set('vector', $row['pass']);
			$instance->set('target', (int) $row['target']);
		}
		$instance->set('pass', \App\Config::securityKeys('encryptionPass'));
		return $instance;
	}

	/**
	 * Function to change password for encryption.
	 *
	 * @param string $method
	 * @param string $password
	 * @param string $vector
	 */
	public static function recalculatePasswords(string $method, string $password, string $vector)
	{
		$dbAdmin = \App\Db::getInstance('admin');
		$decryptInstance = static::getInstance();
		if ($decryptInstance->get('method') === $method && $decryptInstance->get('vector') === $vector && $decryptInstance->get('pass') === $password) {
			$dbAdmin->createCommand()->update(self::TABLE_NAME, ['status' => self::STATUS_ACTIVE], ['target' => $decryptInstance->getTarget()])->execute();
			return;
		}
		$oldMethod = $decryptInstance->get('method');
		$transactionAdmin = $dbAdmin->beginTransaction();
		$transactionBase = \App\Db::getInstance()->beginTransaction();
		try {
			$passwords = [];
			foreach (self::$mapPasswords as $tableName => $info) {
				$values = (new \App\Db\Query())->select(array_merge([$info['index']], $info['columnName']))
					->from($tableName)
					->createCommand(\App\Db::getInstance($info['db']))
					->queryAllByGroup(1);
				if (!$values) {
					continue;
				}
				if ($decryptInstance->isActive()) {
					foreach ($values as &$columns) {
						foreach ($columns as &$value) {
							if (!empty($value)) {
								$value = $decryptInstance->decrypt($value);
								if (empty($value)) {
									throw new \App\Exceptions\AppException('ERR_IMPOSSIBLE_DECRYPT');
								}
							}
						}
					}
				}
				$passwords[$tableName] = $values;
			}

			$dbAdmin->createCommand()->update(self::TABLE_NAME, ['method' => $method, 'pass' => $vector], ['target' => self::TARGET_SETTINGS])->execute();
			$configFile = new \App\ConfigFile('securityKeys');
			$configFile->set('encryptionMethod', $method);
			$configFile->set('encryptionPass', $password);
			$configFile->create();
			\App\Cache::clear();
			\App\Config::set('securityKeys', 'encryptionMethod', $method);
			\App\Config::set('securityKeys', 'encryptionPass', $password);
			$encryptInstance = static::getInstance();
			foreach ($passwords as $tableName => $pass) {
				$dbCommand = \App\Db::getInstance(self::$mapPasswords[$tableName]['db'])->createCommand();
				foreach ($pass as $index => $values) {
					foreach ($values as &$value) {
						if (!empty($value)) {
							$value = $encryptInstance->encrypt($value);
							if (empty($value)) {
								throw new \App\Exceptions\AppException('ERR_IMPOSSIBLE_ENCRYPT');
							}
						}
					}
					$dbCommand->update($tableName, $values, [self::$mapPasswords[$tableName]['index'] => $index])->execute();
				}
			}
			$dbAdmin->createCommand()->update(self::TABLE_NAME, ['status' => self::STATUS_ACTIVE], ['target' => self::TARGET_SETTINGS])->execute();

			$transactionBase->commit();
			$transactionAdmin->commit();
		} catch (\Throwable $e) {
			$transactionBase->rollBack();
			$transactionAdmin->rollBack();
			$configFile = new \App\ConfigFile('securityKeys');
			$configFile->set('encryptionMethod', $oldMethod);
			$configFile->create();
			throw $e;
		}
	}

	/**
	 * Checks if encrypt or decrypt is possible.
	 *
	 * @param bool $testMode
	 *
	 * @return bool
	 */
	public function isActive(bool $testMode = false)
	{
		$method = \App\Config::securityKeys('encryptionMethod');
		return !(
			!\function_exists('openssl_encrypt')
			|| $this->isEmpty('method')
			|| ($this->get('method') !== $method && !$testMode)
			|| !\in_array($this->get('method'), static::getMethods())
		);
	}
}
