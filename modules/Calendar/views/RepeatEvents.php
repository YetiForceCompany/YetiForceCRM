<?php
/**
 * Modal window for repeat events purpose.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Modal window for repeat events purpose class.
 */
class Calendar_RepeatEvents_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtnIcon = 'far fa-save';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-save mr-2';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_TITLE_TYPE_SAVING';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public $autoRegisterEvents = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), 'Calendar');
		if (!$recordModel->isEditable()) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/RepeatEvents.tpl', $request->getModule());
	}
}
