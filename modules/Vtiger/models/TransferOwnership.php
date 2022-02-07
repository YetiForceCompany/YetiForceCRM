<?php

/**
 * Vtiger TransferOwnership model class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_TransferOwnership_Model extends \App\Base
{
	protected $skipModules = [];

	public function getSkipModules()
	{
		return $this->skipModules;
	}

	public function getRelatedModuleRecordIds(App\Request $request, $recordIds, $relModData)
	{
		$basicModule = $request->getModule();
		$parentModuleModel = Vtiger_Module_Model::getInstance($basicModule);
		$relatedIds = [];
		$relModData = explode('::', $relModData);
		$relatedModule = $relModData[0];
		$type = $relModData[1];
		switch ($type) {
			case 0:
				$field = $relModData[2];
				foreach ($recordIds as $recordId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $basicModule);
					if (0 != $recordModel->get($field) && \App\Record::getType($recordModel->get($field)) == $relatedModule) {
						$relatedIds[] = $recordModel->get($field);
					}
				}
				break;
			case 1:
				$tablename = Vtiger_Relation_Model::getInstance($parentModuleModel, Vtiger_Module_Model::getInstance($relatedModule))->getRelationField()->get('table');
				$tabIndex = CRMEntity::getInstance($relatedModule)->table_index;
				$relIndex = $this->getRelatedColumnName($relatedModule, $basicModule);
				if (!$relIndex) {
					break;
				}
				$relatedIds = (new \App\Db\Query())->select([$tabIndex])->from($tablename)->where([$relIndex => $recordIds])->column();
				break;
			case 2:
				foreach ($recordIds as $recordId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $basicModule);
					$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModule);
					$relatedIds = $relationListView->getRelationQuery()->select(['vtiger_crmentity.crmid'])
						->distinct()
						->column();
				}
				break;
			default:
				break;
		}
		return array_unique($relatedIds);
	}

	public function transferRecordsOwnership($module, $transferOwnerId, $relatedModuleRecordIds)
	{
		foreach ($relatedModuleRecordIds as $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
			if($recordModel->isEditable()){
				$recordModel->set('assigned_user_id', $transferOwnerId);
				$recordModel->save();
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
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $relation) {
					if (\App\Privilege::isPermitted($relation, 'EditView')) {
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
		$relatedModules = [];
		foreach ($moduleModel->getRelations() as $relation) {
			$relationModule = $relation->getRelationModuleName();
			if (\App\Privilege::isPermitted($relationModule, 'EditView')) {
				$relatedModules[] = [
					'name' => $relationModule,
					'type' => $relation->getRelationType(),
				];
			}
		}
		return $relatedModules;
	}

	public function getRelatedColumnName($relatedModule, $findModule)
	{
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relatedModelFields = $relatedModuleModel->getFields();
		foreach ($relatedModelFields as $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				if (\in_array($findModule, $referenceList)) {
					return $fieldModel->get('column');
				}
			}
		}
		return false;
	}
}
