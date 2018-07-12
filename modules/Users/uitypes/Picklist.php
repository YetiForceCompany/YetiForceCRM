<?php
/**
 * UIType Picklist Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_Picklist_UIType extends Vtiger_Picklist_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function isAjaxEditable()
	{
		if ($this->getFieldModel()->getColumnName() === 'login_method') {
			return \App\User::getCurrentUserModel()->isAdmin();
		}
		return parent::isAjaxEditable();
	}
}
