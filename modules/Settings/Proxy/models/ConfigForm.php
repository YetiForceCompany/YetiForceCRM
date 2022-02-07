<?php
/**
 * Settings proxy config form file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings proxy config form class.
 */
class Settings_Proxy_ConfigForm_Model
{
	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public static function getFields(string $moduleName): array
	{
		$config = App\Config::security();
		$fields = [
			'proxyConnection' => [
				'purifyType' => 'boolean',
				'uitype' => 56,
				'label' => 'LBL_PROXY_CONNECTION',
				'labelDesc' => 'LBL_QUICK_CREATE_MODULES_DESC',
				'fieldvalue' => $config['proxyConnection'] ?? ''
			],
			'proxyProtocol' => [
				'purifyType' => 'text',
				'uitype' => 16,
				'label' => 'LBL_PROXY_PROTOCOL',
				'labelDesc' => 'LBL_PROXY_PROTOCOL_DESC',
				'picklistValues' => ['http' => 'http', 'https' => 'https', 'tcp' => 'tcp'],
				'fieldvalue' => $config['proxyProtocol'] ?? ''
			],
			'proxyHost' => [
				'purifyType' => 'url',
				'uitype' => 17,
				'label' => 'LBL_PROXY_HOST',
				'labelDesc' => 'LBL_PROXY_HOST_DESC',
				'fieldvalue' => $config['proxyHost'] ?? ''
			],
			'proxyPort' => [
				'purifyType' => 'integer',
				'uitype' => 7,
				'label' => 'LBL_PROXY_PORT',
				'labelDesc' => 'LBL_PROXY_PORT_DESC',
				'fieldvalue' => $config['proxyPort'] ?? ''
			],
			'proxyLogin' => [
				'purifyType' => 'text',
				'uitype' => 106,
				'label' => 'LBL_PROXY_LOGIN',
				'labelDesc' => 'LBL_PROXY_LOGIN_DESC',
				'fieldvalue' => $config['proxyLogin'] ?? ''
			],
			'proxyPassword' => [
				'purifyType' => 'text',
				'uitype' => 99,
				'label' => 'LBL_PROXY_PASSWORD',
				'labelDesc' => 'LBL_PROXY_PASSWORD_DESC',
				'fieldvalue' => $config['proxyPassword'] ?? '',
				'mandatory' => true
			],
		];
		foreach ($fields as $key => $value) {
			$fields[$key] = \Vtiger_Field_Model::init($moduleName, $value, $key);
		}
		return $fields;
	}
}
