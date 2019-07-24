<?php

/**
 * String formatting test class file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Base;

/**
 * String formatting test class.
 */
class Z_StringFormatting extends \Tests\Base
{
	/**
	 * @var string Decimal numbers separator
	 */
	public static $separatorDecimal;
	/**
	 * @var string Numbers grouping separator
	 */
	public static $separatorGrouping;
	/**
	 * @var string Currency symbol placement
	 */
	public static $symbolPlacement;
	/**
	 * @var string Numbers grouping pattern
	 */
	public static $patternGrouping;
	/**
	 * @var int Decimal places count
	 */
	public static $decimalNum;
	/**
	 * @var bool Truncate zeros in decimal numbers
	 */
	public static $truncateTrailingZeros;
	/**
	 * @var array Possible combinations cache
	 */
	public static $combinations = [];

	/**
	 * Store current user preferences.
	 *
	 * @codeCoverageIgnore
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$userModel = \App\User::getCurrentUserModel();
		static::$separatorDecimal = $userModel->getDetail('currency_decimal_separator');
		static::$separatorGrouping = $userModel->getDetail('currency_grouping_separator');
		static::$symbolPlacement = $userModel->getDetail('currency_symbol_placement');
		static::$patternGrouping = $userModel->getDetail('currency_grouping_pattern');
		static::$decimalNum = $userModel->getDetail('no_of_currency_decimals');
		static::$truncateTrailingZeros = $userModel->getDetail('truncate_trailing_zeros');
	}

	/**
	 * Data provider for the numbers formatting test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerNumbers()
	{
		$combinations = [];
		foreach (
			[
				'integer',
				'double',
			] as $type) {
			$method = 'append' . \ucfirst($type);
			if (\method_exists($this, $method)) {
				$this->{$method}($combinations);
			} else {
				$this->fail('Unsupported field type: ' . \ucfirst($type));
			}
		}
		return $combinations;
	}

	/**
	 * Generate list of possible combinations.
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function getCombinations()
	{
		if (!static::$combinations) {
			$query = (new \App\Db\Query())->select(
				[
					'decimal_separator' => 'vtiger_currency_decimal_separator.currency_decimal_separator',
					'grouping_pattern' => 'vtiger_currency_grouping_pattern.currency_grouping_pattern',
					'grouping_separator' => 'vtiger_currency_grouping_separator.currency_grouping_separator',
					'symbol_placement' => 'vtiger_currency_symbol_placement.currency_symbol_placement',
					'decimals' => 'vtiger_no_of_currency_decimals.no_of_currency_decimals'
				]
			)->from('vtiger_currency_decimal_separator')->join('cross join', 'vtiger_currency_grouping_pattern')->join('cross join', 'vtiger_currency_grouping_separator')->join('cross join', 'vtiger_currency_symbol_placement')->join('cross join', 'vtiger_no_of_currency_decimals')->createCommand()->query();
			while ($combination = $query->read()) {
				if ($combination['grouping_separator'] !== $combination['decimal_separator']) {
					static::$combinations[] = $combination;
				}
			}
		}
		return static::$combinations;
	}

	/**
	 * Append integer validation data sets to test combinations.
	 *
	 * @param $combinations
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function appendInteger(&$combinations)
	{
		$dbFormat = '123456789';
		$fieldData = (new \App\Db\Query())->from('vtiger_field')->where(['uitype' => 7])->one();
		foreach ($this->getCombinations() as $combination) {
			$usrFormatTruncated = $usrFormat = \str_replace(',', $combination['grouping_separator'], $combination['grouping_pattern']);
			$combinations[] = [
				\App\Module::getModuleName($fieldData['tabid']),
				$fieldData['fieldname'],
				$usrFormatTruncated,
				$dbFormat,
				$combination['decimal_separator'],
				$combination['grouping_separator'],
				$combination['grouping_pattern'],
				$combination['decimals'],
				$combination['symbol_placement'],
				true,
				true
			];
			$combinations[] = [
				\App\Module::getModuleName($fieldData['tabid']),
				$fieldData['fieldname'],
				$usrFormat,
				$dbFormat,
				$combination['decimal_separator'],
				$combination['grouping_separator'],
				$combination['grouping_pattern'],
				$combination['decimals'],
				$combination['symbol_placement'],
				false,
				true
			];
		}
		return $combinations;
	}

	/**
	 * Append double validation data sets to test combinations.
	 *
	 * @param $combinations
	 *
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function appendDouble(&$combinations)
	{
		$int = '123456789';
		$fieldData = (new \App\Db\Query())->from('vtiger_field')->where(['uitype' => 7, 'typeofdata' => 'NN~O'])->one();
		foreach ($this->getCombinations() as $combination) {
			$decimals = \substr(12312, 0, $combination['decimals']);
			$dbFormat = $int . '.' . 12312;
			$usrFormatTruncated = $usrFormat = \str_replace(',', $combination['grouping_separator'], $combination['grouping_pattern']);
			if ($decimals) {
				$usrFormat .= $combination['decimal_separator'] . $decimals;
				$usrFormatTruncated .= $combination['decimal_separator'] . rtrim($decimals, '0');
			}
			$combinations[] = [
				\App\Module::getModuleName($fieldData['tabid']),
				$fieldData['fieldname'],
				$usrFormat,
				$dbFormat,
				$combination['decimal_separator'],
				$combination['grouping_separator'],
				$combination['grouping_pattern'],
				$combination['decimals'],
				$combination['symbol_placement'],
				true,
				true
			];
			$combinations[] = [
				\App\Module::getModuleName($fieldData['tabid']),
				$fieldData['fieldname'],
				$usrFormatTruncated,
				$dbFormat,
				$combination['decimal_separator'],
				$combination['grouping_separator'],
				$combination['grouping_pattern'],
				$combination['decimals'],
				$combination['symbol_placement'],
				false,
				true
			];
			$decimals = \substr(10000, 0, $combination['decimals']);
			$dbFormat = $int . '.' . 10000;
			$usrFormatTruncated = $usrFormat = \str_replace(',', $combination['grouping_separator'], $combination['grouping_pattern']);
			if ($decimals) {
				$usrFormat .= $combination['decimal_separator'] . $decimals;
				$usrFormatTruncated .= $combination['decimal_separator'] . rtrim($decimals, '0');
			}
			$combinations[] = [
				\App\Module::getModuleName($fieldData['tabid']),
				$fieldData['fieldname'],
				$usrFormatTruncated,
				$dbFormat,
				$combination['decimal_separator'],
				$combination['grouping_separator'],
				$combination['grouping_pattern'],
				$combination['decimals'],
				$combination['symbol_placement'],
				true,
				true
			];
			$combinations[] = [
				\App\Module::getModuleName($fieldData['tabid']),
				$fieldData['fieldname'],
				$usrFormat,
				$dbFormat,
				$combination['decimal_separator'],
				$combination['grouping_separator'],
				$combination['grouping_pattern'],
				$combination['decimals'],
				$combination['symbol_placement'],
				false,
				true
			];
		}
		return $combinations;
	}

	/**
	 * Numbers conversion tests.
	 *
	 * @dataProvider providerNumbers
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

		$userModel2 = \App\User::getCurrentUserModel();
		$this->assertSame($userModel2->getDetail('currency_grouping_separator'), $groupingSeparator);

		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set($fieldName, $dbFormat);
		$this->assertSame($userFormat, $recordModel->getDisplayValue($fieldName), 'Display value different than expected' . $dbFormat . ' ' . $recordModel->get($fieldName));
		$this->assertSame($dbFormat, $recordModel->get($fieldName), 'Database value different than expected');
	}

	/**
	 * Restore current user preferences.
	 *
	 * @codeCoverageIgnore
	 *
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
