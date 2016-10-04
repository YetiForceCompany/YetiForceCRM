<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_TaxRecord_Model extends Vtiger_Base_Model
{

	const PRODUCT_AND_SERVICE_TAX = 0;

	public function __construct($values = array())
	{
		parent::__construct($values);
		$this->unMarkDeleted();
	}

	private $type;

	public function getId()
	{
		return $this->get('taxid');
	}

	public function getName()
	{
		return $this->get('taxlabel');
	}

	public function getTax()
	{
		return $this->get('percentage');
	}

	public function isDeleted()
	{
		return $this->get('deleted') == 0 ? false : true;
	}

	public function markDeleted()
	{
		return $this->set('deleted', '1');
	}

	public function unMarkDeleted()
	{
		return $this->set('deleted', '0');
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function isProductTax()
	{
		return ($this->getType() == self::PRODUCT_AND_SERVICE_TAX) ? true : false;
	}

	public function getCreateTaxUrl()
	{
		return '?module=Vtiger&parent=Settings&view=TaxAjax';
	}

	public function getEditTaxUrl()
	{
		return '?module=Vtiger&parent=Settings&view=TaxAjax&type=' . $this->getType() . '&taxid=' . $this->getId();
	}

	private function getTableNameFromType()
	{
		$tablename = 'vtiger_inventorytaxinfo';
		return $tablename;
	}

	public function save()
	{
		$db = PearDatabase::getInstance();

		$tablename = $this->getTableNameFromType();

		$taxId = $this->getId();

		if (!empty($taxId)) {
			$deleted = 0;
			if ($this->isDeleted()) {
				$deleted = 1;
			}
			$db->update($tablename, [
				'taxlabel' => $this->getName(),
				'percentage' => $this->get('percentage'),
				'deleted' => $deleted
				], 'taxid = ?', [$taxId]);
		} else {
			$taxId = $this->addTax();
		}
		return $taxId;
	}

	/** 	Function used to add the tax type which will do database alterations
	 * 	@param string $taxlabel - tax label name to be added
	 * 	@param string $taxvalue - tax value to be added
	 *      @param string $sh - sh or empty , if sh passed then the tax will be added in shipping and handling related table
	 *      @return void
	 */
	public function addTax()
	{
		$adb = PearDatabase::getInstance();

		$tableName = $this->getTableNameFromType();
		$taxid = $adb->getUniqueID($tableName);
		$taxLabel = $this->getName();
		$percentage = $this->get('percentage');

		//if the tax is not available then add this tax.
		//Add this tax as a column in related table	
		$taxname = "tax" . $taxid;
		$query = "ALTER TABLE vtiger_inventoryproductrel ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
		$res = $adb->pquery($query, array());

		vimport('~include/utils/utils.php');

		if ($this->isProductTax()) {

			$inventoryModules = getInventoryModules();
			foreach ($inventoryModules as $moduleName) {
				$moduleInstance = vtlib\Module::getInstance($moduleName);
				$blockInstance = vtlib\Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);
				$field = new vtlib\Field();

				$field->name = $taxname;
				$field->label = $taxLabel;
				$field->column = $taxname;
				$field->table = 'vtiger_inventoryproductrel';
				$field->uitype = '83';
				$field->typeofdata = 'V~O';
				$field->readonly = '0';
				$field->displaytype = '5';
				$field->masseditable = '0';

				$blockInstance->addField($field);
			}
		}

		//if the tax is added as a column then we should add this tax in the list of taxes
		if ($res) {
			$query = 'INSERT INTO ' . $tableName . ' values(?,?,?,?,?)';
			$params = array($taxid, $taxname, $taxLabel, $percentage, 0);
			$adb->pquery($query, $params);
			return $taxid;
		}
		throw new Error('Error occurred while adding tax');
	}

	public static function getProductTaxes()
	{
		vimport('include/utils/InventoryUtils.php');
		$taxes = getAllTaxes();
		$recordList = array();
		foreach ($taxes as $taxInfo) {
			$taxRecord = new self();
			$taxRecord->setData($taxInfo)->setType(self::PRODUCT_AND_SERVICE_TAX);
			$recordList[] = $taxRecord;
		}
		return $recordList;
	}

	public static function getInstanceById($id, $type = self::PRODUCT_AND_SERVICE_TAX)
	{
		$db = PearDatabase::getInstance();
		$tablename = 'vtiger_inventorytaxinfo';

		$query = sprintf('SELECT * FROM %s WHERE taxid = ?', $tablename);
		$result = $db->pquery($query, array($id));
		$taxRecordModel = new self();
		if ($db->num_rows($result) > 0) {
			$row = $db->query_result_rowdata($result, 0);
			$taxRecordModel->setData($row)->setType($type);
		}
		return $taxRecordModel;
	}

	public static function checkDuplicate($label, $excludedIds = array(), $type = self::PRODUCT_AND_SERVICE_TAX)
	{
		$db = PearDatabase::getInstance();

		if (!is_array($excludedIds)) {
			if (!empty($excludedIds)) {
				$excludedIds = array($excludedIds);
			} else {
				$excludedIds = array();
			}
		}
		$tablename = 'vtiger_inventorytaxinfo';

		$query = sprintf('SELECT 1 FROM %s WHERE taxlabel = ?', $tablename);
		$params = [$label];

		if (!empty($excludedIds)) {
			$query .= " && taxid NOT IN (" . generateQuestionMarks($excludedIds) . ")";
			$params = array_merge($params, $excludedIds);
		}
		$result = $db->pquery($query, $params);
		return ($db->num_rows($result) > 0) ? true : false;
	}
}
