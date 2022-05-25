<?php
/**
 * Encryption basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	/** @var int The encryption ID for the configuration */
	public const TARGET_SETTINGS = 0;
	/** @var string Table name */
	public const TABLE_NAME = 'a_#__encryption';
	/** @var int Encryption status */
	public const STATUS_ACTIVE = 1;
	/** @var int Encryption status */
	public const STATUS_WORKING = 2;

	/** @var array Recommended encryption methods */
	public static $recommendedMethods = [
		'aes-256-cbc', 'aes-256-ctr', 'aes-192-cbc', 'aes-192-ctr',
	];

	/**
	 * Function to get instance.
	 *
	 * @param int $target self::TARGET_SETTINGS or module ID
	 *
	 * @return self
	 */
	public static function getInstance(int $target = self::TARGET_SETTINGS)
	{
		if (Cache::has('Encryption', $target)) {
			return Cache::get('Encryption', $target);
		}
		if (self::TARGET_SETTINGS === $target) {
			$instance = \App\Encryptions\Settings::getInstance($target);
		} else {
			$instance = \App\Encryptions\Module::getInstance($target);
		}
		Cache::save('Encryption', $target, $instance, Cache::LONG);
		return $instance;
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
	 * Get vector.
	 *
	 * @return string
	 */
	public function getVector(): string
	{
		return $this->get('vector') ?? '';
	}

	/**
	 * Get target ID.
	 *
	 * @return int
	 */
	public function getTarget()
	{
		return $this->get('target');
	}

	/**
	 * Get method.
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->get('method');
	}

	/**
	 * Get options or options default value(0).
	 *
	 * @return int
	 */
	public function getOptions(): int
	{
		return $this->get('options') ?? 0;
	}

	/**
	 * Function to encrypt data.
	 *
	 * @param string $decrypted
	 * @param bool   $testMode
	 *
	 * @return string
	 */
	public function encrypt($decrypted, bool $testMode = false)
	{
		if (!$this->isActive($testMode)) {
			return $decrypted;
		}
		$encrypted = openssl_encrypt($decrypted, $this->getMethod(), $this->get('pass'), $this->getOptions(), $this->get('vector'));
		return base64_encode($encrypted);
	}

	/**
	 * Function to decrypt data.
	 *
	 * @param string $encrypted
	 * @param bool   $testMode
	 *
	 * @return string
	 */
	public function decrypt($encrypted, bool $testMode = false)
	{
		if (!$this->isActive($testMode)) {
			return $encrypted;
		}
		return openssl_decrypt(base64_decode($encrypted), $this->getMethod(), $this->get('pass'), $this->getOptions(), $this->get('vector'));
	}

	/**
	 * Returns list method of encryption.
	 *
	 * @return string[]
	 */
	public static function getMethods()
	{
		return array_filter(openssl_get_cipher_methods(), fn ($methodName) => false === stripos($methodName, 'gcm') && false === stripos($methodName, 'ccm'));
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
		return false;
	}

	/**
	 * Check if the encryption change has been set.
	 *
	 * @return bool
	 */
	public function isReady(): bool
	{
		return (new \App\Db\Query())->from('s_#__batchmethod')->where(['method' => static::class . '::recalculatePasswords', 'status' => \App\BatchMethod::STATUS_ENABLED])->exists();
	}

	/**
	 * Check if the encryption change has started.
	 *
	 * @return bool
	 */
	public function isRunning(): bool
	{
		$result = (new \App\Db\Query())->from('s_#__batchmethod')->where(['method' => static::class . '::recalculatePasswords', 'status' => [\App\BatchMethod::STATUS_ENABLED, \App\BatchMethod::STATUS_RUNNING, \App\BatchMethod::STATUS_HALTED, \App\BatchMethod::STATUS_COMPLETED]])->exists();
		return $result || (new \App\Db\Query())->from(self::TABLE_NAME)->where(['target' => $this->getTarget(), 'status' => self::STATUS_WORKING])->exists();
	}

	/**
	 * Encryption change.
	 */
	public function reload()
	{
		$db = \App\Db::getInstance('admin');
		\App\BatchMethod::deleteByMethod(static::class . '::recalculatePasswords');
		(new \App\BatchMethod(['method' => static::class . '::recalculatePasswords', 'params' => [$this->get('method'), $this->get('pass'), $this->get('vector'), $this->getTarget()]]))->save();
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where(['target' => $this->getTarget()])->exists($db)) {
			$db->createCommand()->insert(self::TABLE_NAME, ['method' => '', 'pass' => '', 'target' => $this->getTarget(), 'status' => self::STATUS_WORKING])->execute();
		} else {
			$db->createCommand()->update(self::TABLE_NAME, ['status' => self::STATUS_WORKING], ['target' => $this->getTarget()])->execute();
		}
		\App\Cache::delete('Encryption', $this->getTarget());
	}

	/**
	 * Generate random password.
	 *
	 * @param int   $length
	 * @param mixed $type
	 *
	 * @return string
	 */
	public static function generatePassword($length = 10, $type = 'lbd')
	{
		$chars = [];
		if (false !== strpos($type, 'l')) {
			$chars[] = 'abcdefghjkmnpqrstuvwxyz';
		}
		if (false !== strpos($type, 'b')) {
			$chars[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		}
		if (false !== strpos($type, 'd')) {
			$chars[] = '0123456789';
		}
		if (false !== strpos($type, 's')) {
			$chars[] = '!"#$%&\'()*+,-./:;<=>?@[\]^_{|}';
		}
		$password = $allChars = '';
		foreach ($chars as $char) {
			$allChars .= $char;
			$password .= $char[array_rand(str_split($char))];
		}
		$allChars = str_split($allChars);
		$missing = $length - \count($chars);
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
		if ('true' === $passDetail['numbers']) {
			$type .= 'd';
		}
		if ('true' === $passDetail['big_letters']) {
			$type .= 'b';
		}
		if ('true' === $passDetail['special']) {
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
		return crypt($text, '$1$' . \App\Config::main('application_unique_key'));
	}

	/**
	 * Function to create a password hash.
	 *
	 * @param string $text
	 * @param string $pepper
	 *
	 * @return string
	 */
	public static function createPasswordHash(string $text, string $pepper): string
	{
		return password_hash(hash_hmac('sha256', $text, $pepper . \App\Config::main('application_unique_key')), \defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
	}

	/**
	 * Check password hash.
	 *
	 * @param string $password
	 * @param string $hash
	 * @param string $pepper
	 *
	 * @return bool
	 */
	public static function verifyPasswordHash(string $password, string $hash, string $pepper): bool
	{
		return password_verify(hash_hmac('sha256', $password, $pepper . \App\Config::main('application_unique_key')), $hash);
	}
}
