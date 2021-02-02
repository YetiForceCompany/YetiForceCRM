<?php
/**
 * Settings proxy config module model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings proxy config module model class.
 */
class Settings_Proxy_Module_Model extends Settings_Vtiger_Module_Model
{
	/** @var array Load proxy module list fields. */
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
	public function init(): void
	{
		foreach ($this->listFields as $fieldName => $fieldData) {
			$value = \App\Config::security($fieldName);
			$this->set($fieldName, $value);
		}
	}

	/**
	 * Function to get the instance of Config module model.
	 *
	 * @param string $name
	 *
	 * @throws \ReflectionException
	 *
	 * @return self
	 */
	public static function getInstance($name = 'Settings:Proxy')
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		$moduleModel = new $modelClassName();
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
	public function getFieldInstanceByName(string $name)
	{
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'label' => $this->listFields[$name], 'displaytype' => 1, 'typeofdata' => 'I~M', 'presence' => 0, 'isEditableReadOnly' => false];
		switch ($name) {
			case 'proxyConnection':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'V~O';
				unset($params['validator']);
				break;
			case 'proxyProtocol':
				$params['uitype'] = 16;
				$params['picklistValues'] = ['http' => 'http', 'https' => 'https', 'tcp' => 'tcp'];
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
		return Settings_Vtiger_Field_Model::init($this->getName(true), $params);
	}
}
