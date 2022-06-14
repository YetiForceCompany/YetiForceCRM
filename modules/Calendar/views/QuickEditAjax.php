<?php

/**
 * QuickEdit view for module Calendar.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Calendar_QuickEditAjax_View.
 */
class Calendar_QuickEditAjax_View extends Calendar_QuickCreateAjax_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$this->record->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($this->record, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$recordStructure = $recordStructureInstance->getStructure();

		$viewer = $this->getViewer($request);
		$viewer->assign('QUICKCREATE_LINKS', Vtiger_QuickCreateView_Model::getInstance($moduleName)->getLinks([]));
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('LIST_FILTER_FIELDS', \App\Json::encode(\App\ModuleHierarchy::getFieldsForListFilter($moduleName)));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('RECORD', $this->record);
		$viewer->assign('RECORD_ID', $this->record->getId());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('VIEW', $request->getByType('view'));
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return $this->record->getName();
	}
}
