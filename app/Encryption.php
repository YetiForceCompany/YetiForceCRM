<?php
/**
 * Encryption basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class to encrypt and decrypt data.
 */
class Encryption extends Base
{
	/** @var array Passwords to encrypt */
	private static $mapPasswords = [
		'roundcube_users' => ['columnName' => ['password'], 'index' => 'user_id', 'db' => 'base'],
		's_#__mail_smtp' => ['columnName' => ['password', 'smtp_password'], 'index' => 'id', 'db' => 'admin'],
		'a_#__smsnotifier_servers' => ['columnName' => ['api_key'], 'index' => 'id', 'db' => 'admin'],
		'u_#__github' => ['columnName' => ['token'], 'index' => 'github_id', 'db' => 'base'],
		'w_#__portal_user' => ['columnName' => ['password_t'], 'index' => 'id', 'db' => 'webservice'],
		'w_#__servers' => ['columnName' => ['pass', 'api_key'], 'index' => 'id', 'db' => 'webservice'],
		'dav_users' => ['columnName' => ['key'], 'index' => 'id', 'db' => 'base'],
	];
	/**
	 * @var array Recommended encryption methods
	 */
	public static $recomendedMethods = [
		'aes-256-cbc', 'aes-256-ctr', 'aes-192-cbc', 'aes-192-ctr'
	];

	/**
	 * Function to get instance.
	 */
	public static function getInstance()
	{
		if (Cache::has('Encryption', 'Instance')) {
			return Cache::get('Encryption', 'Instance');
		}
		$row = (new \App\Db\Query())->from('a_#__encryption')->one(\App\Db::getInstance('admin'));
		$instance = new static();
		if ($row) {
			$instance->set('method', $row['method']);
			$instance->set('vector', $row['pass']);
		}
		$instance->set('pass', \AppConfig::securityKeys('encryptionPass'));
		Cache::save('Encryption', 'Instance', $instance, Cache::LONG);
		return $instance;
	}

	/**
	 * Function to change password for encryption.
	 *
	 * @param string $method
	 * @param string $password
	 *
	 * @throws \Exception
	 * @throws Exceptions\AppException
	 */
	public static function recalculatePasswords($method, $password)
	{
		$decryptInstance = static::getInstance();
		if ($decryptInstance->get('method') === $method && $decryptInstance->get('vector') === $password) {
			return;
		}
		$oldMethod = $decryptInstance->get('method');
		$dbAdmin = Db::getInstance('admin');
		$transactionAdmin = $dbAdmin->beginTransaction();
		$transactionBase = Db::getInstance()->beginTransaction();
		$transactionWebservice = Db::getInstance('webservice')->beginTransaction();
		try {
			$passwords = [];
			foreach (self::$mapPasswords as $tableName => $info) {
				$values = (new Db\Query())->select(array_merge([$info['index']], $info['columnName']))
					->from($tableName)
					->createCommand(Db::getInstance($info['db']))
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
									throw new Exceptions\AppException('ERR_IMPOSSIBLE_DECRYPT');
								}
							}
						}
					}
				}
				$passwords[$tableName] = $values;
			}
			if (!$decryptInstance->isActive()) {
				$dbAdmin->createCommand()->insert('a_#__encryption', ['method' => $method, 'pass' => $password])->execute();
			} elseif (empty($method) && empty($password)) {
				$dbAdmin->createCommand()->delete('a_#__encryption')->execute();
			} else {
				$dbAdmin->createCommand()->update('a_#__encryption', ['method' => $method, 'pass' => $password])->execute();
			}
			$configFile = new ConfigFile('securityKeys');
			$configFile->set('encryptionMethod', $method);
			$configFile->create();
			Cache::clear();
			$encryptInstance = static::getInstance();
			foreach ($passwords as $tableName => $pass) {
				$dbCommand = Db::getInstance(self::$mapPasswords[$tableName]['db'])->createCommand();
				foreach ($pass as $index => $values) {
					foreach ($values as &$value) {
						if (!empty($value)) {
							$value = $encryptInstance->encrypt($value);
							if (empty($value)) {
								throw new Exceptions\AppException('ERR_IMPOSSIBLE_ENCRYPT');
							}
						}
					}
					$dbCommand->update($tableName, $values, [self::$mapPasswords[$tableName]['index'] => $index])->execute();
				}
			}
			$transactionWebservice->commit();
			$transactionBase->commit();
			$transactionAdmin->commit();
		} catch (\Throwable $e) {
			$transactionWebservice->rollBack();
			$transactionBase->rollBack();
			$transactionAdmin->rollBack();
			$configFile = new ConfigFile('securityKeys');
			$configFile->set('encryptionMethod', $oldMethod);
			$configFile->create();
			throw $e;
		}
	}

	/**
	 * Specifies the length of the vector.
	 *
	 * @param string $method
	 *
	 * @return int
	 */
	public static function getLengthVector($method)
	{
		return openssl_cipher_iv_length($method);
	}

	/**
	 * Function to encrypt data.
	 *
	 * @param string $decrypted
	 *
	 * @return string
	 */
	public function encrypt($decrypted)
	{
		if (!$this->isActive()) {
			return $decrypted;
		}
		$encrypted = openssl_encrypt($decrypted, $this->get('method'), $this->get('pass'), $this->get('options'), $this->get('vector'));
		return base64_encode($encrypted);
	}

	/**
	 * Function to decrypt data.
	 *
	 * @param string $encrypted
	 *
	 * @return string
	 */
	public function decrypt($encrypted)
	{
		if (!$this->isActive()) {
			return $encrypted;
		}
		return openssl_decrypt(base64_decode($encrypted), $this->get('method'), $this->get('pass'), $this->get('options'), $this->get('vector'));
	}

	/**
	 * Returns list method of encryption.
	 *
	 * @return string[]
	 */
	public static function getMethods()
	{
		return array_filter(openssl_get_cipher_methods(), function ($methodName) {
			return stripos($methodName, 'gcm') === false && stripos($methodName, 'ccm') === false;
		});
	}

	/**
	 * Checks if encrypt or decrypt is possible.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		if (!\function_exists('openssl_encrypt') || $this->isEmpty('method') || $this->get('method') !== \AppConfig::securityKeys('encryptionMethod') || !\in_array($this->get('method'), static::getMethods())) {
			return false;
		}
		return true;
	}

	/**
	 * Generate random password.
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function generatePassword($length = 10, $type = 'lbd')
	{
		$chars = [];
		if (strpos($type, 'l') !== false) {
			$chars[] = 'abcdefghjkmnpqrstuvwxyz';
		}
		if (strpos($type, 'b') !== false) {
			$chars[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		}
		if (strpos($type, 'd') !== false) {
			$chars[] = '0123456789';
		}
		if (strpos($type, 's') !== false) {
			$chars[] = '!"#$%&\'()*+,-./:;<=>?@[\]^_{|}';
		}
		$password = $allChars = '';
		foreach ($chars as $char) {
			$allChars .= $char;
			$password .= $char[array_rand(str_split($char))];
		}
		$allChars = str_split($allChars);
		$missing = $length - count($chars);
		for ($i = 0; $i < $missing; ++$i) {
			$password .= $allChars[array_rand($allChars)];
		}
		return str_shuffle($password);
	}

	/**
	 * Generate user password.
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function generateUserPassword($length = 10)
	{
		$passDetail = \Settings_Password_Record_Model::getUserPassConfig();
		if ($length > $passDetail['max_length']) {
			$length = $passDetail['max_length'];
		}
		if ($length < $passDetail['min_length']) {
			$length = $passDetail['min_length'];
		}
		$type = 'l';
		if ($passDetail['numbers'] === 'true') {
			$type .= 'd';
		}
		if ($passDetail['big_letters'] === 'true') {
			$type .= 'b';
		}
		if ($passDetail['special'] === 'true') {
			$type .= 's';
		}
		return static::generatePassword($length, $type);
	}

	/**
	 * Function to create a hash.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function createHash($text)
	{
		return crypt($text, '$1$' . \AppConfig::main('application_unique_key'));
	}
}
