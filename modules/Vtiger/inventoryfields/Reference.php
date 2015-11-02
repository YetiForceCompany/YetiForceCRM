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
		if ($value != 0)
			return Vtiger_Functions::getCRMRecordLabel($value);
		return '';
	}
	
	public function getReferenceModule()
	{
		$params = Zend_Json::decode($this->get('params'));
		return $params['modules'];
	}
}
