<?php

/**
 * List view file for MeetingServices module.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * List view class for MeetingServices.
 */
class Settings_MeetingServices_List_View extends Settings_Vtiger_List_View
{
	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard'
		]));
	}
}
