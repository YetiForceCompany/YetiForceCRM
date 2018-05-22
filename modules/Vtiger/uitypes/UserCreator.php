<?php

/**
 * UIType User Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_UserCreator_UIType extends Vtiger_Reference_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\User::getCurrentUserRealId();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Owner.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getReferenceModule($value)
	{
		return Vtiger_Module_Model::getInstance('Users');
	}
}
