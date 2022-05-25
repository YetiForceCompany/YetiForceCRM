<?php

/**
 * Export PDF modal view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Export PDF modal view class.
 */
class Vtiger_PDF_View extends Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($moduleName, 'ExportPdf')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('record', true) && !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$pdfModuleName = $moduleName = $request->getModule();
		$view = $request->getByType('fromview', \App\Purifier::STANDARD);
		$recordId = $request->isEmpty('record', true) ? null : $request->getInteger('record');
		if ($isRelatedView = ('RelatedList' === $view)) {
			$pdfModuleName = $request->getByType('relatedModule', \App\Purifier::ALNUM);
		}
		$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $pdfModuleName);
		$pdfModel = new $handlerClass();
		$dynamicTemplates = $records = [];
		$active = $activeDynamic = false;

		if ($isRelatedView) {
			$templates = $pdfModel->getActiveTemplatesForModule($pdfModuleName, $view);
			$records = \Vtiger_RelationAjax_Action::getRecordsListFromRequest($request);
		} elseif ($recordId) {
			$templates = $pdfModel->getActiveTemplatesForRecord($recordId, $view, $pdfModuleName);
			$records = [$recordId];
		} else {
			$templates = $pdfModel->getActiveTemplatesForModule($pdfModuleName, $view);
			$records = \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		}

		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName($pdfModuleName);
		$eventHandler->setParams([
			'records' => $records,
			'viewInstance' => $this,
			'pdfModel' => $pdfModel,
		]);
		$eventHandler->trigger('PdfModalBefore');

		$moduleModel = \Vtiger_Module_Model::getInstance($pdfModuleName);
		$isInventory = $moduleModel->isInventory();
		foreach ($templates as $key => $template) {
			$isTemplateActive = $template->get('default');
			if ($isTemplateActive && !$active) {
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
			foreach (\Vtiger_Inventory_Model::getInstance($pdfModuleName)->getFields() as $name => $field) {
				$allInventoryColumns[$name] = $field->get('label');
			}
			$viewer->assign('ALL_INVENTORY_COLUMNS', $allInventoryColumns);
			$viewer->assign('SELECTED_INVENTORY_COLUMNS', ($recordId && !$isRelatedView) ? \App\Pdf\InventoryColumns::getInventoryColumnsForRecord($recordId, $pdfModuleName) : array_keys($allInventoryColumns));
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
			$request->getByType('search_value', \App\Purifier::TEXT), $pdfModuleName,
			$request->getByType('search_key', \App\Purifier::ALNUM), $request->getByType('operator')
		));
		$viewer->assign('VIEW_NAME', $request->getByType('viewname', \App\Purifier::ALNUM));
		$viewer->assign('ENTITY_STATE', $request->isEmpty('entityState') ? '' : $request->getByType('entityState'));
		$viewer->assign('SELECT_MODE', ($request->isEmpty('selectMode', true) || 'multi' === $request->getByType('selectMode')) ? 'checkbox' : 'radio');
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', \App\Purifier::INTEGER));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', \App\Purifier::INTEGER));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', \App\Purifier::ALNUM));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($pdfModuleName, $request->getArray('search_params'), false));
		$viewer->assign('ORDER_BY', $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL));
		$advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : [];
		if ($advancedConditions) {
			\App\Condition::validAdvancedConditions($advancedConditions);
		}
		$viewer->assign('ADVANCED_CONDITIONS', $advancedConditions);
		if ($isRelatedView) {
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('RELATED_MODULE', $pdfModuleName);
			$viewer->assign('RELATION_ID', $request->getInteger('relationId'));
			$viewer->assign('CV_ID', $request->getByType('cvId', \App\Purifier::ALNUM));
		}
		$eventHandler->trigger('PdfModalAfter');
		$viewer->view('ExportPDF.tpl', $pdfModuleName);
		$this->postProcess($request);
	}
}
