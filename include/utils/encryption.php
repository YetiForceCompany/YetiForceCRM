<?php namespace includes\utils;

/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

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
