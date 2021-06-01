<?php

 /* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Vtiger_CRMEntity extends CRMEntity
{
	public $db; // Used in class functions of CRMEntity
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];

	public function __construct()
	{
		$this->column_fields = vtlib\Deprecated::getColumnFields(static::class);
	}
}
