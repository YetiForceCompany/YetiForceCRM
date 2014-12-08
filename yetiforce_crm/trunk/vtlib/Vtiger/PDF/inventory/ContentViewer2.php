<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/ContentViewer.php';

class Vtiger_PDF_InventoryTaxGroupContentViewer extends Vtiger_PDF_InventoryContentViewer {

	function __construct() {
		// NOTE: General A4 PDF width ~ 189 (excluding margins on either side)
			
		$this->cells = array( // Name => Width
			'Code'		=> 30,
			'Name'		=> 65,
			'Quantity'	=> 20,
			'Price'		=> 25,
			'Discount'	=> 20,
			'Total'		=> 30
		);
	}
	
}
