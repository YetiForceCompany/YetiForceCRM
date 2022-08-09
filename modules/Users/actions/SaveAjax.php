<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Users_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('restoreUser');
		$this->exposeMethod('changeAccessKey');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$userModel = \App\User::getCurrentUserModel();
		if (!$userModel->isAdmin() && (int) $userModel->getId() !== $request->getInteger('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);

			return;
		}

		$this->saveRecord($request);
		$fieldModelList = $this->record->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => &$fieldModel) {
			if (!$fieldModel->isViewEnabled()) {
				continue;
			}
			$fieldValue = $displayValue = \App\Purifier::encodeHtml($this->record->get($fieldName));
			if ('currency' !== $fieldModel->getFieldDataType()) {
				$displayValue = $fieldModel->getDisplayValue($fieldValue, $this->record->getId());
			}
			if ('language' === $fieldName) {
				$displayValue = \App\Language::getLanguageLabel($fieldValue);
			}
			if (('currency_decimal_separator' === $fieldName || 'currency_grouping_separator' === $fieldName) && (' ' === $displayValue)) {
				$displayValue = \App\Language::translate('LBL_SPACE', 'Users');
			}
			$prevDisplayValue = false;
			if (false !== ($recordFieldValuePrev = $this->record->getPreviousValue($fieldName))) {
				$prevDisplayValue = $fieldModel->getDisplayValue($recordFieldValuePrev, $this->record->getId(), $this->record);
			}
			$result[$fieldName] = [
				'value' => $fieldValue,
				'display_value' => $displayValue,
				'prev_display_value' => $prevDisplayValue
			];
		}
		$result['_recordLabel'] = $this->record->getName();
		$result['_recordId'] = $this->record->getId();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/** {@inheritdoc} */
	public function getRecordModelFromRequest(App\Request $request)
	{
		parent::getRecordModelFromRequest($request);
		$fieldName = $request->get('field');
		if ('is_admin' === $fieldName && ($fieldModel = $this->record->getModule()->getFieldByName($fieldName))->isWritable()) {
			$fieldModel->getUITypeModel()->setValueFromRequest($request, $this->record, 'value');
		}
		return $this->record;
	}

	/**
	 * To restore a user.
	 *
	 * @param \App\Request Object
	 * @param \App\Request $request
	 */
	public function restoreUser(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');

		$recordModel = Users_Record_Model::getInstanceById($record, $moduleName);
		$recordModel->set('status', 'Active');
		$recordModel->save();

		App\Db::getInstance()->createCommand()->update('vtiger_users', ['deleted' => 0], ['id' => $record])->execute();

		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$listViewUrl = $userModuleModel->getListViewUrl();

		$response = new Vtiger_Response();
		$response->setResult(['message' => \App\Language::translate('LBL_USER_RESTORED_SUCCESSFULLY', $moduleName), 'listViewUrl' => $listViewUrl]);
		$response->emit();
	}

	public function changeAccessKey(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();

		$response = new Vtiger_Response();
		try {
			$recordModel = Users_Record_Model::getInstanceById($recordId, $moduleName);
			$oldAccessKey = $recordModel->get('accesskey');

			$entity = $recordModel->getEntity();
			$entity->createAccessKey();

			require "user_privileges/user_privileges_$recordId.php";
			$newAccessKey = $user_info['accesskey'];
			if ($newAccessKey != $oldAccessKey) {
				$response->setResult(['message' => \App\Language::translate('LBL_ACCESS_KEY_UPDATED_SUCCESSFULLY', $moduleName), 'accessKey' => $newAccessKey]);
			} else {
				$response->setError(\App\Language::translate('LBL_FAILED_TO_UPDATE_ACCESS_KEY', $moduleName));
			}
		} catch (Exception $ex) {
			$response->setError($ex->getMessage());
		}
		$response->emit();
	}
}
