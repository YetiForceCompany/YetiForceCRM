<?php
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

	function __construct()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT * FROM a_yf_encryption');
		if ($row = $db->getRow($result)) {
			$this->method = $row['method'];
			$this->vector = $row['pass'];
			$this->pass = AppConfig::securityKeys('encryptionPass');
		}
	}

	function encrypt($decrypted)
	{
		if (!$this->isActive()) {
			return $decrypted;
		}
		$encrypted = openssl_encrypt($decrypted, $this->method, $this->pass, $this->options, $this->vector);
		return base64_encode($encrypted);
	}

	function decrypt($encrypted)
	{
		if (!$this->isActive()) {
			return $encrypted;
		}
		$decrypted = openssl_decrypt(base64_decode($encrypted), $this->method, $this->pass, $this->options, $this->vector);
		return $decrypted;
	}

	function getMethods()
	{
		return openssl_get_cipher_methods();
	}

	function isActive()
	{
		if (!function_exists('openssl_encrypt')) {
			return false;
		} elseif (empty($this->method)) {
			return false;
		} elseif ($this->method != AppConfig::securityKeys('encryptionMethod')) {
			return false;
		} elseif (!in_array($this->method, $this->getMethods())) {
			return false;
		}
		return true;
	}
}
