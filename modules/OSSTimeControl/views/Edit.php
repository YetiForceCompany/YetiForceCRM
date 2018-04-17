<?php

/**
 * OSSTimeControl DetailView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Edit_View extends Vtiger_Edit_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		\App\Config::setJsEnv('disallowLongerThan24Hours', true);
	}
}
