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
class Calendar_RepeatEventsDelete_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtnIcon = 'far fa-save';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-save mr-2';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_TITLE_TYPE_DELETE';

	public $showFooter = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		return true;
		//TODO
		if (!(\App\Process::hasEvent('showVisitPurpose')) && !(\App\Process::hasEvent('showSuperUserVisitPurpose'))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/RepeatEventsDelete.tpl', $request->getModule());
	}
}
