<?php
/**
 * UIType mail scanner fields field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$query = (new App\Db\Query())->select(['vtiger_field.fieldid', 'vtiger_tab.name', 'vtiger_field.fieldname', 'vtiger_field.fieldlabel', 'vtiger_field.uitype'])
			->from('vtiger_field')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->leftJoin('vtiger_relatedlists', 'vtiger_tab.tabid = vtiger_relatedlists.tabid')
			->where(['and',
				['<>', 'vtiger_field.presence', 1],
				['uitype' => [4, 13, 319]],
				['<>', 'vtiger_tab.name', 'Users'],
				['vtiger_tab.presence' => 0],
				['vtiger_relatedlists.related_tabid' => \App\Module::getModuleId('OSSMailView')],
			])
			->orderBy(['vtiger_tab.tabid' => \SORT_ASC, 'vtiger_field.uitype' => \SORT_DESC, 'vtiger_field.sequence' => \SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$return["{$row['fieldid']}|{$row['name']}|{$row['fieldname']}|{$row['uitype']}"] = App\Language::translate($row['name'], $row['name'], false, false) . ' - ' . App\Language::translate($row['fieldlabel'], $row['name'], false, false);
		}
		$dataReader->close();
		return $return;
	}
}
