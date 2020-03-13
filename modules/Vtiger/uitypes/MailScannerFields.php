<?php
/**
 * UIType mail scanner fields field file.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 *  UIType mail scanner fields field class.
 */
class Vtiger_MailScannerFields_UIType extends Vtiger_MultiListFields_UIType
{
	/**
	 * Get pick list values.
	 *
	 * @return string[]
	 */
	public function getPicklistValues(): array
	{
		$return = [];
		$query = (new App\Db\Query())->from('vtiger_field')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['and',
				['<>', 'vtiger_field.presence', 1],
				['uitype' => [4, 13, 319]],
				['<>', 'vtiger_tab.name', 'Users'],
				['vtiger_tab.presence' => 0],
			])
			->orderBy(['vtiger_tab.tabid' => \SORT_ASC, 'vtiger_field.sequence' => \SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$return["{$row['fieldid']}|{$row['name']}|{$row['fieldname']}|{$row['uitype']}"] = App\Language::translate($row['name'], $row['name']) . ' - ' . App\Language::translate($row['fieldlabel'], $row['name']);
		}
		$dataReader->close();
		return $return;
	}
}
