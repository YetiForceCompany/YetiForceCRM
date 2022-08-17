<?php
/**
 * Web service request file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Core;

/**
 * Web service request class.
 */
class Request extends \App\Request
{
	/** @var string Requested content type. */
	public $contentType;

	/** @var array The content of the request. */
	public $content = [];

	/** @var array List of headings and sanitization methods. */
	public $headersPurifierMap = [
		'encrypted' => \App\Purifier::INTEGER,
		'authorization' => \App\Purifier::ALNUM_EXTENDED,
		'x-token' => \App\Purifier::ALNUM,
		'x-api-key' => \App\Purifier::ALNUM,
		'x-raw-data' => \App\Purifier::INTEGER,
		'x-parent-id' => \App\Purifier::INTEGER,
		'x-row-limit' => \App\Purifier::INTEGER,
		'x-row-offset' => \App\Purifier::INTEGER,
		'x-unit-price' => \App\Purifier::INTEGER,
		'x-unit-gross' => \App\Purifier::INTEGER,
		'x-product-bundles' => \App\Purifier::INTEGER,
		'x-start-with' => \App\Purifier::INTEGER,
		'x-only-column' => \App\Purifier::INTEGER,
		'x-row-count' => \App\Purifier::INTEGER,
		'x-cv-id' => \App\Purifier::INTEGER,
		'x-header-fields' => \App\Purifier::INTEGER,
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
			static::$request = new self($request ?: $_REQUEST);
			static::$request->contentType = isset($_SERVER['CONTENT_TYPE']) ? static::$request->getServer('CONTENT_TYPE') : static::$request->getHeader('content-type');
			if (empty(static::$request->contentType)) {
				static::$request->contentType = static::$request->getHeader('accept');
			}
		}
		return static::$request;
	}

	/**
	 * Load data from request.
	 *
	 * @return $this
	 */
	public function loadData(): self
	{
		if ('GET' === self::getRequestMethod()) {
			return $this;
		}
		$encrypted = $this->getHeader('encrypted');
		$content = file_get_contents('php://input');
		if (\App\Config::api('ENCRYPT_DATA_TRANSFER') && $encrypted && 1 === (int) $encrypted) {
			$content = $this->decryptData($content);
		}
		if (empty($content)) {
			return $this;
		}
		$this->rawValues = \App\Utils::merge($this->contentParse($content), $this->rawValues);
		return $this;
	}

	/**
	 * Parsing the content of the request.
	 *
	 * @param string $content
	 *
	 * @return array
	 */
	private function contentParse(string $content): array
	{
		$type = $this->contentType;
		if (!empty($type)) {
			$type = explode('/', (explode(';', $type)[0]));
			$type = array_pop($type);
		}
		$return = [];
		switch ($type) {
			case 'json':
				$return = json_decode($content, 1);
				break;
			case 'form-data':
			case 'x-www-form-urlencoded':
				$return = \Notihnio\MultipartFormDataParser\MultipartFormDataParser::parse()->params;
				break;
		}
		return $this->content = $return;
	}

	/**
	 * Get key of the content request.
	 *
	 * @return array
	 */
	public function getContentKeys(): array
	{
		return array_map('\App\Purifier::purify', array_keys($this->content));
	}

	/**
	 * Decrypt content of the request.
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public function decryptData(string $data): string
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
