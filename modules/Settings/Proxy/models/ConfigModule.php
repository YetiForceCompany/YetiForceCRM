<?php
/**
 * Settings proxy config module model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */

class Settings_Proxy_ConfigModule_Model extends Settings_Vtiger_Module_Model
{
	public $listFields = [
		'proxyConnection' => 'LBL_PROXY_CONNECTION',
		'proxyProtocol' => 'LBL_PROXY_PROTOCOL',
		'proxyHost' => 'LBL_PROXY_HOST',
		'proxyPort' => 'LBL_PROXY_PORT',
		'proxyLogin' => 'LBL_PROXY_LOGIN',
		'proxyPassword' => 'LBL_PROXY_PASSWORD',
	];

	/**
	 * Function to initiation.
	 *
	 * @throws \ReflectionException
	 */
	public function init()
	{
		foreach ($this->listFields as $fieldName => $fieldData) {
			$value = \App\Config::security($fieldName);
			$this->set($fieldName, $value);
		}
	}

	/**
	 * Function to get the instance of Config module model.
	 *
	 * @param mixed $name
	 *
	 * @throws \ReflectionException
	 *
	 * @return \Settings_Proxy_ConfigModule_Model|\Settings_Vtiger_Module_Model
	 */
	public static function getInstance($name = 'Settings:Proxy')
	{
		$moduleModel = new self();
		$moduleModel->init();
		return $moduleModel;
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $name
	 *
	 * @return \Settings_Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$moduleName = $this->getName(true);
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'label' => $this->listFields[$name], 'displaytype' => 1, 'typeofdata' => 'I~M', 'presence' => 0, 'isEditableReadOnly' => false];
		switch ($name) {
			case 'proxyConnection':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				unset($params['validator']);
				break;
			case 'proxyProtocol':
				$params['uitype'] = 16;
				$params['picklistValues'] = ['http', 'https', 'tcp'];
				unset($params['validator']);
				break;
			case 'proxyHost':
				$params['uitype'] = 17;
				$params['typeofdata'] = 'V~O';
				break;
			case 'proxyPort':
				$params['uitype'] = 7;
				$params['typeofdata'] = 'I~O';
				break;
			case 'proxyLogin':
				$params['uitype'] = 106;
				$params['typeofdata'] = 'V~M';
				break;
			case 'proxyPassword':
				$params['uitype'] = 99;
				$params['typeofdata'] = 'P~M';
				break;
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
	}
}
