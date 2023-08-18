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
	protected $isResizableColumn = true;

	/** {@inheritdoc} */
	public function changeMaximumLength(int $minimumLength, int $maximumLength): void
	{
		if ($this->isResizableColumn() && $this->validateMaximumLength($minimumLength, $maximumLength)) {
			$fieldInstance = $this->getFieldModel();
			if ($this->isColumnLengthIncreased($maximumLength)) {
				$dbColumnStructure = $fieldInstance->getDBColumnType(false);
				$columnType = $dbColumnStructure['type'];
				$db = App\Db::getInstance();
				$db->createCommand()->alterColumn($fieldInstance->getTableName(), $fieldInstance->getColumnName(), "{$columnType}({$maximumLength})")->execute();
			}
			$this->getFieldModel()->set('maximumlength', $maximumLength);
		}
	}

	/**
	 * Method is responsible for comparing the current length of a column with its previous state to ascertain whether there have been any changes in the column length.
	 *
	 * @param string $newColumnLength
	 *
	 * @return bool
	 */
	public function isColumnLengthIncreased(string $newColumnLength): bool
	{
		$dbColumnStructure = $this->getFieldModel()->getDBColumnType(false);
		return (int) $newColumnLength > $dbColumnStructure['size'];
	}
}
