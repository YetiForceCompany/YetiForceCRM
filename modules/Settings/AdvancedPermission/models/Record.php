<?php

/**
 * Advanced permission record model class
 * @package YetiForce.Settings.Record
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get the Id
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Edit View Url
	 * @return string URL
	 */
	public function getEditViewUrl($step = false)
	{
		$mode = '';
		if ($step !== false) {
			$mode = '&mode=step' . $step;
		}
		return '?module=AdvancedPermission&parent=Settings&view=Edit&record=' . $this->getId() . $mode;
	}

	/**
	 * Function to get the Delete Action Url
	 * @return string URL
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=AdvancedPermission&parent=Settings&action=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=AdvancedPermission&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of advanced permission record model
	 * @param int $id
	 * @return \self instance, if exists.
	 */
	public static function getInstance($id)
	{
		$db = \App\DB::getInstance('admin');
		$query = (new \App\db\Query())->from('a_#__adv_permission')->where(['id' => $id]);
		$row = $query->createCommand($db)->queryOne();
		$instance = false;
		if ($row !== false) {
			$row['conditions'] = json_decode($row['conditions'], true);
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to save
	 */
	public function save()
	{
		$db = \App\DB::getInstance('admin');
		$recordId = $this->getId();

		if ($recordId === false) {
			$params = [
				'name' => $this->get('name'),
				'tabid' => $this->get('tabid'),
			];
			$db->createCommand()->insert('a_#__adv_permission', $params)->execute();
			$id = $db->getLastInsertID();
			$this->set('id', $id);
		} else {
			$params = [];
			foreach ($this->getData() as $key => $value) {
				if ($this->has($key)) {
					$params[$key] = $value;
				}
			}
			$db->createCommand()->update('a_#__adv_permission', $params, ['id' => $recordId])->execute();
		}
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $key
	 * @return string
	 */
	public function getDisplayValue($key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'tabid':
				$value = \includes\Modules::getModuleName($value);
				break;
			case 'status':
				if (isset(Settings_AdvancedPermission_Module_Model::$status[$value])) {
					$value = Settings_AdvancedPermission_Module_Model::$status[$value];
				}
				break;
			case 'action':
				if (isset(Settings_AdvancedPermission_Module_Model::$action[$value])) {
					$value = Settings_AdvancedPermission_Module_Model::$action[$value];
				}
				break;
		}
		return $value;
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete()
	{
		$db = \App\DB::getInstance('admin');
		$db->createCommand()
			->delete('a_#__adv_permission', ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = [];
		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => $this->getDeleteActionUrl(),
				'linkicon' => 'glyphicon glyphicon-trash'
			)
		);
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}
}
