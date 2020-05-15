<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * This is a simple parser for conditional expressions used to trigger workflow actions.
 */
class VTConditionalParser
{
	public function __construct($expr)
	{
		$this->tokens = $this->getTokens($expr);
		$this->pos = 0;
	}

	private function getTokens($expression)
	{
		preg_match_all('/and|or|\\d+|=|\\w+|\\(|\\)/', $expression, $matches, PREG_SET_ORDER);
		$tokens = [];
		foreach ($matches as $arr) {
			$tokenVal = $arr[0];
			if (\in_array($tokenVal, ['and', 'or', '=', '(', ')'])) {
				$tokenType = 'op';
			} elseif (is_numeric($tokenVal)) {
				$tokenType = 'num';
			} else {
				$tokenType = 'sym';
			}
			$tokens[] = [$tokenType, $tokenVal];
		}
		return $tokens;
	}

	public function parse()
	{
		$op = [
			'and' => ['op', 'and'],
			'or' => ['op', 'or'],
			'=' => ['op', '='],
			'(' => ['op', '('],
			')' => ['op', ')'], ];

		if ($this->peek() == $op['(']) {
			$this->nextToken();
			$left = $this->parse();
			if ($this->nextToken() != $op[')']) {
				throw new VTParseFailed();
			}
		} else {
			$left = $this->cond();
		}
		if (\count($this->tokens) > $this->pos && \in_array($this->peek(), [$op['and'], $op['or']])) {
			$nt = $this->nextToken();

			return [$nt[1], $left, $this->parse()];
		}
		return $left;
	}

	private function cond()
	{
		$left = $this->nextToken();
		$operator = $this->nextToken();
		$right = $this->nextToken();

		return [$operator[1], $left, $right];
	}

	private function peek()
	{
		return $this->tokens[$this->pos];
	}

	private function nextToken()
	{
		++$this->pos;

		return $this->tokens[$this->pos - 1];
	}
}
