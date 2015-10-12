<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Vtiger_TransferOwnership_Model extends Vtiger_Base_Model
{

	protected $skipModules = ['Emails'];

	public function getSkipModules()
	{
		return $this->skipModules;
	}

	public function getRelatedModuleRecordIds(Vtiger_Request $request, $recordIds = [])
	{
		$db = PearDatabase::getInstance();
		$basicModule = $request->getModule();

		$relatedModules = $request->get('related_modules');
		$parentModuleModel = Vtiger_Module_Model::getInstance($basicModule);

		$relatedIds = [];
		if (!empty($relatedModules)) {
			foreach ($relatedModules as $relModData) {
				$relModData = explode('::', $relModData);
				$relatedModule = $relModData[0];
				$type = $relModData[1];
				switch ($type) {
					case 0:

						$field = $relModData[2];
						foreach ($recordIds as $recordId) {
							$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $basicModule);
							if ($recordModel->get($field) != 0 && Vtiger_Functions::getCRMRecordType($recordModel->get($field)) == $relatedModule) {
								$relatedIds[] = $recordModel->get($field);
							}
						}

						break;
					case 1:

						$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
						$instance = CRMEntity::getInstance($relatedModule);
						$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel);
						$fieldModel = $relationModel->getRelationField();
						$tablename = $fieldModel->get('table');
						$tabIndex = $instance->table_index;
						$relIndex = $this->getRelatedFieldName($relatedModule, $basicModule);

						if (!$relIndex) {
							break;
						}
						$sql = "SELECT vtiger_crmentity.crmid FROM vtiger_crmentity INNER JOIN $tablename ON $tablename.$tabIndex = vtiger_crmentity.crmid
						WHERE $tablename.$relIndex IN (" . $db->generateQuestionMarks($recordIds) . ")";
						$result = $db->pquery($sql, $recordIds);
						while ($crmid = $db->getSingleValue($result)) {
							$relatedIds[] = $crmid;
						}

						break;
					case 2:

						foreach ($recordIds as $recordId) {
							$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $basicModule);
							$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModule);
							$query = $relationListView->getRelationQuery();
							$queryEx = explode('FROM', $query, 2);
							$query = 'SELECT DISTINCT vtiger_crmentity.crmid FROM' . $queryEx[1];
							$result = $db->query($query);
							while ($crmid = $db->getSingleValue($result)) {
								$relatedIds[] = $crmid;
							}
						}

						break;
				}
				$relatedIds = array_unique($relatedIds);
			}
		}
		return $relatedIds;
	}

	public function transferRecordsOwnership($module, $transferOwnerId, $relatedModuleRecordIds)
	{
		$currentUser = vglobal('current_user');
		$db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_crmentity SET smownerid = ?, modifiedby = ?, modifiedtime = NOW() WHERE crmid IN (' . $db->generateQuestionMarks($relatedModuleRecordIds) . ')';
		$db->pquery($query, [$transferOwnerId, $currentUser->id, $relatedModuleRecordIds]);

		vimport('~modules/ModTracker/ModTracker.php');
		$flag = ModTracker::isTrackingEnabledForModule($module);
		if ($flag) {
			foreach ($relatedModuleRecordIds as $record) {
				$id = $db->getUniqueID('vtiger_modtracker_basic');
				$query = 'INSERT INTO vtiger_modtracker_basic ( id, whodid, whodidsu, changedon, crmid, module ) SELECT ? , ? , ?, ?, crmid, setype FROM vtiger_crmentity WHERE crmid = ?';
				$db->pquery($query, [$id, $currentUser->id, Vtiger_Session::get('baseUserId'), date('Y-m-d H:i:s', time()), $record]);

				$query = 'INSERT INTO vtiger_modtracker_detail ( id, fieldname, postvalue , prevalue ) SELECT ? , ? ,? , smownerid FROM vtiger_crmentity WHERE crmid = ?';
				$db->pquery($query, [$id, 'assigned_user_id', $currentUser->id, $record]);
			}
		}
	}

	public static function getInstance($module)
	{
		$instance = Vtiger_Cache::get('transferOwnership', $module);
		if (!$instance) {
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TransferOwnership', $module);
			$instance = new $modelClassName();
			$instance->set('module', $module);
			Vtiger_Cache::set('transferOwnership', $module, $instance);
		}
		return $instance;
	}

	public function getRelationsByFields($privileges = true)
	{
		$module = $this->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$relatedModelFields = $moduleModel->getFields();

		$relatedModules = [];
		foreach ($relatedModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $relation) {
					if (Users_Privileges_Model::isPermitted($relation, 'EditView')) {
						$relatedModules[] = ['name' => $relation, 'field' => $fieldName];
					}
				}
			}
		}
		return $relatedModules;
	}

	public function getRelationsByRelatedList($privileges = true)
	{
		$module = $this->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$relatedModelFields = $moduleModel->getFields();

		$relatedModules = [];
		$relations = $moduleModel->getRelations();
		foreach ($relations as $relation) {
			$relationModule = $relation->getRelationModuleName();
			if (Users_Privileges_Model::isPermitted($relationModule, 'EditView')) {
				$relatedModules[] = [
					'name' => $relationModule,
					'type' => $relation->getRelationType(),
				];
			}
		}
		return $relatedModules;
	}

	public function getRelatedFieldName($relatedModule, $findModule)
	{
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relatedModelFields = $relatedModuleModel->getFields();
		foreach ($relatedModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $relation) {
					if ($relation == $findModule) {
						return $fieldName;
					}
				}
			}
		}
		return false;
	}
}
