<?php

/**
 * MailSmtp record model class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailSmtp_Record_Model extends Settings_Vtiger_Record_Model
{
	/** @var array Record changes */
	protected $changes = [];

	/**
	 * Function to get the Id.
	 *
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record.
	 *
	 * @param int $value - id value
	 */
	public function setId($value)
	{
		return $this->set('id', (int) $value);
	}

	/**
	 * Function to get the Name.
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
		return '?module=MailSmtp&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/** {@inheritdoc} */
	public function get($key)
	{
		return parent::get($key) ?? '';
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=MailSmtp&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Save Ajax.
	 *
	 * @return string URL
	 */
	public function getSaveAjaxActionUrl()
	{
		return '?module=MailSmtp&parent=Settings&action=SaveAjax&mode=save';
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-info',
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
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'default':
			case 'authentication':
			case 'individual_delivery':
			case 'save_send_mail':
			case 'smtp_validate_cert':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'password':
			case 'smtp_password':
				$value = str_repeat('*', \strlen($value));
				break;
			case 'status':
				if (isset(\App\Mailer::$statuses[$value])) {
					$value = \App\Mailer::$statuses[$value];
				}
				break;
			case 'unsubscribe':
				$unsubscribe = App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
				foreach ($unsubscribe as &$row) {
					$row = "<$row>";
				}
				$value = App\Purifier::encodeHtml(implode(',', $unsubscribe));
				break;
			default:
				$value = \App\Purifier::encodeHtml($value);
				break;
		}
		return $value;
	}

	/**
	 * Function to get the Display Value, for the checbox field type with given DB Insert Value.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getDisplayCheckboxValue($value)
	{
		if (0 === $value) {
			$value = \App\Language::translate('LBL_NO');
		} else {
			$value = \App\Language::translate('LBL_YES');
		}
		return $value;
	}

	/**
	 * Function to get the instance of advanced permission record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstanceById($id)
	{
		$row = (new \App\Db\Query())->from('s_#__mail_smtp')->where(['id' => $id])->one(\App\Db::getInstance('admin'));
		$instance = false;
		if ($row) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:MailSmtp');
		$instance = new self();
		$instance->module = $moduleInstance;

		return $instance;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_MailSmtp_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:MailSmtp');
		}
		return $this->module;
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getModule()->editFields as $fieldName) {
			if ($request->has($fieldName)) {
				if (!$this->isFieldActive($fieldName, $request)) {
					$value = $this->getModule()->dependency($fieldName)['default'];
				} else {
					$value = $this->getValueByField($fieldName, $request);
				}
				$this->set($fieldName, $value);
			}
		}
	}

	public function isFieldActive(string $fieldName, App\Request $request = null): bool
	{
		$active = true;
		if ($dependency = $this->getModule()->dependency($fieldName)) {
			foreach ($dependency['condition'] as $field => ['value' => $conValue, 'operator' => $operator]) {
				$conFieldVal = $this->getValueByField($field, $request);
				if (('e' === $operator && $conValue === $conFieldVal) || ('n' === $operator && $conValue !== $conFieldVal)) {
					$active = false;
					break;
				}
			}
		}

		return $active;
	}

	public function getValueByField(string $fieldName, ?App\Request $request = null)
	{
		if ($request && $request->has($fieldName)) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			$value = 'raw' !== $fieldModel->get('purifyType') ? $request->getRaw($fieldName) : $request->getByType($fieldName, $fieldModel->get('purifyType'));
			$fieldUITypeModel = $fieldModel->getUITypeModel();
			$fieldUITypeModel->validate($value, true);
			$value = $fieldModel->getDBValue($value);
		} else {
			$value = $this->get($fieldName);
		}

		return $value;
	}

	/**
	 * Function returns field instances for given name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name)
	{
		$fieldModel = $this->getModule()->getFieldInstanceByName($name);
		if ($this->has($name)) {
			$fieldModel->set('fieldvalue', $this->get($name) ?? '');
		}
		return $fieldModel;
	}

	public function validate()
	{
		$response = [];
		$isDuplicate = (new App\Db\Query())
			->from($this->getModule()->baseTable)
			->where(['name' => $this->get('name')])
			->andWhere(['not', [$this->getModule()->baseIndex => $this->getId()]])
			->exists();
		if ($isDuplicate) {
			$response[] = [
				'result' => false,
				'hoverField' => 'subject',
				'message' => App\Language::translate('LBL_DUPLICATE', $this->getModule()->getName(true))
			];
		}
		return $response;
	}

	/**
	 * Get pervious value by field.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getPreviousValue(string $fieldName = '')
	{
		return $fieldName ? ($this->changes[$fieldName] ?? null) : $this->changes;
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['id']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	/**
	 * Function removes record.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$db = App\Db::getInstance('admin');
		$result = false;
		if ($this->getId()) {
			$result = (bool) $db->createCommand()->delete($this->getModule()->baseTable, ['id' => $this->getId()])->execute();
		}

		return $result;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();
		try {
			$this->saveToDb();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
		\App\Cache::delete('MailServer', 'all');
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$db = \App\Db::getInstance('admin');
		$tablesData = $this->getId() ? array_intersect_key($this->getData(), $this->changes) : array_intersect_key($this->getData(), array_flip($this->getModule()->editFields));
		if ($tablesData) {
			$baseTable = $this->getModule()->baseTable;
			$baseTableIndex = $this->getModule()->baseIndex;
			foreach ($this->getValuesToSave($tablesData) as $tableName => $tableData) {
				if (!$this->getId() && $baseTable === $tableName) {
					$db->createCommand()->insert($tableName, $tableData)->execute();
					$this->setId((int) $db->getLastInsertID("{$baseTable}_id_seq"));
				} elseif ($baseTable === $tableName) {
					$db->createCommand()->update($tableName, $tableData, [$baseTableIndex => $this->getId()])->execute();
				}
			}
		}
	}

	/**
	 * Function formats data for saving.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function getValuesToSave(array $data): array
	{
		$tableName = $this->getModule()->baseTable;
		if (!$this->getId()) {
			$forSave[$tableName] = [];
		}
		foreach ($data as $fieldName => $value) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			$forSave[$tableName][$fieldModel->getColumnName()] = $value;
		}
		return $forSave;
	}
}
