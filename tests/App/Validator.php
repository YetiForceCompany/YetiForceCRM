<?php
/**
 * The file contains: Validator test class.
 *
 * @package Tests
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Tests\App;

/**
 * Validator test class.
 */
class Validator extends \Tests\Base
{
	/**
	 * @dataProvider dataValidator
	 *
	 * @param string $fn
	 * @param bool   $expected
	 * @param string $value
	 *
	 * @return void
	 */
	public function testValidator(string $fn, bool $expected, $value)
	{
		$this->assertSame($expected, \App\Validator::{$fn}($value));
	}

	/**
	 * Data provider for testValidator function.
	 *
	 * @return array
	 */
	public function dataValidator(): array
	{
		return [
			['url', true, 'ssl://imap.gmail.com:993'],
			['url', true, 'ssl://imap.gmail.com'],
			['url', true, 'tls://imap.gmail.com'],
			['url', true, 'imap.gmail.com:993'],
			['url', true, 'imap.gmail.com'],
			['urlDomain', true, 'ssl://imap.gmail.com:993'],
			['urlDomain', true, 'ssl://imap.gmail.com'],
			['urlDomain', true, 'tls://imap.gmail.com'],
			['urlDomain', true, 'imap.gmail.com'],
			['urlDomain', true, 'google.pl'],
			['urlDomain', true, 'http://google.pl'],
			['urlDomain', false, 'http://a-.bc.com'],
			['urlDomain', false, 'https://1243yfcom%.pl'],
			['urlDomain', false, '.'],
			['urlDomain', false, '#'],
			['urlDomain', false, 'https://yetiforce*com/pl/'],
			['urlDomain', false, 'https://yeti force.com/pl/'],
			['urlDomain', true, 'ftp://yetiforce.com'],
			['urlDomain', false, 'test@com.pl'],
			['urlDomain', true, 'tel://600500100'],
			['urlDomain', true, 'mailto://info@yetiforce.com'],
			['urlDomain', false, 'http*://yetiforce.com'],
			['urlDomain', true, 'http://yetiforce.com:2160/'],
			['urlDomain', false, ' http://yetiforce.com/'],
			['urlDomain', false, 'javascript:alert(1)'],
			['urlDomain', true, 'http://www.müller.de'],
			['urlDomain', true, 'http://элтранс.рф'],
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
