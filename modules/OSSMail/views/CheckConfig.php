<?php

/**
 * OSSMail CheckConfig view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_CheckConfig_View extends Vtiger_Index_View
{

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function postProcess(\App\Request $request)
	{
		
	}

	public function process(\App\Request $request)
	{
		require_once 'modules/OSSMail/views/CheckConfigCore.php';
	}
}
