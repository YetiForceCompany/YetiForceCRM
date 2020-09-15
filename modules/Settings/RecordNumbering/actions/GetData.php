<?php
/**
 * Record numbering action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Record numbering action class.
 */
class Settings_RecordNumbering_GetData_Action extends Settings_Vtiger_Index_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getModuleCustomNumberingData');
	}

	/**
	 * {@inheritdoc}
	 */
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
	 * Function to get Module custom numbering data.
	 *
	 * @param \App\Request $request
	 */
	public function getModuleCustomNumberingData(App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', 2);
		$instance = \App\Fields\RecordNumber::getInstance($sourceModule);
		$moduleData = $instance->getData();
		if (empty($moduleData['reset_sequence'])) {
			$moduleData['reset_sequence'] = 'n';
		}
		$picklistsModels = Vtiger_Module_Model::getInstance($sourceModule)->getFieldsByType(['picklist']);
		foreach ($picklistsModels as $fieldModel) {
			if (\App\Fields\Picklist::prefixExist($fieldModel->getFieldName())) {
				$moduleData['picklists'][$fieldModel->getName()] = App\Language::translate($fieldModel->getFieldLabel(), $sourceModule);
			}
		}
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($moduleData);
		$response->emit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
