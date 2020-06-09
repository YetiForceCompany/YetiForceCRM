<?php
/**
 * Settings Meeting Services model class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings_MeetingServices_Record_Model class.
 */
class Settings_MeetingServices_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Length key.
	 */
	const KEY_LENGTH = 32;

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
		return $this->get('url');
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?parent=Settings&module=MeetingServices&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return array - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'BTN_COPY_API_KEY',
				'linkicon' => 'fas fa-copy',
				'linkclass' => 'btn btn-sm btn-primary js-clipboard',
				'linkdata' => ['copy-attribute' => 'clipboard-text', 'clipboard-text' => \App\Purifier::encodeHtml($this->get('key'))]
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'BTN_RECORD_EDIT',
				'linkdata' => ['url' => $this->getEditViewUrl()],
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-primary js-edit-record'
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
		return \App\Db::getInstance()->createCommand()
			->delete($this->getModule()->baseTable, ['id' => $this->getId()])
			->execute();
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

		if ($row = \App\MeetingService::getService($id)) {
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
		$instance = new self();
		$instance->getModule();
		return $instance;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$params = array_intersect_key($this->getData(), $this->getModule()->getFormFields());
		$tableName = $this->getModule()->baseTable;
		if ($this->getId()) {
			$result = $db->createCommand()->update($tableName, $params, ['id' => $this->getId()])->execute();
		} else {
			$params['key'] = \App\Encryption::generatePassword(self::KEY_LENGTH);
			$result = $db->createCommand()->insert($tableName, $params)->execute();
			$this->set('id', $db->getLastInsertID("{$tableName}_id_seq"));
		}
		if ($result && !empty($params['status'])) {
			$db->createCommand()->update($tableName, ['status' => 0], ['<>', 'id', $this->getId()])->execute();
		}
		\App\Cache::delete('MeetingService::getServices', '');
		return (bool) $result;
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getModule()->getFormFields() as $fieldName => $fieldInfo) {
			if ($request->has($fieldName)) {
				switch ($fieldName) {
					case 'secret':
						$value = \App\Encryption::getInstance()->encrypt($request->getRaw($fieldName));
						break;
					default:
						$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
						$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
						$fieldModel->validate($value, true);
						$value = $fieldModel->getDBValue($value);
						break;
				}
				$this->set($fieldName, $value);
			}
		}
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:MeetingServices');
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
	public function getFieldInstanceByName($name)
	{
		$moduleName = $this->getModule()->getName(true);
		$fields = $this->getModule()->getFormFields();
		$params = [
			'label' => $fields[$name]['label'],
			'fieldvalue' => $this->get($name) ?? $fields[$name]['default'] ?? '',
			'typeofdata' => $fields[$name]['required'] ? 'V~M' : 'V~O',
			'maximumlength' => $fields[$name]['maximumlength'] ?? ''
		];
		switch ($name) {
			case 'url':
				$params['uitype'] = 17;
				break;
			case 'status':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				break;
			case 'secret':
				$params['uitype'] = 99;
				break;
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'status':
				$value = \App\Language::translate(1 == $value ? 'LBL_ACTIVE' : 'LBL_INACTIVE', $this->getModule()->getName(true));
				break;
		}
		return $value;
	}
}
