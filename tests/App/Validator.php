<?php
/**
 * The file contains: Validator test class.
 *
 * @package Tests
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\App;

/**
 * Validator test class.
 */
class Validator extends \Tests\Base
{
	/**
	 * @dataProvider dataUrlNoProtocolRequired
	 *
	 * @param mixed $expectedValue
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function testUrlNoProtocolRequired(bool $expectedValue, $value)
	{
		$this->assertSame($expectedValue, \App\Validator::urlDomain($value));
	}

	/**
	 * Data provider for testUrlNoProtocolRequired.
	 *
	 * @return array
	 */
	public function dataUrlNoProtocolRequired(): array
	{
		return [
			[true, 'ssl://yf.com.pl'],
			[true, 'google.pl'],
			[true, 'http://google.pl'],
			[false, 'http://a-.bc.com'],
			[false, 'https://1243yfcom%.pl'],
			[false, '.'],
			[false, '#'],
			[false, 'https://yetiforce*com/pl/'],
			[false, 'https://yeti force.com/pl/'],
			[true, 'ftp://yetiforce.com'],
			[false, 'test@com.pl'],
			[true, 'tel://600500100'],
			[true, 'mailto://info@yetiforce.com'],
			[false, 'http*://yetiforce.com'],
			[true, 'http://yetiforce.com:2160/'],
			[false, ' http://yetiforce.com/'],
			[false, 'javascript:alert(1)'],
			[true, 'http://www.müller.de'],
			[true, 'http://элтранс.рф'],
		];
	}

	/**
	 * Provide test data for testFloatIsEqual function.
	 *
	 * @return array
	 */
	public function floatIsEqualProvider()
	{
		return [
			[3.5768, 3.58, 3, false],
			[0.314111, 0.3199, 3, false],
			[0.314000, 0.314, 6, true],
			[0.314001, 0.314, 6, false],
			[0.314001, 0.314, 5, true],
		];
	}

	/**
	 * Testing process function.
	 *
	 * @dataProvider floatIsEqualProvider
	 *
	 * @param float $value1
	 * @param float $value2
	 * @param int   $precision
	 * @param bool  $result
	 */
	public function testFloatIsEqual(float $value1, float $value2, int $precision, bool $result)
	{
		$this->assertSame($result, \App\Validator::floatIsEqual($value1, $value2, $precision), 'Expected ' . $result);
	}
}
