<?php

/**
 * EmailTemplates module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class EmailTemplates_Module_Model extends Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public function isQuickCreateSupported()
	{
		return false;
	}
}
