<?php
namespace App;

/**
 * Encryption basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Encryption
{

	protected $method = false;
	protected $pass = false;
	protected $vector = false;
	protected $options = true;

	public function __construct()
	{
		$db = \PearDatabase::getInstance();
		$result = $db->query('SELECT * FROM a_yf_encryption');
		if ($row = $db->getRow($result)) {
			$this->method = $row['method'];
			$this->vector = $row['pass'];
			$this->pass = \AppConfig::securityKeys('encryptionPass');
		}
	}

	public function encrypt($decrypted)
	{
		if (!$this->isActive()) {
			return $decrypted;
		}
		$encrypted = openssl_encrypt($decrypted, $this->method, $this->pass, $this->options, $this->vector);
		return base64_encode($encrypted);
	}

	public function decrypt($encrypted)
	{
		if (!$this->isActive()) {
			return $encrypted;
		}
		$decrypted = openssl_decrypt(base64_decode($encrypted), $this->method, $this->pass, $this->options, $this->vector);
		return $decrypted;
	}

	public function getMethods()
	{
		return openssl_get_cipher_methods();
	}

	public function isActive()
	{
		if (!function_exists('openssl_encrypt')) {
			return false;
		} elseif (empty($this->method)) {
			return false;
		} elseif ($this->method != \AppConfig::securityKeys('encryptionMethod')) {
			return false;
		} elseif (!in_array($this->method, $this->getMethods())) {
			return false;
		}
		return true;
	}
}
