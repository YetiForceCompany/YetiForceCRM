<?php

/**
 * OSSMail CheckConfig view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMail_CheckConfig_View extends Vtiger_Index_View
{

	/**
	 * {@inheritDoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function postProcess(\App\Request $request)
	{
		
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		require_once 'modules/OSSMail/views/CheckConfigCore.php';
	}
}
