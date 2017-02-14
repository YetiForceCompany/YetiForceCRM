<?php
namespace Api\Core;

/**
 * Web service request class 
 * @package YetiForce.Webservice
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Request extends \Vtiger_Request
{

	public static function init()
	{
		return new self($_REQUEST);
	}

	public function getData()
	{
		if ($this->getRequestMetod() === 'GET') {
			$this->rawValueMap = $_REQUEST;
			return $this;
		} else {
			$encrypted = $this->getHeader('Encrypted');
			$content = file_get_contents('php://input');
			if (\AppConfig::api('ENCRYPT_DATA_TRANSFER') && $encrypted && (int) $encrypted === 1) {
				$content = $this->decryptData($content);
			}
		}
		if (empty($content)) {
			return false;
		}
		$this->rawValueMap = array_merge($this->contentParse($content), $_REQUEST);
		return $this;
	}

	public function contentParse($content)
	{
		$type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : $this->getHeader('Content-Type');
		if (empty($type)) {
			$type = $this->getHeader('Accept');
		}
		if (!empty($type)) {
			$type = array_pop(explode('/', $type));
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
		$privateKey = 'file://' . ROOT_DIRECTORY . DIRECTORY_SEPARATOR . vglobal('privateKey');
		if (!$privateKey = openssl_pkey_get_private($privateKey)) {
			throw new \Exception\AppException('Private Key failed');
		}
		$privateKey = openssl_pkey_get_private($privateKey);
		openssl_private_decrypt($data, $decrypted, $privateKey);
		return $decrypted;
	}
}
