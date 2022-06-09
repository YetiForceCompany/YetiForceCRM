<?php
/**
 * BusinessHours record model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return int|null Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Id.
	 *
	 * @param int $id
	 *
	 * @return self
	 */
	protected function setId(int $id): self
	{
		$this->set('id', $id);
		return $this;
	}

	/**
	 * Function to get the Name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($oldValue = $this->get($key)) {
			$this->oldValues[$key] = $oldValue;
		}
		parent::set($key, $value);
		return $this;
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @param mixed $step
	 *
	 * @return string URL
	 */
	public function getEditViewUrl($step = false): string
	{
		return '?module=BusinessHours&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl(): string
	{
		return '?module=BusinessHours&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current profile.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl(): string
	{
		return '?module=BusinessHours&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of companies record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstanceById(int $id)
	{
		$row = (new \App\Db\Query())->from('s_#__business_hours')->where(['id' => $id])->one(\App\Db::getInstance('admin'));
		$instance = false;
		if ($row) {
			$instance = new self();
			$instance->setData($row);
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
		return new static();
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_BusinessHours_Module_Model
	 */
	public function getModule(): Settings_BusinessHours_Module_Model
	{
		if (!isset($this->module)) {
			$this->module = new Settings_BusinessHours_Module_Model();
		}
		return $this->module;
	}

	/**
	 * Validate record data.
	 *
	 * @param array $data
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function validate(array $data): array
	{
		if (isset($data['id'])) {
			$data['id'] = \App\Purifier::purifyByType($data['id'], 'Integer');
		}
		$data['name'] = \App\Purifier::purifyByType($data['name'], 'Text');
		if (\App\TextUtils::getTextLength($data['name']) > 254) {
			throw new \App\Exceptions\AppException('ERR_EXCEEDED_NUMBER_CHARACTERS||255', 406);
		}
		if (!\is_string($data['working_days'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['working_days'], 406);
		}
		$days = explode(',', trim($data['working_days'], ','));
		foreach ($days as $index => $day) {
			$days[$index] = \App\Purifier::purifyByType($day, 'Integer');
			if ((int) $day < 1 || (int) $day > 7) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $day, 406);
			}
		}
		$data['working_days'] = implode(',', $days);
		$data['working_hours_from'] = \App\Purifier::purifyByType($data['working_hours_from'], 'time');
		$data['working_hours_to'] = \App\Purifier::purifyByType($data['working_hours_to'], 'time');
		if (isset($data['holidays']) && 0 !== $data['holidays'] && 1 !== $data['holidays']) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['holidays'], 406);
		}
		if (isset($data['default']) && 0 !== $data['default'] && 1 !== $data['default']) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['default'], 406);
		}
		$data['reaction_time'] = \App\Purifier::purifyByType($data['reaction_time'], 'timePeriod');
		$data['idle_time'] = \App\Purifier::purifyByType($data['idle_time'], 'timePeriod');
		$data['resolve_time'] = \App\Purifier::purifyByType($data['resolve_time'], 'timePeriod');
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
		if (!isset($data['holidays'])) {
			$data['holidays'] = 0;
		}
		if (!isset($data['default'])) {
			$data['default'] = 0;
		}
		if ($recordId) {
			$db->createCommand()->update('s_#__business_hours', $data, ['id' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__business_hours', $data)->execute();
		}
		\App\Cache::clear();
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key): string
	{
		$value = $this->get($key);
		if ('working_days' === $key) {
			$days = explode(',', trim($value, ','));
			$value = [];
			foreach ($days as $day) {
				$value[] = \App\Language::translate(array_flip(\App\Fields\Date::$dayOfWeek)[$day], 'Calendar');
			}
			$value = implode(', ', $value);
		} elseif ('working_hours_from' === $key || 'working_hours_to' === $key) {
			$value = \App\Fields\Time::formatToDisplay($value, false);
		} elseif ('default' === $key) {
			$value = $value ? \App\Language::translate('LBL_YES') : \App\Language::translate('LBL_NO');
		} elseif ('reaction_time' === $key || 'idle_time' === $key || 'resolve_time' === $key) {
			$value = \App\Fields\TimePeriod::getLabel($value);
		} elseif ('holidays' === $key) {
			$value = $value ? \App\Language::translate('LBL_HOLIDAYS', 'Settings:BusinessHours') : '';
		}
		return $value;
	}

	/**
	 * Function to delete the current Record Model.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$delete = \App\Db::getInstance('admin')->createCommand()
			->delete('s_#__business_hours', ['id' => $this->getId()])
			->execute();
		if ($delete) {
			\App\Cache::clear();
		}
		return $delete;
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
				'linkclass' => 'btn btn-primary btn-sm',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_BusinessHours_List_Js.deleteById(' . $this->getId() . ');',
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
