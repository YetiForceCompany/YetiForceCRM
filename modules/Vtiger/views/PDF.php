<?php

/**
 * Export PDF Modal View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_PDF_View extends Vtiger_BasicModal_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'ExportPdf')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('record') && !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$view = $request->getByType('fromview', 'Standard');
		$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdfModel = new $handlerClass();

		$dynamicTemplates = $records = $templateIds = [];
		$active = false;
		$activeDynamic = false;

		if ($recordId) {
			$templates = $pdfModel->getActiveTemplatesForRecord($recordId, $view, $moduleName);
			$records = [$recordId];
		} else {
			$templates = $pdfModel->getActiveTemplatesForModule($moduleName, $view);
			$records = \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		}

		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$isInventory = $moduleModel->isInventory();
		foreach ($templates as $key => $template) {
			$isTemplateActive = $template->get('default');
			if ($isTemplateActive && !$active) {
				$templateIds[] = $key;
				foreach ($records as $record) {
					if ($template->checkFiltersForRecord((int) $record)) {
						$active = true;
						break;
					}
				}
			}
			if ($isInventory && \Vtiger_PDF_Model::TEMPLATE_TYPE_DYNAMIC === $template->get('type')) {
				$dynamicTemplates[] = $template;
				if ($isTemplateActive && !$activeDynamic) {
					$activeDynamic = true;
				}
				unset($templates[$key]);
			}
		}
		if ($isInventory) {
			$allInventoryColumns = [];
			foreach (\Vtiger_Inventory_Model::getInstance($moduleName)->getFields() as $name => $field) {
				$allInventoryColumns[$name] = $field->get('label');
			}
			$viewer->assign('ALL_INVENTORY_COLUMNS', $allInventoryColumns);
			$viewer->assign('SELECTED_INVENTORY_COLUMNS', $recordId ? \App\Pdf\InventoryColumns::getInventoryColumnsForRecord($recordId, $moduleName) : array_keys($allInventoryColumns));
			$viewer->assign('CAN_CHANGE_SCHEME', $moduleModel->isPermitted('RecordPdfInventory'));
		}
		$viewer->assign('STANDARD_TEMPLATES', $templates);
		$viewer->assign('DYNAMIC_TEMPLATES', $dynamicTemplates);
		$viewer->assign('ACTIVE_DYNAMIC', $activeDynamic);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('FROM_VIEW', $view);
		$viewer->assign('ACTIVE', $active);
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', App\Condition::validSearchValue(
			$request->getByType('search_value', \App\Purifier::TEXT),
			$moduleName,
			$request->getByType('search_key', \App\Purifier::ALNUM), $request->getByType('operator')
		));
		$viewer->assign('VIEW_NAME', $request->getByType('viewname', \App\Purifier::ALNUM));
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', \App\Purifier::INTEGER));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', \App\Purifier::INTEGER));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', \App\Purifier::ALNUM));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($moduleName, $request->getArray('search_params')));
		$viewer->view('ExportPDF.tpl', $moduleName);
		$this->postProcess($request);
	}
}
