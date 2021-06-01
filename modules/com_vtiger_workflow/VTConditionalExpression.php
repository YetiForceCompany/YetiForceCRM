<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTConditionalParser.php');
Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTParseFailed.php');

class VTConditionalExpression
{
	public function __construct($expression)
	{
		$parser = new VTConditionalParser($expression);
		$this->expTree = $parser->parse();
	}

	public function evaluate($data)
	{
		$this->env = $data;

		return $this->evalGate($this->expTree);
	}

	private function evalGate($tree)
	{
		if (\in_array($tree[0], ['and', 'or'])) {
			switch ($tree[0]) {
				case 'and':
					return $this->evalGate($tree[1]) && $this->evalGate($tree[2]);
				case 'or':
					return $this->evalGate($tree[1]) || $this->evalGate($tree[2]);
				default:
					break;
			}
		} else {
			return $this->evalCondition($tree);
		}
	}

	private function evalCondition($tree)
	{
		if ('=' === $tree[0]) {
			return (int) $this->getVal($tree[1]) == (int) $this->getVal($tree[2]);
		}
	}

	private function getVal($node)
	{
		[$valueType, $value] = $node;
		switch ($valueType) {
			case 'sym':
				return $this->env[$value];
			case 'num':
				return $value;
			default:
				break;
		}
	}
}
