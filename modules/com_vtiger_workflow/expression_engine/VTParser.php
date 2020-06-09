<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/expression_engine/VTExpressionSymbol.php');
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/expression_engine/VTExpressionParser.php');

class VTExpressionTreeNode
{
	public function __construct($arr)
	{
		$this->arr = $arr;
	}

	public function getParams()
	{
		$arr = $this->arr;

		return \array_slice($arr, 1, \count($arr) - 1);
	}

	public function getName()
	{
		return $this->arr[0];
	}
}
