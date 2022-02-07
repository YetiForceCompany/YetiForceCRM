<?php

namespace App\Conditions\RecordFields;

/**
 * User creator condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class UserCreatorField extends OwnerField
{
	/**
	 * Not created by owner.
	 *
	 * @return array
	 */
	public function operatorNco()
	{
		return $this->getValue() != $this->recordModel->get('assigned_user_id');
	}
}
