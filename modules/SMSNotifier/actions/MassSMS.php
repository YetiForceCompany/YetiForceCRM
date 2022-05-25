<?php
/**
 * Mass sms creation file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mass sms creation class.
 */
class SMSNotifier_MassSMS_Action extends Vtiger_Mass_Action
{
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
	 * Function that saves SMS records.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$queryGenerator = $this->getQuery($request);
		$count = 0;
		$moduleName = $request->getModule();
		$recordModelTemp = \Vtiger_Record_Model::getCleanInstance($moduleName);
		foreach (['message', 'image'] as $fieldName) {
			$fieldModel = $recordModelTemp->getField($fieldName);
			if ($fieldModel && $fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModelTemp);
			}
		}
		$phoneFieldList = $request->getArray('fields', \App\Purifier::ALNUM);

		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$phoneFields = array_filter(array_unique(array_intersect_key($row, array_flip($phoneFieldList))));
			foreach ($phoneFields as $phoneNumber) {
				$recordModel = clone $recordModelTemp;
				$recordModel->set('phone', $phoneNumber)
					->set('related_to', $row['id'])
					->set('image', $recordModelTemp->getField($fieldName)->getUITypeModel()->getDuplicateValue($recordModelTemp))
					->save();
				++$count;
			}
		}
		$dataReader->close();

		$response = new \Vtiger_Response();
		$response->setResult(['message' => \App\Language::translate('LBL_MASS_SEND_SMS_QUEUE_INFO', $request->getModule()), 'count' => $count]);
		$response->emit();
	}

	/** {@inheritdoc} */
	public static function getQuery(App\Request $request)
	{
		$moduleName = $request->getByType('source_module', \App\Purifier::ALNUM);
		$sourceModule = $request->getModule();
		$cvId = $request->isEmpty('viewname') ? '' : $request->getByType('viewname', \App\Purifier::ALNUM);
		$empty = false;

		if ((!$cvId || !empty($cvId) && 'undefined' === $cvId) && 'Users' !== $sourceModule) {
			$empty = true;
			$cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
		}
		$customViewModel = CustomView_Record_Model::getInstanceById((int) $cvId);
		if (!$customViewModel) {
			return false;
		}
		$selectedIds = $request->getArray('selected_ids', 2);
		if ($selectedIds && 'all' !== $selectedIds[0]) {
			$queryGenerator = new App\QueryGenerator($moduleName);
			$queryGenerator->initForCustomViewById($cvId, $empty);
			$queryGenerator->addCondition('id', $selectedIds, 'e');
			$queryGenerator->setStateCondition($request->getByType('entityState'));
		} else {
			if (!$request->isEmpty('operator')) {
				$operator = $request->getByType('operator');
				$searchKey = $request->getByType('search_key', 'Alnum');
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $searchKey, $operator));
			}
			if ($request->getBoolean('isSortActive') && !$request->isEmpty('orderby')) {
				$customViewModel->set('orderby', $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL));
			}
			$customViewModel->set('search_params', App\Condition::validSearchParams($moduleName, $request->getArray('search_params')));
			$customViewModel->set('entityState', $request->getByType('entityState'));
			if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
				$customViewModel->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
			}
			$queryGenerator = $customViewModel->getRecordsListQuery($request->getArray('excluded_ids', 2), $moduleName);
		}

		$fields = $request->getArray('fields', \App\Purifier::ALNUM);
		foreach ($fields as $phoneField) {
			$queryGenerator->addCondition($phoneField, '', 'ny', false);
		}
		$fields[] = 'id';
		$queryGenerator->clearFields()->setFields($fields);
		$queryGenerator->setLimit(\App\Config::module($request->getModule(), 'maxMassSentSMS', 1));
		return $queryGenerator;
	}
}
