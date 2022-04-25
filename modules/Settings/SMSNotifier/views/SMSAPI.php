<?php
/**
 * Edit View Class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Edit View Class.
 */
class Settings_SMSNotifier_SMSAPI_View extends Settings_SMSNotifier_Edit_View
{
	/** {@inheritdoc} */
	public function getTemplateName(): string
	{
		return 'SMSAPI/Edit.tpl';
	}
}
