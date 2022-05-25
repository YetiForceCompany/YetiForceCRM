<?php

/**
 * Mass SMS creation view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mass SMS creation view class.
 */
class SMSNotifier_MassSMS_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SEND_SMS_TO_SELECTED_NUMBERS';
	/** {@inheritdoc} */
	public $modalIcon = 'yfm-SMSNotifier';
	/** {@inheritdoc} */
	public $successBtn = 'LBL_SEND';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$moduleModel->isPermitted('CreateView') || !\in_array($sourceModule, $moduleModel->getFieldByName('related_to')->getReferenceList()) || !$userPrivilegesModel->hasModuleActionPermission($sourceModule, 'MassSendSMS') || !\App\Integrations\SMSProvider::isActiveProvider()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$selectedIds = $request->getArray('selected_ids', 2);
		$query = $this->getRecordsListQueryFromRequest($request)->clearFields()->setFields(['id'])->createQuery();
		$total = $query->count();
		if (1 === $total && $recordId = $query->scalar()) {
			$selectedRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);
			$viewer->assign('SINGLE_RECORD', $selectedRecordModel);
		}
		$templates = [];
		$templateModel = \Vtiger_Module_Model::getInstance('SMSTemplates');
		if ($templateModel->isActive() && $templateModel->getFieldByName('message')->isViewable()) {
			$queryGenerator = new \App\QueryGenerator($templateModel->getName());
			$queryGenerator->setFields(['id', 'message'])->addCondition('target', $sourceModule, 'e');
			$dataReader = $queryGenerator->setLimit(100)->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				$id = $row['id'];
				$row['name'] = \App\Record::getLabel($id, true);
				$templates[$id] = $row;
			}
		}

		$phoneFields = [];
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		foreach ($sourceModuleModel->getFieldsByType('phone', true) as $fieldName => $fieldModel) {
			if ($fieldModel->isViewable()) {
				$phoneFields[$fieldName] = $fieldModel;
			}
		}

		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$cvId = !$request->isEmpty('cvId') ? $request->getByType('cvId', \App\Purifier::ALNUM) : $request->getByType('viewname', \App\Purifier::ALNUM);

		$viewer->assign('TEMPLATES', $templates);
		$viewer->assign('FIELD_MESSAGE', $moduleModel->getFieldByName('message'));
		$viewer->assign('FIELD_IMAGE', $moduleModel->getFieldByName('image'));
		$viewer->assign('SMS_LIMIT', \App\Config::module($moduleName, 'maxMassSentSMS'));
		$viewer->assign('VIEWNAME', 'relation' !== $cvId ? $cvId : '');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 2));
		$viewer->assign('ENTITY_STATE', $request->getByType('entityState'));
		$viewer->assign('PHONE_FIELDS', $phoneFields);
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 'Alnum'));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($sourceModule, $request->getArray('search_params'), false));
		$advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : [];
		if ($advancedConditions) {
			\App\Condition::validAdvancedConditions($advancedConditions);
		}
		$viewer->assign('ADVANCED_CONDITIONS', $advancedConditions);
		$viewer->view('MassSMS.tpl', $request->getModule());
	}

	/**
	 * Get query for records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\QueryGenerator
	 */
	public function getRecordsListQueryFromRequest(App\Request $request): App\QueryGenerator
	{
		$module = $request->getModule();
		$sourceModule = $request->getByType('source_module', 'Alnum');
		$selectedIds = $request->getArray('selected_ids', 'Alnum');
		$excludedIds = $request->getArray('excluded_ids', 'Alnum');
		if (!empty($selectedIds) && !\in_array($selectedIds[0], ['all', '"all"'])) {
			$queryGenerator = new \App\QueryGenerator($sourceModule);
			$queryGenerator->addCondition('id', $selectedIds, 'e');
		} elseif ($customViewModel = CustomView_Record_Model::getInstanceById($request->getByType('viewname', 'Alnum'))) {
			if (!$request->isEmpty('operator')) {
				$operator = $request->getByType('operator');
				$searchKey = $request->getByType('search_key', 'Alnum');
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $sourceModule, $searchKey, $operator));
			}
			$customViewModel->set('search_params', App\Condition::validSearchParams($sourceModule, $request->getArray('search_params')));
			$customViewModel->set('entityState', $request->getByType('entityState'));
			if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
				$customViewModel->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
			}
			$queryGenerator = $customViewModel->getRecordsListQuery($excludedIds, $module);
		}
		return $queryGenerator;
	}
}
