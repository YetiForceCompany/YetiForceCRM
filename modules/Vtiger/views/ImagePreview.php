<?php
/**
 * Display image preview file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * Display image preview class.
 */
class Vtiger_ImagePreview_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-images';

	/** {@inheritdoc} */
	public $pageTitle = 'LBL_IMAGE_PREVIEW';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/ImagePreview.tpl', $request->getModule());
	}
}
