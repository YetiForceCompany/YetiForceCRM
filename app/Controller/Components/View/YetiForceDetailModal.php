<?php
/**
 * YetiForce detail view file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\Controller\Components\View;

/**
 * YetiForce detail view class.
 */
class YetiForceDetail extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtn = '';
	/** {@inheritdoc} */
	public $dangerBtn = 'LBL_CLOSE';
	/** {@inheritdoc} */
	public $modalSize = 'modal-xl';

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
	public function getPageTitle(\App\Request $request)
	{
		$version = \App\User::getCurrentUserModel()->isAdmin() ? 'v' . \App\Version::get() : '';
		return 'YetiForceCRM ' . $version . '- The most flexible CRM in the world';
	}

	/** {@inheritdoc} */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('YetiForceDetailModal.tpl', $request->getModule());
	}
}
