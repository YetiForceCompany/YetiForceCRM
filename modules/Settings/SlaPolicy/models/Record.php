<?php
/**
 * Settings SlaPolicy Record Model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SlaPolicy_Record_Model extends Settings_Vtiger_Record_Model
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
	 * @param Settings_SlaPolicy_Module_Model $moduleModel
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
	public static function getInstanceById(int $id, string $qualifiedModuleName = 'Settings:SlaPolicy')
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
	public static function getCleanInstance(): self
	{
		$instance = new static();
		$instance->setModule(Settings_Vtiger_Module_Model::getInstance('Settings:SlaPolicy'));
		return $instance;
	}

	/**
	 * Validate record data.
	 *
	 * @param array $data
	 *
	 * @return array cleaned up data
	 */
	public static function validate(array $data): array
	{
		if (isset($data['id'])) {
			$data['id'] = \App\Purifier::purifyByType($data['id'], 'Integer');
		}
		$data['name'] = \App\Purifier::purifyByType($data['name'], 'Text');
		if (\App\TextUtils::getTextLength($data['name']) > 255) {
			throw new \App\Exceptions\AppException('ERR_EXCEEDED_NUMBER_CHARACTERS||255', 406);
		}
		$data['tabid'] = \App\Purifier::purifyByType($data['tabid'], 'Integer');
		$data['reaction_time'] = \App\Purifier::purifyByType($data['reaction_time'], 'timePeriod');
		$data['idle_time'] = \App\Purifier::purifyByType($data['idle_time'], 'timePeriod');
		$data['resolve_time'] = \App\Purifier::purifyByType($data['resolve_time'], 'timePeriod');
		$data['available_for_record_time_count'] = \App\Purifier::purifyByType($data['available_for_record_time_count'], 'Bool');
		if ($data['business_hours']) {
			$data['business_hours'] = explode(',', $data['business_hours']);
			foreach ($data['business_hours'] as $index => $businessHoursId) {
				$data['business_hours'][$index] = \App\Purifier::purifyByType($businessHoursId, 'Integer');
			}
			$data['business_hours'] = implode(',', $data['business_hours']);
		}
		return $data;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$data = static::validate($this->getData());
		$db = \App\Db::getInstance('admin');
		if ($recordId = $this->getId()) {
			$db->createCommand()->update('s_#__sla_policy', $data, ['id' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__sla_policy', $data)->execute();
		}
		\App\Cache::clear();
	}

	/**
	 * Function to delete the current Record Model.
	 *
	 * @return int
	 */
	public function delete()
	{
		$result = \App\Db::getInstance('admin')->createCommand()
			->delete('s_#__sla_policy', ['id' => $this->getId()])
			->execute();
		\App\Cache::clear();
		return $result;
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
		if ('operational_hours' === $key) {
			$value = 0 === $value ? \App\Language::translate('LBL_CALENDAR_HOURS', 'ServiceContracts') : \App\Language::translate('LBL_BUSINESS_HOURS', 'ServiceContracts');
		} elseif ('tabid' === $key) {
			$moduleName = \App\Module::getModuleName($value);
			$value = \App\Language::translate($moduleName, $moduleName);
		} elseif ('business_hours' === $key) {
			$value = implode(', ', array_column(\App\Utils\ServiceContracts::getBusinessHoursByIds(explode(',', $value)), 'name'));
		} elseif (\in_array($key, ['reaction_time', 'idle_time', 'resolve_time'])) {
			$value = \App\Fields\TimePeriod::getLabel($value);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getModule()->getEditRecordUrl($this->getId()),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Vtiger_List_Js.deleteById(' . $this->getId() . ', true);',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn text-white btn-danger btn-sm',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}
}
