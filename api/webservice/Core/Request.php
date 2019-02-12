<?php

namespace Api\Core;

/**
 * Web service request class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Request extends \App\Request
{
	/**
	 * Static instance initialization.
	 *
	 * @param bool|array $request
	 *
	 * @return Request
	 */
	public static function init($request = false)
	{
		if (!static::$request) {
			static::$request = new self($request ? $request : $_REQUEST);
		}
		return static::$request;
	}

	public function getData()
	{
		if ($this->getRequestMethod() === 'GET') {
			return $this;
		} else {
			$encrypted = $this->getHeader('encrypted');
			$content = file_get_contents('php://input');
			if (\AppConfig::api('ENCRYPT_DATA_TRANSFER') && $encrypted && (int) $encrypted === 1) {
				$content = $this->decryptData($content);
			}
		}
		if (empty($content)) {
			return false;
		}
		$this->rawValues = array_merge($this->contentParse($content), $this->rawValues);

		return $this;
	}

	public function contentParse($content)
	{
		$type = isset($_SERVER['CONTENT_TYPE']) ? $this->getServer('CONTENT_TYPE') : $this->getHeader('content-type');
		if (empty($type)) {
			$type = $this->getHeader('accept');
		}
		if (!empty($type)) {
			$type = explode('/', $type);
			$type = array_pop($type);
		}
		switch ($type) {
			case 'form-data':
				parse_str($content, $data);

				return $data;
			case 'json':
			default:
				return json_decode($content, 1);
		}
	}

	public function decryptData($data)
	{
		$privateKey = 'file://' . ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \AppConfig::api('PRIVATE_KEY');
		if (!$privateKey = openssl_pkey_get_private($privateKey)) {
			throw new \App\Exceptions\AppException('Private Key failed');
		}
		$privateKey = openssl_pkey_get_private($privateKey);
		openssl_private_decrypt($data, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

		return $decrypted;
	}
}
