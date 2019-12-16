<?php
/**
 * Base quick edit modal view class file.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Base quick edit modal view class.
 */
class Vtiger_QuickEditModal_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-xl';
	/**
	 * {@inheritdoc}
	 */
	public $showFooter = false;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true) || !\App\Privilege::isPermitted($request->getModule(), 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Modals/QuickEditHeader.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$changedFields = [];
		foreach (array_intersect($request->getKeys(), array_keys($fieldList)) as $fieldName) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel);
				$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
				$changedFields[$fieldName] = $fieldModel;
			}
		}
		$recordStructure = $this->getStructure($recordModel, $request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$layout = $request->getByType('showLayout') ?? Config\Performance::$quickEditLayout ?? 'standard';
		if ('blocks' === $layout && 'Calendar' !== $moduleName) {
			$layout = 'blocks';
			$blockModels = $moduleModel->getBlocks();
			$blockRecordStructure = $blockIdFieldMap = [];
			foreach ($recordStructure as $fieldModel) {
				$blockIdFieldMap[$fieldModel->getBlockId()][$fieldModel->getName()] = $fieldModel;
				$blockRecordStructure[$fieldModel->block->label][$fieldModel->name] = $fieldModel;
			}
			foreach ($blockModels as $blockModel) {
				if (isset($blockIdFieldMap[$blockModel->get('id')])) {
					$blockModel->setFields($blockIdFieldMap[$blockModel->get('id')]);
				}
			}
			$viewer->assign('RECORD_STRUCTURE', $blockRecordStructure);
			$viewer->assign('BLOCK_LIST', $blockModels);
		}
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode(\App\Fields\Picklist::getPicklistDependencyDatasource($moduleName)));
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('LAYOUT', $layout);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('CHANGED_FIELDS', $changedFields);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('MODE', 'edit');
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT', \App\Config::main('upload_maxsize'));
		$viewer->view('Modals/QuickEdit.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'modules.Vtiger.resources.QuickEditModal',
			"modules.$moduleName.resources.QuickEditModal",
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}

	/**
	 * Function to get the values in stuctured format.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param App\Request          $request
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getStructure(Vtiger_Record_Model $recordModel, App\Request $request): array
	{
		Vtiger_Field_Model::$tabIndexDefaultSeq = 1000;
		$values = [];
		$moduleModel = $recordModel->getModule();
		$mandatoryFields = $request->getArray('mandatoryFields', 'Alnum') ?? [];
		if (!$request->isEmpty('editFields', true)) {
			$fieldModelList = [];
			foreach ($request->getArray('editFields', 'Alnum') as $fieldName) {
				$fieldModel = $moduleModel->getFieldByName($fieldName);
				if ($fieldModel->isEditable()) {
					$fieldModelList[$fieldName] = $fieldModel;
				}
			}
		} else {
			$fieldModelList = $moduleModel->getQuickCreateFields();
		}
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
			if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
				Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
			}
			if ($mandatoryFields && \in_array($fieldName, $mandatoryFields)) {
				$fieldModel->set('isMandatory', true);
			}
			$values[$fieldName] = $fieldModel;
		}
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $values;
	}
}
