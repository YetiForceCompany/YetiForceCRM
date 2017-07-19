<?php

/**
 * Inventory Reference Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Reference_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Reference';
	protected $defaultLabel = 'LBL_REFERENCE';
	protected $columnName = 'ref';
	protected $dbType = 'int';
	protected $params = ['modules'];

	/**
	 * Getting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		if ($value == 0) {
			return '';
		}
		$metaData = vtlib\Functions::getCRMRecordMetadata($value);
		$linkValue = '<a class="moduleColor_' . $metaData['setype'] . '" href="index.php?module=' . $metaData['setype'] . '&view=Detail&record=' . $value . '" title="' . \App\Language::translate($metaData['setype'], $metaData['setype']) . '">' . \App\Record::getLabel($value) . '</a>';
		return $linkValue;
	}

	/**
	 * Getting value to display
	 * @param type $value
	 * @return string
	 */
	public function getEditValue($value)
	{
		if (empty($value)) {
			return '';
		}
		$value = vtlib\Functions::getCRMRecordLabel($value, $default = '');
		return $value;
	}

	public function getReferenceModules()
	{
		$params = \App\Json::decode($this->get('params'));
		return $params['modules'];
	}

	public function getReferenceModule($record)
	{
		if (!empty($record)) {
			$metadata = vtlib\Functions::getCRMRecordMetadata($record);
			return $metadata['setype'];
		}
		return '';
	}
}
