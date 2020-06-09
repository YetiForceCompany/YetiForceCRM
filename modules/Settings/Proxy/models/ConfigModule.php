<?php

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
	 * Function to get CompanyDetails Menu item.
	 *
	 * @return menu item Model
	 */
	public function getMenuItem()
	{
		return Settings_Vtiger_MenuItem_Model::getInstance('LBL_CONFIG_PROXY');
	}

	/**
	 * Function to get Edit view Url.
	 *
	 * @return string Url
	 */
	public function getEditViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return 'index.php?module=Vtiger&parent=Settings&view=ConfigProxyEdit&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
	}

	/**
	 * Function to get Detail view Url.
	 *
	 * @return string Url
	 */
	public function getDetailViewUrl()
	{
		$menuItem = $this->getMenuItem();

		return 'index.php?module=Vtiger&parent=Settings&view=ConfigProxyDetail&block=' . $menuItem->get('blockid') . '&fieldid=' . $menuItem->get('fieldid');
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
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'label' => $this->listFields[$name], 'displaytype' => 1, 'typeofdata' => 'I~M', 'presence' => 0, 'isEditableReadOnly' => false, 'maximumlength' => ''];
		switch ($name) {
			case 'proxyConnection':
				$params['uitype'] = 56;
				// $params['typeofdata'] = 'C~M';
				unset($params['validator']);
				break;
			case 'proxyProtocol':
				$params['uitype'] = 16;
				$params['picklistValues'] = ['http', 'https', 'tcp'];
				unset($params['validator']);
				break;
			case 'proxyHost':
			case 'proxyPort':
			case 'proxyLogin':
			case 'proxyPassword':
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
	}

	/**
	 * Function to getDisplay value of every field.
	 *
	 * @param string $name field name
	 *
	 * @return mixed
	 */
	public function getDisplayValue($name)
	{
		switch ($name) {
			case 'upload_maxsize':
				$value = $this->get($name) . ' ' . \App\Language::translate('LBL_MB', $this->getName(true));
				break;
			default:
				$value = $this->getFieldInstanceByName($name)->getDisplayValue($this->get($name));
				break;
		}
		return $value;
	}
}
