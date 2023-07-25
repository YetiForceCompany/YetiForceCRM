<?php
/**
 * String UIType field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * String UIType field class.
 */
class Vtiger_String_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function isResizableColumn(): bool
	{
		return true;
	}

	/** {@inheritdoc} */
	public function validateColumnLength($newColumnLength): bool
	{
		$minColumnLength = 0;
		$maxColumnLength = 255;
		$newColumnLength = (int) $newColumnLength;
		if ($newColumnLength > $minColumnLength && $newColumnLength <= $maxColumnLength) {
			return true;
		}
		return $newColumnLength > $minColumnLength && $newColumnLength <= $maxColumnLength;
	}

	/** {@inheritdoc} */
	public function isColumnLengthIncreased(string $newColumnLength): bool
	{
		$dbColumnStructure = $this->getFieldModel()->getDBColumnType(false);
		return (int) $newColumnLength > $dbColumnStructure['size'];
	}
}
