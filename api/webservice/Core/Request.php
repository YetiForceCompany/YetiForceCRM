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
	 * Requested content type.
	 *
	 * @var string
	 */
	public $contentType;
	/**
	 * List of headings and sanitization methods.
	 *
	 * @var array
	 */
	public $headersPurifierMap = [
		'x-token' => \App\Purifier::ALNUM,
		'x-api-key' => \App\Purifier::ALNUM,
		'x-raw-data' => \App\Purifier::INTEGER,
		'authorization' => \App\Purifier::ALNUM_EXTENDED,
		'x-parent-id' => \App\Purifier::INTEGER,
		'encrypted' => \App\Purifier::INTEGER,
		'x-row-limit' => \App\Purifier::INTEGER,
		'x-row-offset' => \App\Purifier::INTEGER,
		'x-unit-price' => \App\Purifier::INTEGER,
		'x-unit-gross' => \App\Purifier::INTEGER,
		'x-product-bundles' => \App\Purifier::INTEGER,
		'x-row-order-field' => \App\Purifier::ALNUM_EXTENDED,
		'x-row-order' => \App\Purifier::ALNUM,
		'x-start-with' => \App\Purifier::INTEGER,
	];

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
			static::$request->contentType = isset($_SERVER['CONTENT_TYPE']) ? static::$request->getServer('CONTENT_TYPE') : static::$request->getHeader('content-type');
			if (empty(static::$request->contentType)) {
				static::$request->contentType = static::$request->getHeader('accept');
			}
		}
		return static::$request;
	}

	public function getData()
	{
		if ('GET' === $this->getRequestMethod()) {
			return $this;
		}
		$encrypted = $this->getHeader('encrypted');
		$content = file_get_contents('php://input');
		if (\App\Config::api('ENCRYPT_DATA_TRANSFER') && $encrypted && 1 === (int) $encrypted) {
			$content = $this->decryptData($content);
		}
		if (empty($content)) {
			return false;
		}
		$this->rawValues = array_merge($this->contentParse($content), $this->rawValues);
		return $this;
	}

	public function contentParse($content)
	{
		$type = $this->contentType;
		if (!empty($type)) {
			$type = explode('/', $type);
			$type = array_pop($type);
		}
		$return = [];
		switch ($type) {
			case 'json':
				$return = json_decode($content, 1);
				break;
			case 'form-data':
			case 'x-www-form-urlencoded':
				mb_parse_str($content, $data);
				$return = $data;
				break;
		}
		return $return;
	}

	public function decryptData($data)
	{
		$privateKey = 'file://' . ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Config::api('PRIVATE_KEY');
		if (!$privateKey = openssl_pkey_get_private($privateKey)) {
			throw new \App\Exceptions\AppException('Private Key failed');
		}
		$privateKey = openssl_pkey_get_private($privateKey);
		openssl_private_decrypt($data, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

		return $decrypted;
	}
}
