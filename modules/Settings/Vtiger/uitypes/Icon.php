<?php
/**
 * UIType Icon Field File.
 *
 * @package   Settings.UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Icon Field Class.
 */
class Settings_Vtiger_Icon_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Icon.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return [];
	}
}
