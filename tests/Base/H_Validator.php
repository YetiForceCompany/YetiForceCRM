<?php
/**
 * The file contains: Validator test class.
 *
 * @package Tests
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Base;

/**
 * Validator test class.
 */
class H_Validator extends \Tests\Base
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
		$this->assertSame($expectedValue, \App\Validator::urlNoProtocolRequired($value));
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
			[false, 'test@com.pl'],
		];
	}
}
