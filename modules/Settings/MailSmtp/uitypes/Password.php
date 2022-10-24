<?php
/**
 * UIType Password field file.
 *
 * @package   Settings.UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Password Field Class.
 */
class Settings_MailSmtp_Password_UIType extends Vtiger_Password_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		$encryptInstance = \App\Encryption::getInstance();
		if ($encryptInstance->isActive()) {
			$value = $encryptInstance->encrypt($value);
		}

		return $value;
	}
}
