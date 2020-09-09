<?php

/**
 * Admin privilege basic trait.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	}
}
