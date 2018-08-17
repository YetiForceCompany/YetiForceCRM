<?php

/**
 * YetiForce status action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_YetiForce_Status_Action extends Settings_Vtiger_Save_Action
{
	public function process(\App\Request $request)
	{
		if (!$request->has('type')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		switch ($request->get('type')) {
			case 'url':

				break;
			case 'flag':

				break;
		}
	}
}
