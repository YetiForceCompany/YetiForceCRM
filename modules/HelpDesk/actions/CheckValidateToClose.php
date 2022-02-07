<?php
/**
 * Checking Close Validation Action Class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
class HelpDesk_CheckValidateToClose_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!$this->recordModel->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult($this->recordModel->checkValidateToClose($request->getByType('status', 'Text')));
		$response->emit();
	}
}
