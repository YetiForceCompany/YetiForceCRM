<?php

/**
 * Calendar EventForm View class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_EventForm_View extends Vtiger_QuickCreateAjax_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('record')) {
			$this->recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			if (!$this->recordModel->isEditable()
				|| (true === $request->getBoolean('isDuplicate') && (!$this->recordModel->isCreateable() || !$this->recordModel->isPermitted('ActivityPostponed')))
			) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			parent::checkPermission($request);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		if ($request->has('record')) {
			$recordModel = $this->recordModel ?: Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
			$this->fields = $recordModel->getModule()->getFields();
			$this->loadFieldValuesFromRequest($request);
			$recordStructureInstance = $this->getRecordStructure();
			$this->recordStructure = $recordStructureInstance->getStructure();
			$fieldValues = $this->loadFieldValuesFromSource($request);
			$viewer->assign('QUICKCREATE_LINKS', Vtiger_QuickCreateView_Model::getInstance($moduleName)->getLinks([]));
			$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
			$viewer->assign('LIST_FILTER_FIELDS', \App\Json::encode(\App\ModuleHierarchy::getFieldsForListFilter($moduleName)));
			$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
			$viewer->assign('RECORD_STRUCTURE', $this->recordStructure);
			$viewer->assign('SOURCE_RELATED_FIELD', $fieldValues);
			$viewer->assign('IS_POSTPONED', $request->getBoolean('isDuplicate'));
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('RECORD_ID', $request->getBoolean('isDuplicate') ? '' : $recordModel->getId());
			$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
			$viewer->assign('VIEW', $request->getByType('view'));
		} else {
			parent::process($request);
			$viewer->assign('RECORD', null);
			$viewer->assign('RECORD_ID', '');
		}
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Calendar/EventForm.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$jsFiles = parent::getFooterScripts($request);
		if (!empty(App\Config::module('Calendar', 'SHOW_ACTIVITY_BUTTONS_IN_EDIT_FORM'))) {
			$jsFiles = array_merge($jsFiles, $this->checkAndConvertJsScripts([
				'modules.Calendar.resources.ActivityStateModal',
			]));
		}
		return $jsFiles;
	}
}
