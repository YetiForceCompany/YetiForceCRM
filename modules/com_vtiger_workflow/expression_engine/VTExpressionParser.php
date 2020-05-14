<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

class VTExpressionParser
{
	public function __construct($tokens)
	{
		$this->tokens = $tokens;
		$this->tokenQueue = [];
	}

	public function nextToken()
	{
		if (0 == \count($this->tokenQueue)) {
			return $this->tokens->nextToken();
		}
		return array_shift($this->tokenQueue);
	}

	/**
	 * The function returns a queue of tokens.
	 *
	 * @param int $n
	 *
	 * @return VTExpressionTokenizer
	 */
	public function la($n = 1)
	{
		for ($i = \count($this->tokenQueue); $i < $n; ++$i) {
			$token = $this->tokens->nextToken();
			$this->tokenQueue[] = $token;
		}
		return $this->tokenQueue[$n - 1];
	}

	public function consume($label, $value)
	{
		$token = $this->nextToken();
		if ($token->label != $label || $token->value != $value) {
			echo "Was expecting a $label of value $value got a {$token->label} of {$token->value} instead.";
			throw new \App\Exceptions\AppException("Was expecting a $label of value $value got a {$token->label} of {$token->value} instead.");
		}
	}

	public function consumeSymbol($sym)
	{
		$this->consume('SYMBOL', new VTExpressionSymbol($sym));
	}

	/**
	 * The function returns labels and values.
	 *
	 * @param \VTExpressionToken $token
	 * @param string             $label
	 * @param string             $value
	 *
	 * @return type
	 */
	public function check(VTExpressionToken $token, $label, $value)
	{
		return $token->label == $label && $token->value == $value;
	}

	public function checkSymbol($token, $sym)
	{
		return $this->check($token, 'SYMBOL', new VTExpressionSymbol($sym));
	}

	public function atom()
	{
		$token = $this->nextToken();
		switch ($token->label) {
			case 'STRING':
				return $token->value;
			case 'INTEGER':
				return $token->value;
			case 'FLOAT':
				return $token->value;
			case 'SYMBOL':
				return $token->value;
			case 'OPEN_BRACKET':
				$val = $this->expression();
				$close = $this->nextToken();
				if ('CLOSE_BRACKET' !== $close->label) {
					throw new \App\Exceptions\AppException('Was expecting a close bracket');
				}

				return $val;
			default:
				throw new \App\Exceptions\AppException('Token not found: ' . $token->label);
		}
	}

	public function ifCondition()
	{
		$this->consumeSymbol('if');
		$cond = $this->expression();
		$this->consumeSymbol('then');
		$ifTrue = $this->expression();
		$this->consumeSymbol('else');
		if ($this->checkSymbol($this->la(), 'if')) {
			$ifFalse = $this->ifCondition();
		} else {
			$ifFalse = $this->expression();
			$this->consumeSymbol('end');
		}
		return new VTExpressionTreeNode([new VTExpressionSymbol('if'), $cond, $ifTrue, $ifFalse]);
	}

	public function expression()
	{
		$la1 = $this->la(1);
		$la2 = $this->la(2);
		if ($this->checkSymbol($la1, 'if')) {
			return $this->ifCondition();
		}
		if ('SYMBOL' == $la1->label && 'OPEN_BRACKET' == $la2->label) {
			$arr = [$this->nextToken()->value];
			$this->nextToken();
			if ('CLOSE_BRACKET' != $this->la()->label) {
				$arr[] = $this->expression();
				$comma = $this->nextToken();
				while ('COMMA' == $comma->label) {
					$arr[] = $this->expression();
					$comma = $this->nextToken();
				}
				if ('CLOSE_BRACKET' != $comma->label) {
					throw new \App\Exceptions\AppException('Was expecting a closing bracket');
				}
			} else {
				$this->consume('CLOSE_BRACKET', new Symbol(')'));
			}

			return new VTExpressionTreeNode($arr);
		}
		return $this->binOp();
	}

	public $precedence = [
		['*', '/'],
		['+', '-'],
		['and', 'or'],
		['==', '>=', '<=', '>', '<'],
	];

	public function binOp()
	{
		return $this->binOpPrec(\count($this->precedence) - 1);
	}

	private function binOpPrec($prec)
	{
		if ($prec >= 0) {
			$lhs = $this->binOpPrec($prec - 1);
			$la = $this->la();
			if ('OPERATOR' == $la->label && \in_array($la->value->value, $this->precedence[$prec])) {
				$operator = $this->nextToken()->value;
				$rhs = $this->binOpPrec($prec);

				return new VTExpressionTreeNode([$operator, $lhs, $rhs]);
			}
			return $lhs;
		}
		return $this->unaryOp();
	}

	public function unaryOp()
	{
		$la = $this->la();
		if ('OPERATOR' == $la->label && \in_array($la->value->value, ['+', '-'])) {
			$this->nextToken();
			$operator = $la->value;
			$operand = $this->unaryOp();

			return new VTExpressionTreeNode([$operator, $operand]);
		}
		return $this->atom();
	}
}
