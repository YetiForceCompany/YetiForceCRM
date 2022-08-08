<?php
/**
 * Base quick edit modal view class file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Base quick edit modal view class.
 */
class Vtiger_QuickEditModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-xl';
	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true) || !\App\Privilege::isPermitted($request->getModule(), 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$viewer = $this->getViewer($request);
		$viewer->assign('QUICKCREATE_LINKS', $this->getLinks($recordModel));
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Modals/QuickEditHeader.tpl';
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return $request->has('modalTitle') ? $request->getByType('modalTitle', 'Text') : '';
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$changedFields = $noFieldsAccess = [];
		$changedFieldsExist = false;
		foreach (array_intersect($request->getKeys(), array_keys($fieldList)) as $fieldName) {
			$fieldModel = $fieldList[$fieldName];
			$changedFieldsExist = true;
			if ($fieldModel->isWritable()) {
				$uitypeModel = $fieldModel->getUITypeModel();
				$uitypeModel->setValueFromRequest($request, $recordModel);
				if ($uitypeModel->validateValue($recordModel->get($fieldName))) {
					$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
					$changedFields[$fieldName] = $fieldModel;
				} elseif ($fieldModel->isViewEnabled()) {
					$noFieldsAccess[$fieldModel->getFieldLabel()] = '-';
				}
			} elseif ($fieldModel->isViewEnabled()) {
				$noFieldsAccess[$fieldModel->getFieldLabel()] = $fieldModel->getDisplayValue($recordModel->get($fieldName));
			}
		}
		$recordStructure = $this->getStructure($recordModel, $request);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_STRUCTURE_MODEL', Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel));
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$layout = $request->getByType('showLayout') ?: Config\Performance::$quickEditLayout ?? 'blocks';
		$layout = 'Calendar' === $moduleName ? 'standard' : $layout;

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);
		$eventHandler->setParams([
			'mode' => 'QuickEdit',
			'layout' => $layout,
			'viewInstance' => $this,
		]);
		$eventHandler->trigger('EditViewBefore');
		['layout' => $layout] = $eventHandler->getParams();

		if ('blocks' === $layout) {
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
		foreach (array_intersect_key($recordStructure, $changedFields) as $key => $value) {
			unset($changedFields[$key]);
		}
		$viewer->assign('SHOW_ALERT_NO_POWERS', ($changedFieldsExist && !$changedFields && !$recordStructure));
		$viewer->assign('ADDRESS_BLOCK_LABELS', ['LBL_ADDRESS_INFORMATION', 'LBL_ADDRESS_MAILING_INFORMATION', 'LBL_ADDRESS_DELIVERY_INFORMATION', 'LBL_ADDRESS_BILLING', 'LBL_ADDRESS_SHIPPING']);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('LIST_FILTER_FIELDS', \App\Json::encode(\App\ModuleHierarchy::getFieldsForListFilter($moduleName)));
		$viewer->assign('LAYOUT', $layout);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('CHANGED_FIELDS', $changedFields);
		$viewer->assign('NO_FIELD_ACCESS', $noFieldsAccess);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('MODE', 'edit');
		$viewer->assign('RECORD_ACTIVITY_NOTIFIER', $record && \App\Config::performance('recordActivityNotifier', false) && $moduleModel->isTrackingEnabled() && $moduleModel->isPermitted('RecordActivityNotifier'));
		$viewer->view('Modals/QuickEdit.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'modules.Vtiger.resources.QuickEditModal',
			"modules.$moduleName.resources.QuickEditModal",
		]);
	}

	/** {@inheritdoc} */
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
		$picklistValues = $request->getArray('picklistValues', 'Text') ?? [];
		if (!$request->isEmpty('editFields', true)) {
			$fieldModelList = [];
			if ('none' !== $request->getRaw('editFields')) {
				foreach ($request->getArray('editFields', 'Alnum') as $fieldName) {
					$fieldModel = $moduleModel->getFieldByName($fieldName);
					if ($fieldModel && $fieldModel->isEditable()) {
						if ('picklist' === $fieldModel->getFieldDataType() && isset($picklistValues[$fieldName])) {
							$fieldModel->picklistValues = $picklistValues[$fieldName];
						}
						$fieldModelList[$fieldName] = $fieldModel;
					}
				}
			}
		} else {
			$fieldModelList = $moduleModel->getQuickCreateFields();
		}
		$fieldsDependency = \App\FieldsDependency::getByRecordModel('QuickEdit', $recordModel);
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if ($fieldsDependency['hide']['backend'] && \in_array($fieldName, $fieldsDependency['hide']['backend'])) {
				continue;
			}
			$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
			if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
				Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
			}
			if (($mandatoryFields && \in_array($fieldName, $mandatoryFields)) || ($fieldsDependency['mandatory'] && \in_array($fieldName, $fieldsDependency['mandatory']))) {
				$fieldModel->set('isMandatory', true);
			}
			if ($fieldsDependency['hide']['frontend'] && \in_array($fieldName, $fieldsDependency['hide']['frontend'])) {
				$fieldModel->set('hideField', true);
			}
			$values[$fieldName] = $fieldModel;
		}
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $values;
	}

	/**
	 * Function to get the list of links for the module.
	 *
	 * @param \Vtiger_Record_Model $recordMode
	 * @param Vtiger_Record_Model  $recordModel
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getLinks(Vtiger_Record_Model $recordModel)
	{
		$links = Vtiger_Link_Model::getAllByType($recordModel->getModule()->getId(), ['QUICKCREATE_VIEW_HEADER', 'EDIT_VIEW_RECORD_COLLECTOR'], []);
		$links['QUICKEDIT_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'QUICKEDIT_VIEW_HEADER',
			'linkhint' => 'LBL_GO_TO_FULL_FORM',
			'showLabel' => 1,
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkdata' => ['js' => 'click', 'url' => $recordModel->getEditViewUrl()],
			'linkclass' => 'btn-light js-full-editlink fontBold u-text-ellipsis mb-2 mb-md-0 col-12',
		]);
		return $links;
	}
}
