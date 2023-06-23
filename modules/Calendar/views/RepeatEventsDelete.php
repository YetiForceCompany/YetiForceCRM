<?php
/**
 * Modal window for delete repeat events.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Modal window for delete repeat events class.
 */
class Calendar_RepeatEventsDelete_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtnIcon = 'far fa-save';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-save mr-2';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_TITLE_TYPE_DELETE';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** @var Vtiger_Record_Model */
	private $recordModel;

	/** {@inheritdoc} */
	public $autoRegisterEvents = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), 'Calendar');
		if (!$this->recordModel->privilegeToDelete() || !$this->recordModel->privilegeToMoveToTrash()) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('DELETE_URL', $this->recordModel->getDeleteUrl());
		$viewer->view('Modals/RepeatEventsDelete.tpl', $request->getModule());
	}
}
