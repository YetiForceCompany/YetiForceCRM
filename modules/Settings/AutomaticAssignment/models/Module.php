<?php

/**
 * Automatic assignment module model class
 * @package YetiForce.Settings.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_Module_Model extends Settings_Vtiger_Module_Model
{

	/**
	 * Table name
	 * @var string 
	 */
	public $baseTable = 's_#__automatic_assignment';

	/**
	 * Table primary key
	 * @var string 
	 */
	public $baseIndex = 'id';

	/**
	 * List of fields displayed in list view
	 * @var string 
	 */
	public $listFields = ['tabid' => 'FL_MODULE', 'field' => 'FL_FIELD', 'value' => 'FL_VALUE', 'active' => 'FL_ACTIVE'];

	/**
	 * Module Name
	 * @var string 
	 */
	public $name = 'AutomaticAssignment';

	/**
	 * List of available field types
	 * @var string[] 
	 */
	private static $fieldType = ['string'];

	/**
	 * Function to get the url for Create view of the module
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Create';
	}

	/**
	 * Function to get the url for edit view of the module
	 * @return string - url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for default view of the module
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=List';
	}

	/**
	 * Function get supported modules
	 * @return array - List of modules
	 */
	public static function getSupportedModules()
	{
		return Vtiger_Module_Model::getAll([0], ['SMSNotifier', 'OSSMailView', 'Emails', 'Dashboard', 'ModComments', 'Notification'], true);
	}

	/**
	 * List of supported module fields
	 * @return array
	 */
	public static function getFieldsByModule($moduleName)
	{
		$accessibleFields = [];
		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleInstance->getFields() as $fieldName => $fieldObject) {
			if (in_array($fieldObject->getFieldDataType(), static::$fieldType) && $fieldObject->isActiveField() && $fieldObject->getUIType() !== 4) {
				$accessibleFields[$fieldObject->getBlockName()][$fieldName] = $fieldObject;
			}
		}
		return $accessibleFields;
	}

	/**
	 * Function returns list of fields available in list view
	 * @return Vtiger_Base_Model[]
	 */
	public function getListFields()
	{
		if (!isset($this->listFieldModels)) {
			$fields = $this->listFields;
			$fieldObjects = [];
			foreach ($fields as $fieldName => $fieldLabel) {
				$fieldObjects[$fieldName] = new Vtiger_Base_Model(['name' => $fieldName, 'label' => $fieldLabel, 'sort' => true]);
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function searches for record from the Auto assign records panel
	 * @param Vtiger_Record_Model $recordModel
	 * @return bool|Settings_AutomaticAssignment_Record_Model
	 */
	public function searchRecord(\Vtiger_Record_Model $recordModel)
	{
		$dataReader = (new \App\Db\Query())
				->select(['field', 'value', 'id'])
				->from($this->baseTable)
				->where(['tabid' => \App\Module::getModuleId($recordModel->getModuleName()), 'active' => 1])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			if ($row['value'] === $recordModel->get($row['field'])) {
				return Settings_AutomaticAssignment_Record_Model::getInstanceById($row['id']);
			}
		}
		return false;
	}
}
