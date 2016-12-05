<?php

/**
 * Automatic Assignment Record Model Class
 * @package YetiForce.Settings.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Raw data
	 * @var type 
	 */
	private $rawData = [];

	/**
	 * Variable determines the possibility of creating value duplicates
	 * @var type 
	 */
	public $checkDuplicate = false;

	/**
	 * Function to get the Id
	 * @return int Role Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Role Name
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rolename');
	}

	/**
	 * Function to get Module instance
	 * @return Settings_AutomaticAssignment_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Set module Instance
	 * @param Settings_AutomaticAssignment_Module_Model $moduleModel
	 * @return Settings_AutomaticAssignment_Module_Model
	 */
	public function setModule($moduleModel)
	{
		return $this->module = $moduleModel;
	}

	/**
	 * Function to get table name
	 * @return string
	 */
	public function getTable()
	{
		return $this->module->baseTable;
	}

	/**
	 * Function to get table primary key
	 * @return string
	 */
	public function getTableIndex()
	{
		return $this->module->baseIndex;
	}

	/**
	 * Function to get raw data
	 * @return array
	 */
	public function getRawData()
	{
		return $this->rawData;
	}

	/**
	 * Function determines fields available in edition view
	 * @return string[]
	 */
	public function getEditFields()
	{
		return ['value' => 'FL_VALUE', 'roles' => 'FL_ROLES', 'smowners' => 'FL_SMOWNERS', 'showners' => 'FL_SHOWNERS'];
	}

	/**
	 * Function returns field instances for given name
	 * @param string $name
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		switch ($name) {
			case 'value':
				return Vtiger_Field_Model::getInstanceFromFieldId($this->get('fieldid'));
			case 'roles':
				return Vtiger_Field_Model::getInstance('roleid', Vtiger_Module_Model::getInstance('Users'));
			case 'smowners':
			case 'showners':
				return Vtiger_Field_Model::getInstance('assigned_user_id', Vtiger_Module_Model::getInstance($this->get('tabid')));
			default:
				break;
		}
		return NULL;
	}

	/**
	 * Function to get the Edit View Url
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return $this->getModule()->getEditViewUrl() . '&record=' . $this->getId();
	}

	/**
	 * Function returns url of selected tab in edition view
	 * @return string
	 */
	public function getEditViewTabUrl($tab)
	{
		return $this->getEditViewUrl() . '&tab=' . $tab;
	}

	/**
	 * Function changes the type of a given role
	 * @param string $member
	 */
	public function changeRoleType($member)
	{
		$memberArr = explode(':', $member);
		if ($memberArr[0] === \App\PrivilegeUtil::MEMBER_TYPE_ROLES) {
			$memberArr[0] = \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES;
		} else {
			$memberArr[0] = \App\PrivilegeUtil::MEMBER_TYPE_ROLES;
		}
		$roles = explode(',', $this->get('roles'));
		foreach ($roles as &$role) {
			if ($role === $member) {
				$role = implode(':', $memberArr);
				break;
			}
		}
		$this->set('roles', $roles);
		$this->save();
	}

	/**
	 * Function removes given value from record
	 * @param string $name
	 * @param string $value
	 */
	public function deleteElement($name, $value)
	{
		$values = explode(',', $this->get($name));
		$key = array_search($value, $values);
		if ($key !== false) {
			unset($values[$key]);
		}
		$this->set($name, $values);
		$this->save();
	}

	/**
	 * Function removes record
	 * @return boolean
	 */
	public function delete()
	{
		$db = App\Db::getInstance('admin');
		$recordId = $this->getId();
		if ($recordId) {
			$result = $db->createCommand()->delete($this->getTable(), ['id' => $recordId])->execute();
		}
		return !empty($result);
	}

	/**
	 * Function properly formats data for given field
	 * @param string $key
	 * @return int|array
	 */
	public function getEditValue($key)
	{
		switch ($key) {
			case 'roles':
				$rows = [];
				if ($this->get($key)) {
					$value = explode(',', $this->get($key));
					foreach ($value as $index => $val) {
						$data = explode(':', $val);
						$name = \App\Language::translate(\App\PrivilegeUtil::getRoleName($data[1]));
						$rows[$index]['type'] = $data[0];
						$rows[$index]['name'] = $name;
						$rows[$index]['id'] = $val;
					}
				}
				return $rows;
				break;
			case 'fieldid':
			case 'tabid':
				$value = (int) $value;
				break;
			case 'smowners':
			case 'showners':
				$rows = [];
				if ($this->get($key)) {
					$value = explode(',', $this->get($key));
					foreach ($value as $index => $val) {
						$name = \App\Language::translate(\App\Fields\Owner::getLabel($val));
						$rows[$index]['type'] = \App\Fields\Owner::getType($val);
						$rows[$index]['name'] = $name;
						$rows[$index]['id'] = $val;
					}
				}
				return $rows;
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function formats data for saving
	 * @param string $key
	 * @param mixed $value
	 * @return int|string
	 */
	private function getValueToSave($key, $value)
	{
		switch ($key) {
			case 'roles':
				if (!is_array($value)) {
					$value = array_filter(explode(',', $value));
				}
				if ($this->checkDuplicate) {
					$newVal = [];
					$oldVal = [];
					foreach ($value as $i => $val) {
						if (strpos($val, ':') !== false) {
							$valArr = explode(':', $val);
							$newVal[$valArr[1]] = $val;
						} else {
							$newVal[$val] = 'Roles:' . $val;
						}
					}
					if (isset($this->rawData[$key])) {
						$oldValue = array_filter(explode(',', $this->rawData[$key]));
						foreach ($oldValue as $i => $val) {
							if (strpos($val, ':') !== false) {
								$valArr = explode(':', $val);
								$oldVal[$valArr[1]] = $val;
							} else {
								$oldVal[$val] = 'Roles:' . $val;
							}
						}
					}
					$value = array_unique(array_merge($newVal, $oldVal));
				}
				$value = implode(',', $value);
				break;
			case 'fieldid':
				$value = (int) $value;
				break;
			case 'tabid':
				$value = (int) $value;
				break;
			case 'smowners':
			case 'showners':
				if (!is_array($value)) {
					$value = array_filter(explode(',', $value));
				}
				if ($this->checkDuplicate) {
					$oldValue = array_filter(explode(',', $this->rawData[$key]));
					$value = array_unique(array_merge($value, $oldValue));
				}
				$value = implode(',', $value);
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function to save
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$params = [];
		foreach ($this->getData() as $key => $value) {
			$params[$key] = $this->getValueToSave($key, $value);
		}
		if ($params && empty($this->getId())) {
			$seccess = $db->createCommand()->insert($this->getTable(), $params)->execute();
			if ($seccess) {
				$this->rawData = $params;
				$this->set('id', $db->getLastInsertID());
			}
		} elseif (!empty($this->getId())) {
			$db->createCommand()->update($this->getTable(), $params, ['id' => $this->getId()])->execute();
		}
	}

	/**
	 * Function to get the list view actions for the record
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
				[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			],
				[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Vtiger_List_Js.deleteRecord(' . $this->getId() . ');',
				'linkicon' => 'glyphicon glyphicon-trash'
			]
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get the instance, given id
	 * @param int $id
	 * @return \self
	 */
	public static function getInstanceById($id)
	{
		$cacheName = get_class();
		if (\App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$instance = self::getCleanInstance();
		$data = (new App\Db\Query())
			->from($instance->getTable())
			->where([$instance->getTableIndex() => $id])
			->one(App\Db::getInstance('admin'));
		$instance->setData($data);
		$instance->rawData = $data;
		\App\Cache::staticSave($cacheName, $id, $instance);
		return $instance;
	}

	/**
	 * Function to get the clean instance
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$cacheName = get_class();
		$key = 'Clean';
		if (\App\Cache::staticHas($cacheName, $key)) {
			return \App\Cache::staticGet($cacheName, $key);
		}
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
		$instance = new self();
		$instance->module = $moduleInstance;
		\App\Cache::staticSave($cacheName, $key, $instance);
		return $instance;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $name
	 * @return string
	 */
	public function getDisplayValue($name)
	{
		switch ($name) {
			case 'fieldid':
				$fieldInstance = Vtiger_Field_Model::getInstanceFromFieldId($this->get($name));
				return $fieldInstance->get('label');
			case 'tabid':
				return \App\Module::getModuleName($this->get($name));
			default:
				break;
		}
		return $this->get($name);
	}
}
