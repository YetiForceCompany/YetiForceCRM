<?php

/**
 * Inventory Reference Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Reference_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Reference';
	protected $defaultLabel = 'LBL_REFERENCE';
	protected $columnName = 'ref';
	protected $dbType = 'int(19)';
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
		$metaData = Vtiger_Functions::getCRMRecordMetadata($value);
		$linkValue = '<a class="moduleColor_' . $metaData['setype'] . '" href="index.php?module=' . $metaData['setype'] . '&view=Detail&record=' . $value . '" title="' . vtranslate($metaData['setype'], $metaData['setype']) . '">' . $metaData['label'] . '</a>';
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
		$value = Vtiger_Functions::getCRMRecordLabel($value, $default = '');
		return $value;
	}

	public function getReferenceModules()
	{
		$params = Zend_Json::decode($this->get('params'));
		return $params['modules'];
	}

	public function getReferenceModule($record)
	{
		if (!empty($record)) {
			$metadata = Vtiger_Functions::getCRMRecordMetadata($record);
			return $metadata['setype'];
		}
		return '';
	}
}
