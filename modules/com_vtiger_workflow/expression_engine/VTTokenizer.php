<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

class VTExpressionTokenizer
{
	public function __construct($expr)
	{
		$tokenTypes = [
			'SPACE' => ['\s+', 'processTokenId'],
			'SYMBOL' => ['[a-zA-Z][\w]*', 'processTokenSymbol'],
			'ESCAPED_SYMBOL' => ['?:`([^`]+)`', 'processTokenSymbol'],
			//"STRING" => array('?:(?:"((?:\\\\"|[^"])+)"|'."'((?:\\\\'|[^'])+)')", 'stripcslashes'),
			//"STRING" => array('?:"((?:\\\\"|[^"])+)"', 'stripcslashes'),
			'STRING' => ["?:'((?:\\\\'|[^'])+)'", 'stripcslashes'],
			'FLOAT' => ['\d+[.]\d+', 'floatval'],
			'INTEGER' => ['\d+', 'intval'],
			'OPERATOR' => ['[+]|[-]|[*]|>=|<=|[<]|[>]|==|\/', 'processTokenSymbol'],
			// NOTE: Any new Operator added should be updated in VTParser.inc::$precedence and operation at VTExpressionEvaluater
			'OPEN_BRACKET' => ['[(]', 'processTokenSymbol'],
			'CLOSE_BRACKET' => ['[)]', 'processTokenSymbol'],
			'COMMA' => ['[,]', 'processTokenSymbol'],
		];
		$tokenReArr = [];
		$tokenNames = [];
		$this->tokenTypes = $tokenTypes;

		foreach ($tokenTypes as $tokenName => $code) {
			[$re] = $code;
			$tokenReArr[] = '(' . $re . ')';
			$tokenNames[] = $tokenName;
		}
		$this->tokenNames = $tokenNames;
		$tokenRe = '/' . implode('|', $tokenReArr) . '/';
		$this->EOF = new VTExpressionToken('EOF');

		$matches = [];
		preg_match_all($tokenRe, App\Purifier::decodeHtml($expr), $matches, PREG_SET_ORDER);
		$this->matches = $matches;
		$this->idx = 0;
	}

	public function nextToken()
	{
		$matches = $this->matches;
		$idx = $this->idx;
		if ($idx == \count($matches)) {
			return $this->EOF;
		}
		$match = $matches[$idx];
		$this->idx = $idx + 1;
		$i = 1;
		while (empty($match[$i])) {
			++$i;
		}
		$tokenName = $this->tokenNames[$i - 1];
		$token = new VTExpressionToken($tokenName);
		if (method_exists($this, $this->tokenTypes[$tokenName][1])) {
			$token->value = \call_user_func([$this, $this->tokenTypes[$tokenName][1]], $match[$i]);
		} else {
			$token->value = $this->tokenTypes[$tokenName][1]($match[$i]);
		}

		return $token;
	}

	public function tests($token)
	{
		$this->processTokenId($token);
		$this->processTokenSymbol($token);
	}

	private function processTokenId($token)
	{
		return $token;
	}

	private function processTokenSymbol($token)
	{
		return new VTEXpressionSymbol($token);
	}
}
