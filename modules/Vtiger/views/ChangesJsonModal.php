<?php
/**
 * ChangesJson data modal.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Class ChangesJsonModal.
 */
class Vtiger_ChangesJsonModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-full-editing-view';

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \Vtiger_Module_Model::getInstance($request->getByType('sourceModule', \App\Purifier::ALNUM))
			->getFieldByName($request->getByType('sourceField', \App\Purifier::ALNUM))->getFullLabelTranslation();
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$record = $request->getInteger('record');
		$sourceField = $request->getByType('sourceField', \App\Purifier::ALNUM);
		$sourceModule = $request->getByType('sourceModule', \App\Purifier::ALNUM);
		if (!$sourceField || !\Vtiger_Module_Model::getInstance($sourceModule)->getFieldByName($sourceField)->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ($record && !\Vtiger_Record_Model::getInstanceById($record, $request->getModule())->isViewable()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = 'Edit';
		return array_merge($this->checkAndConvertJsScripts([
			"modules.Vtiger.resources.{$viewName}",
			"modules.{$moduleName}.resources.{$viewName}"
		]), parent::getModalScripts($request));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$changes = $request->getArray('changes');
		$recordStructure = $fieldInfo = [];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleModel->getBlocks() as $blockLabel => $blockModel) {
			foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
				if ($fieldModel->isEditable() && $fieldModel->isMassEditable() && $fieldModel->isViewable() && !\in_array($fieldModel->getFieldDataType(), ['image', 'multiImage', 'accountName'])) {
					if (isset($changes[$fieldName])) {
						$fieldModel->getUITypeModel()->validate($changes[$fieldName], true);
						$fieldModel->set('fieldvalue', $fieldModel->getUITypeModel()->getDBValue($changes[$fieldName]));
					}
					$recordStructure[$blockLabel][$fieldName] = $fieldModel;
					$fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('EDIT_FIELD_DETAILS', $fieldInfo);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('LIST_FILTER_FIELDS', \App\Json::encode(\App\ModuleHierarchy::getFieldsForListFilter($moduleName)));
		$viewer->view('Modals/ChangesJson.tpl', $moduleName);
	}
}
