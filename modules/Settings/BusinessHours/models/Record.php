<?php

/**
 * BusinessHours record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_BusinessHours_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('businesshoursid');
	}

	/**
	 * Function to get the Id.
	 *
	 * @param mixed $id
	 *
	 * @return int Profile Id
	 */
	protected function setId($id)
	{
		$this->set('businesshoursid', $id);

		return $this;
	}

	/**
	 * Function to get the Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('businesshoursname');
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @param mixed $step
	 *
	 * @return string URL
	 */
	public function getEditViewUrl($step = false)
	{
		return '?module=BusinessHours&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=BusinessHours&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current profile.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl()
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
	public static function getInstanceById($id)
	{
		$db = \App\Db::getInstance('admin');
		$row = (new \App\Db\Query())->from('s_#__businesshours')->where(['businesshoursid' => $id])->one($db);
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
	public static function getCleanInstance()
	{
		return new static();
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_BusinessHours_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = new Settings_BusinessHours_Module_Model();
		}
		return $this->module;
	}

	/**
	 * Validate record data.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function validate()
	{
		$data = $this->getData();
		if (isset($data['businesshoursid']) && !is_numeric($data['businesshoursid'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE', 406);
		}
		if (!is_string($data['businesshoursname'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['businesshoursname'], 406);
		}
		if (preg_match('/[\<\>\"\#\,]/', $data['businesshoursname'])) {
			throw new \App\Exceptions\AppException(\App\Language::translateArgs('ERR_SPECIAL_CHARACTERS_NOT_ALLOWED', 'Other.Exceptions', '<>"#,'), 512);
		}
		if (strlen($data['businesshoursname']) > 512) {
			throw new \App\Exceptions\AppException(\App\Language::translate('ERR_EXCEEDED_NUMBER_CHARACTERS', 'Other.Exceptions'), 512);
		}
		if (!is_string($data['working_days'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['working_days'], 406);
		}
		$days = explode(',', trim($data['working_days'], ','));
		foreach ($days as $day) {
			if (!is_numeric($day) || (int) $day < 1 || (int) $day > 7) {
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $day, 406);
			}
		}
		if (!is_string($data['working_hours_from'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['working_hours_from'], 406);
		}
		if (!is_string($data['working_hours_to'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['working_hours_to'], 406);
		}
		if (!preg_match('/[0-9]{2}\:[0-9]{2}\:[0-9]{2}/', $data['working_hours_from'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['working_hours_from'], 406);
		}
		if (!preg_match('/[0-9]{2}\:[0-9]{2}\:[0-9]{2}/', $data['working_hours_to'])) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['working_hours_to'], 406);
		}
		if (isset($data['holidays']) && $data['holidays'] !== 0 && $data['holidays'] !== 1) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['holidays'], 406);
		}
		if (isset($data['default']) && $data['default'] !== 0 && $data['default'] !== 1) {
			throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_VALUE||' . $data['default'], 406);
		}
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$this->validate();
		$db = \App\Db::getInstance('admin');
		$recordId = $this->getId();
		$data = $this->getData();
		if (!isset($data['holidays'])) {
			$data['holidays'] = 0;
		}
		if (!isset($data['default'])) {
			$data['default'] = 0;
		}
		if (!empty($data['default'])) {
			$days = explode(',', trim($data['working_days'], ','));
			$db->createCommand()->update('s_#__businesshours', ['default' => 0], [
				'and',
				['OR LIKE', 'working_days', $days],
				['holidays' => $data['holidays']]
			])->execute();
		}
		if ($recordId) {
			$db->createCommand()->update('s_#__businesshours', $data, ['businesshoursid' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__businesshours', $data)->execute();
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
	public function getDisplayValue($key)
	{
		$value = $this->get($key);
		if ($key === 'working_days') {
			$daysPicklistValues = \App\Fields\Picklist::getValues('dayoftheweek');
			$daysArray = array_column($daysPicklistValues, 'picklistValue', 'dayoftheweekid');
			$days = explode(',', trim($value, ','));
			$value = [];
			foreach ($days as $day) {
				$value[] = \App\Language::translate($daysArray[$day], 'Calendar');
			}
			$value = implode(', ', $value);
		} elseif ($key === 'working_hours_from' || $key === 'working_hours_to') {
			$value = \App\Fields\Time::formatToDisplay($value);
		} elseif ($key === 'default' || $key === 'holidays') {
			$value = $value ? \App\Language::translate('LBL_YES', '_Base') : \App\Language::translate('LBL_NO', '_Base');
		}
		return $value;
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()
			->delete('s_#__businesshours', ['businesshoursid' => $this->getId()])
			->execute();
		\App\Cache::clear();
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
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn btn-primary btn-sm'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_BusinessHours_List_Js.deleteById(' . $this->getId() . ');',
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
