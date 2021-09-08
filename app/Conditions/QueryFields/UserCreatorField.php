<?php

namespace App\Conditions\QueryFields;

/**
 * UserCreator Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
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
		return ['<>', $this->getColumnName(), new \yii\db\Expression($this->getTableName() . '.smownerid')];
	}
}
