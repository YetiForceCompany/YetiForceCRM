<?php
/**
 * UIType Password field file.
 *
 * @package   Settings.UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType Password Field Class.
 */
class Settings_WebserviceUsers_Password_UIType extends Vtiger_Password_UIType
{
	/** {@inheritdoc} */
	public function getActionsUrl(): array
	{
		$fieldModel = $this->getFieldModel();
		return [
			'generate' => "index.php?module=WebserviceUsers&parent=Settings&action=Password&mode=generatePwd&field={$fieldModel->getName()}&typeApi={$fieldModel->get('typeApi')}",
			'validate' => "index.php?module=WebserviceUsers&parent=Settings&action=Password&mode=validatePwd&field={$fieldModel->getName()}&typeApi={$fieldModel->get('typeApi')}",
		];
	}
}
