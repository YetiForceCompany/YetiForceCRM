<?php
/**
 * Settings Magento model class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?parent=Settings&module=Magento&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?parent=Settings&module=Magento&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Save Ajax.
	 *
	 * @return string URL
	 */
	public function getSaveAjaxActionUrl()
	{
		return '?parent=Settings&module=Magento&action=SaveAjax&mode=save';
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'BTN_RECORD_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-info',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_RELOAD_MAGENTO',
				'linkurl' => "javascript:Settings_Magento_List_Js.reload('{$this->getId()}')",
				'linkicon' => 'mdi mdi-reload',
				'linkclass' => 'btn btn-sm btn-warning text-white',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger text-white',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to delete the current record model.
	 */
	public function delete()
	{
		\App\Db::getInstance('admin')->createCommand()
			->delete('i_#__magento_servers', ['id' => $this->getId()])
			->execute();
		\App\Cache::delete('Magento|getAllServers', '');
	}

	/**
	 * Function to get the instance of record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstanceById(int $id)
	{
		$instance = false;
		if ($row = \App\Integrations\Magento\Config::getServer($id)) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
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
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$params = [];
		foreach ($this->getData() as $key => $value) {
			$params[$key] = $value;
		}
		if ($this->getId()) {
			$db->createCommand()->update('i_#__magento_servers', $params, ['id' => $this->getId()])->execute();
		} else {
			$db->createCommand()->insert('i_#__magento_servers', $params)->execute();
			$this->set('id', $db->getLastInsertID('i_#__magento_servers_id_seq'));
		}
		\App\Cache::delete('Magento|getAllServers', '');
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Magento_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
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
		$moduleName = $this->getModule()->getName(true);
		$fields = $this->getModule()->getFormFields();
		$params = ['label' => 'LBL_' . \strtoupper($name), 'fieldvalue' => $this->get($name) ?? $fields[$name]['default'] ?? '', 'typeofdata' => 'V'];
		switch ($name) {
			case 'store_id':
				$params['uitype'] = 7;
				$params['typeofdata'] = 'I';
				$params['maximumlength'] = '16777215';
				break;
			case 'categories_limit':
			case 'products_limit':
			case 'customers_limit':
			case 'orders_limit':
			case 'invoices_limit':
				$params['uitype'] = 7;
				$params['typeofdata'] = 'I';
				$params['maximumlength'] = '65535';
				break;
			case 'storage_id':
				$params['uitype'] = 10;
				$params['isEditableReadOnly'] = false;
				$params['referenceList'] = ['IStorages'];
				break;
			case 'shipping_service_id':
			case 'payment_paypal_service_id':
			case 'payment_cash_service_id':
				$params['uitype'] = 10;
				$params['isEditableReadOnly'] = false;
				$params['referenceList'] = ['Services'];
				break;
			case 'connector':
				$params['uitype'] = 16;
				$params['picklistValues'] = ['Token' => 'Token'];
				break;
			case 'storage_quantity_location':
				$params['uitype'] = 16;
				$params['picklistValues'] = [
					'None' => \App\Language::translate('None'),
					'Products' => \App\Language::translate('SINGLE_Products', 'Products'),
					'IStorages' => \App\Language::translate('SINGLE_IStorages', 'IStorages'),
				];
				break;
			case 'url':
				$params['uitype'] = 17;
				$params['maximumlength'] = '250';
				break;
			case 'sync_currency':
			case 'sync_categories':
			case 'sync_products':
			case 'sync_customers':
			case 'sync_orders':
			case 'sync_invoices':
			case 'status':
				$params['uitype'] = 56;
				break;
		}
		$params['typeofdata'] .= $fields[$name]['required'] ? '~M' : '~O';
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/** {@inheritdoc} */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'status':
				$value = \App\Language::translate(1 == $value ? 'LBL_ACTIVE' : 'LBL_INACTIVE', 'Settings:Magento');
				break;
		}
		return $value;
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getModule()->getFormFields() as $fieldName => $fieldInfo) {
			switch ($fieldName) {
				case 'password':
					$value = \App\Encryption::getInstance()->encrypt($request->getRaw($fieldName));
					break;
				default:
					$value = $request->isEmpty($fieldName) ? '' : $request->getByType($fieldName, $fieldInfo['purifyType']);
					$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
					$fieldModel->validate($value, true);
					$value = $fieldModel->getDBValue($value);
					break;
			}
			if ('' === $value && $fieldInfo['required']) {
				throw new \App\Exceptions\IllegalValue('ERR_NO_VALUE||' . \App\Language::translate('LBL_' . \strtoupper($fieldName), $this->getModule()->getName(true)), 406);
			}
			$this->set($fieldName, $value);
		}
	}
}
