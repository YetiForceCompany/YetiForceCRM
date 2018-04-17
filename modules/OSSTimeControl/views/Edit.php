<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class OSSTimeControl_Edit_View extends Vtiger_Edit_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		\App\Config::setJsEnv('disallowLongerThan24Hours', true);
		parent::preProcess($request);
	}
}
