<?php

/**
 * Admin privilege basic trait file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\Traits;

/**
 * Admin privilege basic trait.
 */
trait SettingsPermission
{
	/**
	 * Only administrator user can access settings.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Security\AdminAccess::isPermitted($request->getModule())) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		if (!empty(\Config\Security::$askSuperUserAboutVisitPurpose) && !\App\Session::has('showedModalVisitPurpose') && !\App\User::getCurrentUserModel()->isAdmin()) {
			\App\Process::addEvent([
				'name' => 'showSuperUserVisitPurpose',
				'type' => 'modal',
				'url' => 'index.php?module=Users&view=VisitPurpose',
			]);
		}
	}
}
