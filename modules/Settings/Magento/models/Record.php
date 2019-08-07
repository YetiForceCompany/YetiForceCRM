<?php
/**
 * Settings Magento model class.
 *
 * @package   Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Settings magento class.
 */
class Settings_Magento_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Record ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Record name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @param string $type
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:Magento');
		$instance = new self();
		$instance->module = $moduleInstance;
		return $instance;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Magento_Module_Model
	 */
	public function getModule()
	{
		if (!$this->module) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:Magento');
		}
		return $this->module;
	}

	/**
	 * Get field instance by name.
	 *
	 * @param $name
	 *
	 * @throws ReflectionException
	 *
	 * @return Settings_Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$config = \App\Config::component('Magento');
		$moduleName = $this->getModule()->getName(true);
		$fields = $this->getModule()->getFormFields();
		$params = ['uitype' => 1, 'column' => $name, 'name' => $name, 'label' => 'LBL_' . \strtoupper($name), 'displaytype' => 1, 'typeofdata' => 'V', 'presence' => 0, 'isEditableReadOnly' => false, 'isReadOnly' => false, 'fieldvalue' => $config[$name] ?? $fields[$name]['default'] ?? ''];
		switch ($name) {
			case 'storeId':
			case 'websiteId':
			case 'customerLimit':
			case 'productLimit':
			case 'orderLimit':
			case 'invoiceLimit':
				$params['uitype'] = 7;
				$params['defaultvalue'] = 7;
				$params['typeofdata'] = 'I';
				$params['maximumlength'] = '16777215';
				break;
			case 'currencyId':
				$params['uitype'] = 117;
				$params['maximumlength'] = '50';
				foreach ((new \Vtiger_Field_Model())->getCurrencyList() as $id => $currency) {
					$params['picklistValues'][$id] = \App\Language::translate($currency, $moduleName);
				}
				break;
			case 'storageId':
				$params['uitype'] = 10;
				$params['isEditableReadOnly'] = false;
				$params['referenceList'] = ['IStorages'];
				break;
			case 'shippingServiceId':
				$params['uitype'] = 10;
				$params['isEditableReadOnly'] = false;
				$params['referenceList'] = ['Services'];
				break;
			case 'masterSource':
				$params['uitype'] = 16;
				$params['picklistValues'] = ['yetiforce' => 'YetiForce', 'magento' => 'Magento'];
				break;
			case 'addressApi':
				$params['uitype'] = 17;
				break;
		}
		$params['typeofdata'] .= $fields[$name]['required'] ? '~M' : '~O';
		return \Settings_Vtiger_Field_Model::init($moduleName, $params);
	}
}
