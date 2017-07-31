<?php

/**
 * Settings HideBlocks List view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_HideBlocks_List_View extends Settings_Vtiger_List_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}
}
