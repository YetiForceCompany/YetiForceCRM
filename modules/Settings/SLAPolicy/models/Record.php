<?php
/**
 * Settings SLAPolicy Record Model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SLAPolicy_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setId($id)
	{
		$this->set('id', $id);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
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
	 * @param Settings_SLAPolicy_Module_Model $moduleModel
	 *
	 * @return \self
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
		return $this;
	}

	/**
	 * Function to get the instance of sla policy record model.
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public static function getInstanceById(int $id, string $qualifiedModuleName = 'Settings:SLAPolicy')
	{
		$row = (new \App\Db\Query())->from('s_#__sla_policy')->where(['id' => $id])->one(\App\Db::getInstance('admin'));
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
	public static function getCleanInstance()
	{
		$instance = new static();
		$instance->setModule(Settings_Vtiger_Module_Model::getInstance('Settings:SLAPolicy'));
		return $instance;
	}

	/**
	 * Validate record data.
	 *
	 * @param array $data
	 *
	 * @return array cleaned up data
	 */
	public static function validate(array $data)
	{
		if (isset($data['id'])) {
			$data['id'] = \App\Purifier::purifyByType($data['id'], 'Integer');
		}
		$data['name'] = \App\Purifier::purifyByType($data['name'], 'Text');
		if (\App\TextParser::getTextLength($data['name']) > 255) {
			throw new \App\Exceptions\AppException('ERR_EXCEEDED_NUMBER_CHARACTERS||255', 406);
		}
		$data['tabid'] = \App\Purifier::purifyByType($data['tabid'], 'Integer');
		return $data;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$data = static::validate($this->getData());
		$db = \App\Db::getInstance('admin');
		$recordId = $this->getId();
		if ($recordId) {
			$db->createCommand()->update('s_#__sla_policy', $data, ['id' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__sla_policy', $data)->execute();
		}
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		\App\Db::getInstance('admin')->createCommand()
			->delete('s_#__sla_policy', ['id' => $this->getId()])
			->execute();
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
		if ($key === 'operational_hours') {
			$moduleName = $this->getModule()->getName();
			$value = $value === 0 ? \App\Language::translate('LBL_CALENDAR_HOURS', $moduleName) : \App\Language::translate('LBL_BUSINESS_HOURS', $moduleName);
		} elseif ($key === 'tabid') {
			$moduleName = \App\Module::getModuleName($value);
			$value = \App\Language::translate($moduleName, $moduleName);
		}
		return $value;
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return array - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getModule()->getEditRecordUrl($this->getId()),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn btn-primary btn-sm'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Vtiger_List_Js.deleteById(' . $this->getId() . ', true);',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn text-white btn-danger btn-sm'
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}
}
