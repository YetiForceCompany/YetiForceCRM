<?php

/**
 * Password Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Password_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Field model instance.
	 *
	 * @var \Vtiger_Field_Model
	 */
	protected $fieldModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('record') && !\Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName)->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->fieldModel = Vtiger_Module_Model::getInstance($moduleName)->getFieldByName($request->getByType('field', 2));
		if (!$this->fieldModel || !$this->fieldModel->isViewEnabled() || \App\Encryption::getInstance(\App\Module::getModuleId($moduleName))->isRunning()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
		}
	}

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('generatePwd');
		$this->exposeMethod('validatePwd');
		$this->exposeMethod('getPwd');
	}

	/**
	 * Generate random password.
	 *
	 * @param App\Request $request
	 */
	public function generatePwd(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult(['pwd' => \App\Encryption::generateUserPassword(10)]);
		$response->emit();
	}

	/**
	 * Validate password.
	 *
	 * @param App\Request $request
	 */
	public function validatePwd(App\Request $request)
	{
		$message = $this->fieldModel->getUITypeModel()->checkPwd($request->getRaw('password'));
		$response = new Vtiger_Response();
		$response->setResult([
			'type' => true === $message ? 'pass' : 'error',
			'message' => true === $message ? \App\Language::translate('LBL_PWD_USER_PASSWORD_OK', $request->getModule()) : $message,
		]);
		$response->emit();
	}

	/**
	 * Gets password.
	 *
	 * @param App\Request $request
	 */
	public function getPwd(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = \Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$pwd = $this->fieldModel->getUITypeModel()->getPwd($recordModel->get($this->fieldModel->getName()));

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);
		$eventHandler->trigger('EntityAfterShowHiddenData');
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'text' => $pwd]);
		$response->emit();
	}
}
