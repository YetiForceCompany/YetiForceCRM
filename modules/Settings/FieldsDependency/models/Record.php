<?php
/**
 * Settings fields dependency record model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings fields dependency record model class.
 */
class Settings_FieldsDependency_Record_Model extends Settings_Vtiger_Record_Model
{
	/** {@inheritdoc} */
	public function getId()
	{
		return $this->get('id');
	}

	/** {@inheritdoc} */
	protected function setId($id)
	{
		$this->set('id', $id);
		return $this;
	}

	/** {@inheritdoc} */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get module instance of this record.
	 *
	 * @return \Settings_Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set module to this record instance.
	 *
	 * @param Settings_FieldsDependency_Module_Model $moduleModel
	 *
	 * @return \self
	 */
	public function setModule($moduleModel): self
	{
		$this->module = $moduleModel;
		return $this;
	}

	/**
	 * Function to get the instance of sla policy record model.
	 *
	 * @param int    $id
	 * @param string $qualifiedModuleName
	 *
	 * @return mixed
	 */
	public static function getInstanceById(int $id, string $qualifiedModuleName = 'Settings:FieldsDependency')
	{
		$row = (new \App\Db\Query())->from('s_#__fields_dependency')->where(['id' => $id])->one(\App\Db::getInstance('admin'));
		$instance = null;
		if ($row) {
			$instance = new self();
			$instance->setData($row)->setModule(Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName));
		}
		return $instance;
	}

	/**
	 * function to get clean instance.
	 *
	 * @return \static
	 */
	public static function getCleanInstance(): self
	{
		$instance = new static();
		$instance->setModule(Settings_Vtiger_Module_Model::getInstance('Settings:FieldsDependency'));
		return $instance;
	}

	/**
	 * Validate and sanitize record data.
	 *
	 * @param array $data
	 *
	 * @return array cleaned up data
	 */
	public static function sanitize(array $data): array
	{
		if (\App\TextParser::getTextLength($data['name']) > 100) {
			throw new \App\Exceptions\AppException('ERR_EXCEEDED_NUMBER_CHARACTERS||100', 406);
		}
		if (isset($data['id'])) {
			$data['id'] = \App\Purifier::purifyByType($data['id'], 'Integer');
		}
		$data['name'] = \App\Purifier::purifyByType($data['name'], 'Text');
		$data['status'] = $data['status'] ? 0 : 1;
		$data['mandatory'] = (int) $data['mandatory'];
		$data['gui'] = (int) $data['gui'];
		$data['tabid'] = (int) $data['tabid'];
		$data['conditionsFields'] = \App\Json::encode(\App\Condition::getFieldsFromConditions($data['conditions'])['baseModule'] ?? []);
		$data['conditions'] = \App\Json::encode($data['conditions']);
		$data['views'] = \App\Json::encode($data['views']);
		$data['fields'] = \App\Json::encode($data['fields']);
		return $data;
	}

	/**
	 * Function to save.
	 *
	 * @return void
	 */
	public function save(): void
	{
		$data = static::sanitize($this->getData());
		$db = \App\Db::getInstance('admin');
		$recordId = $this->getId();
		if ($recordId) {
			$db->createCommand()->update('s_#__fields_dependency', $data, ['id' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__fields_dependency', $data)->execute();
		}
		\App\Cache::delete('FieldsDependency', $data['tabid']);
		$this->checkHandler();
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		$return = \App\Db::getInstance('admin')->createCommand()
			->delete('s_#__fields_dependency', ['id' => $this->getId()])
			->execute();
		\App\Cache::delete('FieldsDependency', $this->get('tabid'));
		$this->checkHandler();
		return $return;
	}

	/**
	 * Get display value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'tabid':
				$moduleName = \App\Module::getModuleName($value);
				$value = \App\Language::translate($moduleName, $moduleName);
				break;
			case 'views':
				$value = implode(', ', array_map(function ($val) {
					return \App\Language::translate(\App\FieldsDependency::VIEWS[$val], 'Settings:FieldsDependency');
				}, \App\Json::decode($value) ?? []));
				break;
			case 'fields':
				$moduleModel = Vtiger_Module_Model::getInstance($this->get('tabid'));
				$value = implode(', ', array_map(function ($fieldName) use ($moduleModel) {
					return $moduleModel->getField($fieldName)->getFullLabelTranslation();
				}, \App\Json::decode($value) ?? []));
				break;
			case 'mandatory':
			case 'gui':
				$value = \App\Language::translate($value ? 'LBL_YES' : 'LBL_NO');
				break;
			case 'status':
				$value = \App\Language::translate($value ? 'LBL_NO' : 'LBL_YES');
				break;
		}
		return $value;
	}

	/**
	 * Get edit record url.
	 *
	 * @param int $recordId
	 *
	 * @return string
	 */
	public function getEditRecordUrl(int $recordId): string
	{
		return 'index.php?parent=Settings&module=FieldsDependency&view=Edit&record=' . $recordId;
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditRecordUrl($this->getId()),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Vtiger_List_Js.deleteById(' . $this->getId() . ', true)',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn text-white btn-danger btn-sm'
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Check whether to activate/remove handler.
	 *
	 * @return void
	 */
	public function checkHandler()
	{
		if ((new \App\Db\Query())->from('s_#__fields_dependency')->where(['status' => 0])->exists(\App\Db::getInstance('admin'))) {
			if (!App\EventHandler::registerHandler('EditViewChangeValue', 'Vtiger_FieldsDependency_Handler') || !App\EventHandler::registerHandler('EditViewPreSave', 'Vtiger_FieldsDependency_Handler')) {
				App\EventHandler::setActive('Vtiger_FieldsDependency_Handler');
			}
		} else {
			App\EventHandler::setInActive('Vtiger_FieldsDependency_Handler');
		}
	}
}
