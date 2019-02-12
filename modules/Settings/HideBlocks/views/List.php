<?php

/**
 * Settings HideBlocks List view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_List_View extends Settings_Vtiger_List_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}
}
