<?php

/**
 * String formatting test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Base;

class Z_StringFormatting extends \Tests\Base
{
	public static $separatorDecimal;
	public static $separatorGrouping;
	public static $symbolPlacement;
	public static $patternGrouping;
	public static $decimalNum;
	public static $truncateTrailingZeros;

	/**
	 * Store current user preferences.
	 * @codeCoverageIgnore
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$userModel = \Vtiger_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		static::$separatorDecimal = $userModel->get('currency_decimal_separator');
		static::$separatorGrouping = $userModel->get('currency_grouping_separator');
		static::$symbolPlacement = $userModel->get('currency_symbol_placement');
		static::$patternGrouping = $userModel->get('currency_grouping_pattern');
		static::$decimalNum = $userModel->get('no_of_currency_decimals');
		static::$truncateTrailingZeros = $userModel->get('truncate_trailing_zeros');
	}

	/**
	 * Data provider for the numbers formatting test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerNumbers()
	{
		//Module,Field,UserFormat,DbFormat,decimalSeparator,groupingSeparator,groupingFormat,afterDotPlaces,currencySymbolPlacement,truncateZeros,correct
		return [
			['Accounts', 'employees', '1000000', '1000000', '.', ' ', '123456789', 2, '1.0$', false, true],
			['Accounts', 'employees', '1 000 000', '1000000', '.', ' ', '123,456,789', 2, '1.0$', false, true],
			['Accounts', 'employees', '1,000,000', '1000000', '.', ',', '123,456,789', 2, '1.0$', false, true],
			['Accounts', 'employees', '1,000,000', '1000000', '.', ',', '123,456,789', 2, '$1.0', false, true],
			['Accounts', 'discount', '70,00%', '70.00', ',', ' ', '123,456,789', 2, '1.0$', false, true],
			['Accounts', 'discount', '70,00%', '70.00', ',', ' ', '123,456,789', 2, '$1.0', false, true],
			['Accounts', 'balance', '85 169,40 zł', '85169.40', ',', ' ', '123,456,789', 2, '1.0$', false, true],
			['Accounts', 'balance', 'zł 85 169,40', '85169.40', ',', ' ', '123,456,789', 2, '$1.0', false, true]
		];
	}

	/**
	 * Numbers conversion tests.
	 *
	 * @param string $moduleName        Module name
	 * @param string $fieldName         Field name
	 * @param string $userFormat        Value in user format
	 * @param string $dbFormat          Value in database format
	 * @param string $decimalSeparator  Char used as decimal separator in string
	 * @param string $groupingSeparator Char used to separate groups in string
	 * @param string $groupingPattern   Pattern for grouping
	 * @param int    $afterDot          Number of chars after decimal separator
	 * @param string $symbolPlacement   Currency symbol placement in user format
	 * @param bool   $truncate          Truncate zeros after decimal separator
	 * @param bool   $correct           Test should be successfull
	 * @dataProvider providerNumbers
	 */
	public function testNumbers($moduleName, $fieldName, $userFormat, $dbFormat, $decimalSeparator, $groupingSeparator, $groupingPattern, $afterDot, $symbolPlacement, $truncate, $correct = true)
	{
		$userModel = \Vtiger_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$userModel->set('currency_decimal_separator', $decimalSeparator);
		$userModel->set('currency_grouping_separator', $groupingSeparator);
		$userModel->set('currency_symbol_placement', $symbolPlacement);
		$userModel->set('currency_grouping_pattern', $groupingPattern);
		$userModel->set('no_of_currency_decimals', $afterDot);
		$userModel->set('truncate_trailing_zeros', $truncate ? '1' : '0');
		$userModel->save();
		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set($fieldName, $dbFormat);
		$this->assertSame($userFormat, $recordModel->getDisplayValue($fieldName), 'Display value different than expected' . $dbFormat . ' ' . $recordModel->get($fieldName));
		$this->assertSame($dbFormat, $recordModel->get($fieldName), 'Database value different than expected');
	}

	/**
	 * Restore current user preferences.
	 *
	 * @codeCoverageIgnore
	 * @throws \Exception
	 */
	public static function tearDownAfterClass()
	{
		$userModel = \Vtiger_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$userModel->set('currency_decimal_separator', static::$separatorDecimal);
		$userModel->set('currency_grouping_separator', static::$separatorGrouping);
		$userModel->set('currency_symbol_placement', static::$symbolPlacement);
		$userModel->set('currency_grouping_pattern', static::$patternGrouping);
		$userModel->set('no_of_currency_decimals', static::$decimalNum);
		$userModel->set('truncate_trailing_zeros', static::$truncateTrailingZeros);
		$userModel->save();
		parent::tearDownAfterClass();
	}
}
