<?php
/**
 * Settings WAPRO ERP record model class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings WAPRO ERP record model class.
 */
class Settings_Wapro_Record_Model extends Settings_Vtiger_Record_Model
{
	use App\Controller\Traits\RecordSettings;

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl(): string
	{
		return 'index.php?parent=Settings&module=Wapro&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of record model.
	 *
	 * @param int $id
	 *
	 * @return $this|null Instance, if exists
	 */
	public static function getInstanceById(int $id): ?self
	{
		$row = App\Integrations\Wapro::getById($id);
		if (empty($row)) {
			return null;
		}
		$instance = new self();
		$instance->setData($row);
		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return $this
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
				'linklabel' => 'LBL_SYNCHRONIZER_LIST',
				'linkdata' => ['id' => $this->getId()],
				'linkicon' => 'fas fa-list',
				'linkclass' => 'btn btn-secondary btn-sm js-list-sync'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkdata' => ['url' => $this->getEditViewUrl()],
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm js-edit-record-modal'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Vtiger_List_Js.deleteById(' . $this->getId() . ')',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn text-white btn-danger btn-sm'
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
		$params = array_intersect_key($this->getData(), $this->getModule()->getFormFields());
		$tableName = $this->getModule()->baseTable;
		$params['password'] = \App\Encryption::getInstance()->encrypt($params['password']);
		if ($this->getId()) {
			$result = $db->createCommand()->update($tableName, $params, ['id' => $this->getId()])->execute();
		} else {
			$result = $db->createCommand()->insert($tableName, $params)->execute();
			$this->set('id', $db->getLastInsertID("{$tableName}_id_seq"));
		}
		\App\Cache::delete('App\Integrations\Wapro::getById', $this->getId());
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
			->delete($this->getModule()->baseTable, ['id' => $this->getId()])
			->execute();
		\App\Cache::delete('App\Integrations\Wapro::getById', $this->getId());
		return $return;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Wapro_Module_Model
	 */
	public function getModule(): Settings_Wapro_Module_Model
	{
		if (!isset($this->module)) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:Wapro');
		}
		return $this->module;
	}

	/**
	 * Gets field instance by name.
	 *
	 * @param string $name
	 *
	 * @throws ReflectionException
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name): Vtiger_Field_Model
	{
		$moduleName = $this->getModule()->getName(true);
		$fields = $this->getModule()->getFormFields();
		$params = [
			'label' => $fields[$name]['label'],
			'fieldvalue' => $this->get($name) ?? $fields[$name]['default'] ?? '',
			'typeofdata' => $fields[$name]['required'] ? 'V~M' : 'V~O',
			'maximumlength' => $fields[$name]['maximumlength'] ?? '',
		];
		switch ($name) {
			case 'status':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				break;
			default:
				break;
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/** {@inheritdoc} */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'status':
				$value = \App\Language::translate(1 == $value ? 'LBL_ACTIVE' : 'LBL_INACTIVE', $this->getModule()->getName(true));
				break;
			default:
				break;
		}
		return $value;
	}
}
