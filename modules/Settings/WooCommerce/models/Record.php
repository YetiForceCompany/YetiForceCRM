<?php
/**
 * Record file for WooCommerce integration model.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Record class for WooCommerce integration model.
 */
class Settings_WooCommerce_Record_Model extends Settings_Vtiger_Record_Model
{
	use App\Controller\Traits\RecordSettings;

	/** @var \Settings_Vtiger_Module_Model Setting module model */
	protected $module;

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?parent=Settings&module=WooCommerce&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of record model.
	 *
	 * @param int $id
	 *
	 * @return self|bool instance, if exists
	 */
	public static function getInstanceById(int $id)
	{
		$instance = false;
		if ($row = \App\Integrations\WooCommerce\Config::getServer($id)) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return self
	 */
	public static function getCleanInstance(): self
	{
		$instance = new self();
		$instance->getModule();
		return $instance;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'BTN_RECORD_EDIT',
				'linkdata' => ['url' => $this->getEditViewUrl()],
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm js-edit-record-modal'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'BTN_RECORD_INFO',
				'linkicon' => 'fas fa-chart-bar',
				'linkclass' => 'btn btn-secondary btn-sm js-list-sync',
				'linkdata' => [
					'url' => 'index.php?parent=Settings&module=WooCommerce&view=EditConfigModal&record=' . $this->getId()
				],
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_RELOAD_WOOCOMMERCE',
				'linkurl' => "javascript:Settings_WooCommerce_List_Js.reload('{$this->getId()}')",
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
		$links = [];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to save.
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		$db = App\Db::getInstance('admin');
		$params = [];
		foreach ($this->getData() as $key => $value) {
			$params[$key] = $value;
		}
		if ($this->getId()) {
			$result = $db->createCommand()->update(\App\Integrations\WooCommerce::TABLE_NAME, $params, ['id' => $this->getId()])
				->execute();
		} else {
			$result = $db->createCommand()->insert(\App\Integrations\WooCommerce::TABLE_NAME, $params)
				->execute();
			$this->set('id', $db->getLastInsertID('i_#__woocommerce_servers_id_seq'));
		}
		\App\Cache::delete('WooCommerce|getAllServers', '');
		return (bool) $result;
	}

	/**
	 * Function to delete the current record model.
	 *
	 * @return int
	 */
	public function delete(): int
	{
		$return = \App\Db::getInstance('admin')->createCommand()
			->delete(\App\Integrations\WooCommerce::TABLE_NAME, ['id' => $this->getId()])
			->execute();
		\App\Cache::delete('WooCommerce|getAllServers', '');
		return $return;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_WooCommerce_Module_Model
	 */
	public function getModule(): Settings_WooCommerce_Module_Model
	{
		if (!isset($this->module)) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:WooCommerce');
		}
		return $this->module;
	}

	/**
	 * Get field instance by name.
	 *
	 * @param string $name
	 *
	 * @return Settings_Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name): Settings_Vtiger_Field_Model
	{
		$moduleName = $this->getModule()->getName(true);
		$fields = $this->getModule()->getFormFields();
		$params = [
			'label' => 'LBL_' . \strtoupper($name),
			'fieldvalue' => $this->get($name) ?? $fields[$name]['default'] ?? '',
			'typeofdata' => 'V'
		];
		switch ($name) {
			case 'products_limit':
			case 'orders_limit':
				$params['uitype'] = 7;
				$params['typeofdata'] = 'I';
				$params['maximumlength'] = '65535';
				break;
			case 'shipping_service_id':
				$params['uitype'] = 10;
				$params['isEditableReadOnly'] = false;
				$params['referenceList'] = ['Services'];
				break;
			case 'verify_ssl':
				$params['uitype'] = 16;
				$params['picklistValues'] = [0 => \App\Language::translate('LBL_NO'), 1 => \App\Language::translate('LBL_YES')];
				break;
			case 'connector':
				$params['uitype'] = 16;
				$params['picklistValues'] = ['HttpAuth' => 'HTTP authentication'];
				break;
			case 'master':
				$params['uitype'] = 16;
				$params['picklistValues'] = [
					0 => \App\Language::translate('LBL_SYSTEM_WOOCOMMERCE', 'Settings:WooCommerce'),
					1 => \App\Language::translate('LBL_SYSTEM_YETIFORCE', 'Settings:WooCommerce')
				];
				break;
			case 'direction_categories':
			case 'direction_tags':
			case 'direction_products':
			case 'direction_orders':
				$params['uitype'] = 16;
				$params['picklistValues'] = [
					0 => \App\Language::translate('LBL_DIR_API_TO_CRM', 'Settings:WooCommerce'),
					1 => \App\Language::translate('LBL_DIR_CRM_TO_API', 'Settings:WooCommerce'),
					2 => \App\Language::translate('LBL_DIR_CRM_API', 'Settings:WooCommerce'),
				];
				break;
			case 'url':
				$params['uitype'] = 17;
				$params['maximumlength'] = '250';
				break;
			case 'sync_currency':
			case 'sync_categories':
			case 'sync_tags':
			case 'sync_products':
			case 'sync_customers':
			case 'sync_orders':
			case 'status':
				$params['uitype'] = 56;
				break;
			case 'assigned_user_id':
				$params['uitype'] = 53;
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
				$value = \App\Language::translate(1 == $value ? 'LBL_ACTIVE' : 'LBL_INACTIVE', 'Settings:WooCommerce');
				break;
			default:
				$value = \App\Purifier::encodeHtml($value);
				break;
		}
		return $value;
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request): void
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
