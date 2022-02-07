<?php
/**
 * Record numbering basic action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Record numbering basic action class.
 */
class Settings_RecordNumbering_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveModuleCustomNumberingData');
		$this->exposeMethod('saveModuleCustomNumberingAdvanceData');
		$this->exposeMethod('updateRecordsWithSequenceNumber');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$request->getModule(false);
		$sourceModule = $request->getByType('sourceModule', 2);
		if (!$sourceModule) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function save module custom numbering data.
	 *
	 * @param \App\Request $request
	 */
	public function saveModuleCustomNumberingData(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_RecordNumbering_Module_Model::getInstance($request->getByType('sourceModule', 2));
		$moduleModel->set('prefix', $request->getByType('prefix', 'Text'));
		$moduleModel->set('leading_zeros', $request->getByType('leading_zeros', 'Integer'));
		$moduleModel->set('sequenceNumber', $request->getByType('sequenceNumber', 'Integer'));
		$moduleModel->set('postfix', $request->getByType('postfix', 'Text'));
		if (!$request->isEmpty('reset_sequence') && \in_array($request->getByType('reset_sequence'), ['Y', 'M', 'D'])) {
			$moduleModel->set('reset_sequence', $request->getByType('reset_sequence'));
		} else {
			$moduleModel->set('reset_sequence', '');
		}
		$result = $moduleModel->setModuleSequence();
		$response = new Vtiger_Response();
		if ($result['success']) {
			$response->setResult(App\Language::translate('LBL_SUCCESSFULLY_UPDATED', $qualifiedModuleName));
		} else {
			$message = App\Language::translate('LBL_PREFIX_IN_USE', $qualifiedModuleName);
			$response->setError($message);
		}
		$response->emit();
	}

	/**
	 * Function save module advanced numbering data.
	 *
	 * @param App\Request $request
	 */
	public function saveModuleCustomNumberingAdvanceData(App\Request $request)
	{
		$updated = false;
		$sourceModule = $request->getByType('sourceModule', 2);
		$moduleId = \App\Module::getModuleId($sourceModule);
		$picklistValues = $request->getArray('sequenceNumber', 'Integer');
		if (!empty($moduleId) && !empty($picklistValues)) {
			$updated = true;
			foreach ($picklistValues as $picklistKey => $picklistSequence) {
				$sequenceField = \App\Fields\RecordNumber::getInstance($moduleId);
				$sequenceField->updateNumberSequence($picklistSequence, $picklistKey);
			}
		}
		$response = new Vtiger_Response();
		if ($updated) {
			$response->setResult(App\Language::translate('LBL_SUCCESSFULLY_UPDATED', $sourceModule));
		} else {
			$response->setError(false, App\Language::translate('LBL_ERROR_WHILE_UPDATING', $sourceModule));
		}
		$response->emit();
	}

	/**
	 * Function to update record with sequence number.
	 *
	 * @param \App\Request $request
	 */
	public function updateRecordsWithSequenceNumber(App\Request $request)
	{
		$result = App\Fields\RecordNumber::getInstance($request->getByType('sourceModule', 2))->updateRecords();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
