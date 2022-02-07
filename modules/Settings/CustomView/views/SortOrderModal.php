<?php

/**
 * Sorting View Class for CustomView.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_SortOrderModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SORTING_SETTINGS';

	/** {@inheritdoc} */
	public $successBtn = 'LBL_SET';

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->modalIcon = 'fas fa-sort';
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$viewName = $request->getByType('view', \App\Purifier::ALNUM);
		return array_merge($this->checkAndConvertJsScripts([
			"modules.Vtiger.resources.{$viewName}"
		]), parent::getModalScripts($request));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$sourceModuleModel = \Vtiger_Module_Model::getInstance($request->getInteger('sourceModule'));
		$customView = CustomView_Record_Model::getInstanceById($request->getInteger('cvid'));
		$viewer = $this->getViewer($request);
		$viewer->assign('CVID', $customView->getId());
		$viewer->assign('SORT_ORDER_BY', $customView->getSortOrderBy());
		$viewer->assign('RECORD_STRUCTURES', $this->getStructure($sourceModuleModel->getName()));
		$viewer->assign('SOURCE_MODULE_MODEL', $sourceModuleModel);
		$viewer->assign('SOURCE_MODULE', $sourceModuleModel->getName());
		$viewer->view('SortOrderModal.tpl', $moduleName);
	}

	/**
	 * The function returns module fields together with the related module fields.
	 *
	 * @param string|null $moduleName
	 * @param string|null $referenceFieldName
	 *
	 * @return array
	 */
	public function getStructure(string $moduleName = null, ?string $referenceFieldName = null): array
	{
		$structures = [];
		foreach (\Vtiger_Module_Model::getInstance($moduleName)->getFields() as $fieldModel) {
			if ($fieldModel->isViewable() && $fieldModel->isListviewSortable()) {
				if (null === $referenceFieldName) {
					if ($fieldModel->isReferenceField()) {
						foreach ($fieldModel->getReferenceList() as $relatedModuleName) {
							if ($structure = $this->getStructure($relatedModuleName, $fieldModel->getName())) {
								$structures[$relatedModuleName][$fieldModel->getName()] = $structure;
							}
						}
					}
					$structures[$moduleName][0][$fieldModel->getBlockName()][$fieldModel->getName()] = $fieldModel;
				} else {
					$fieldModel = clone $fieldModel;
					$fieldModel->set('source_field_name', $referenceFieldName);
					$structures[$fieldModel->getBlockName()][$fieldModel->getName()] = $fieldModel;
				}
			}
		}
		return $structures;
	}
}
