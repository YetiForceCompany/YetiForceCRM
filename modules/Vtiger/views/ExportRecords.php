<?php

/**
 * Export records view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Class ExportRecord.
 */
class Vtiger_ExportRecords_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $pageTitle = 'LBL_QUICK_EXPORT';
	/** {@inheritdoc} */
	public $successBtn = 'LBL_GENERATE';
	/** {@inheritdoc} */
	public $successBtnIcon = 'fas fa-file-export';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-file-export';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'QuickExportToExcel')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process function.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('EXPORT_TYPE', \App\Export\Records::getSupportedFileFormats($moduleName));
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $this->getRecordStructureModuleFields());
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($this->moduleModel)->getStructure());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view('Modals/ExportRecords.tpl', $moduleName);
	}

	/**
	 * Function get record structure for a module.
	 *
	 * @return array
	 */
	public function getRecordStructureModuleFields(): array
	{
		$recordStructureModulesField = [];
		foreach ($this->moduleModel->getFieldsByReference() as $referenceField) {
			if (!$referenceField->isActiveField()) {
				continue;
			}
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
			}
		}
		return $recordStructureModulesField;
	}
}
