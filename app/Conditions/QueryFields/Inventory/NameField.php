<?php
/**
 * Name query inventory field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

declare(strict_types=1);

namespace App\Conditions\QueryFields\Inventory;

/**
 * Name query inventory field class.
 */
class NameField extends ReferenceField
{
	/** {@inheritdoc} */
	public function getRelatedTableName(): array
	{
		return $this->getRelatedTables($this->fieldModel->getModules(), $this->fieldModel->getName());
	}
}
