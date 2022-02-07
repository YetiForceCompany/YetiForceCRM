<?php
/**
 * Settings fields dependency module model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings fields dependency module model class.
 */
class Settings_FieldsDependency_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $baseTable = 's_#__fields_dependency';

	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'LBL_NAME',
		'tabid' => 'LBL_SOURCE_MODULE',
		'status' => 'FL_ACTIVE',
		'views' => 'LBL_VIEWS',
		'gui' => 'LBL_GUI',
		'mandatory' => 'LBL_MANDATORY',
		'fields' => 'LBL_FIELDS',
	];

	/** {@inheritdoc} */
	public $name = 'FieldsDependency';

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return 'index.php?parent=Settings&module=FieldsDependency&view=List';
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl()
	{
		return 'index.php?parent=Settings&module=FieldsDependency&view=Edit';
	}

	/**
	 * Function to get Supported modules for fields dependency.
	 *
	 * @return array
	 */
	public static function getSupportedModules(): array
	{
		return Vtiger_Module_Model::getAll([0, 2], [], true);
	}

	/**
	 * Remove fields from dependent field entries.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 */
	public static function removeField(string $moduleName, string $fieldName)
	{
		$tabId = \App\Module::getModuleId($moduleName);
		$dataReader = (new \App\Db\Query())->select(['id'])->from('s_#__fields_dependency')
			->orWhere(['and', ['tabid' => $tabId], ['or', ['like', 'fields', "\"{$fieldName}\""], ['like', 'conditionsFields', "\"{$fieldName}\""]]])
			->orWhere(['like', 'conditions', $fieldName])->createCommand()->query();
		while ($fieldDependId = $dataReader->readColumn(0)) {
			$fieldDepend = \Settings_FieldsDependency_Record_Model::getInstanceById($fieldDependId);
			$conditions = \App\Json::decode($fieldDepend->get('conditions'));
			$baseModuleName = \App\Module::getModuleName($fieldDepend->get('tabid'));
			$conditions = \App\Condition::removeFieldFromCondition($baseModuleName, $conditions, $moduleName, $fieldName);
			$fieldDepend->set('conditions', $conditions);
			if ($baseModuleName === $moduleName) {
				$fields = \App\Json::decode($fieldDepend->get('fields'));
				if (false !== ($key = array_search($fieldName, $fields))) {
					unset($fields[$key]);
				}
				if (empty($fields)) {
					$fieldDepend->delete();
					continue;
				}
				$fieldDepend->set('fields', $fields);
			}
			$fieldDepend->save();
		}
	}
}
