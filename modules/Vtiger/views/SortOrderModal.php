<?php

/**
 * Sort order in the list.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_SortOrderList_View.
 */
class Vtiger_SortOrderModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SORTING_SETTINGS';

	/** {@inheritdoc} */
	public $successBtn = 'LBL_SET';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule()) ||
			!\Vtiger_Module_Model::getInstance($request->getModule())->isAdvSortEnabled()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->modalIcon = 'fas fa-sort';
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$view = $request->getByType('fromView', \App\Purifier::STANDARD);
		$sourceModuleModel = Vtiger_Module_Model::getInstance($moduleName);
		$structures[$moduleName][] = $this->getStructure($moduleName);
		if ('Detail' !== $view) {
			foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
				if ($referenceField->isViewable()) {
					foreach ($referenceField->getReferenceList() as $relatedModuleName) {
						if ($structure = $this->getStructure($relatedModuleName, $referenceField)) {
							$structures[$relatedModuleName][$referenceField->getName()] = $structure;
						}
					}
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD_STRUCTURES', $structures);
		$viewer->assign('SOURCE_MODULE_MODEL', $sourceModuleModel);
		$viewer->assign('SOURCE_MODULE', $sourceModuleModel->getName());
		$viewer->view('Modals/SortOrderModal.tpl', $moduleName);
	}

	private function getStructure(string $moduleName, ?Vtiger_Field_Model $referenceField = null): array
	{
		$structure = [];
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleModel->getFields() as $fieldModel) {
			if ($fieldModel->isViewable() && $fieldModel->isListviewSortable()) {
				$fieldModel = clone $fieldModel;
				if ($referenceField) {
					$fieldModel->set('source_field_name', $referenceField->getName());
				}
				$structure[$fieldModel->getBlockName()][$fieldModel->getName()] = $fieldModel;
			}
		}
		return $structure;
	}
}
