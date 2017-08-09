<?php
namespace App;

/**
 * Encryption basic class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Encryption
{

	protected $method = false;
	protected $pass = false;
	protected $vector = false;
	protected $options = true;

	/**
	 * Class contructor
	 */
	public function __construct()
	{
		$row = (new \App\Db\Query())->from('a_#__encryption')->one(\App\Db::getInstance('admin'));
		if ($row) {
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
