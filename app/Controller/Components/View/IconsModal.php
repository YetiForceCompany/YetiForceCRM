<?php
/**
 * Icon modal view class file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller\Components\View;

/**
 * Icon modal view class.
 */
class IconsModal extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_ICONS';
	/** {@inheritdoc} */
	public $modalSize = 'c-modal-xxl';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-icons';
	/** {@inheritdoc} */
	public $successBtn = '';

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('ICONS', \App\Layout\Icon::getIcons());
		$viewer->assign('PAGE_LIMT', 100);
		$viewer->view('IconsModal.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'components.IconsModal',
		]));
	}
}
