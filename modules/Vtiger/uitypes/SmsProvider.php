<?php

/**
 * UIType SMS provider field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * UIType SMS provider field class.
 */
class Vtiger_SmsProvider_UIType extends Vtiger_MailServer_UIType
{
	/** {@inheritdoc} */
	public function getPicklistValues()
	{
		return array_map(fn ($provider) => $provider['name'], \App\Integrations\SMSProvider::getAll(\App\Integrations\SMSProvider::STATUS_ACTIVE));
	}
}
