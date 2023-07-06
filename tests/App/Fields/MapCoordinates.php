<?php
/**
 * Test file for `\App\Fields\MapCoordinates`.
 *
 * @see \App\Fields\MapCoordinates
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App\Fields;

/**
 * Test class for `\App\Fields\MapCoordinates`.
 */
class MapCoordinates extends \Tests\Base
{
	/** @return array Provide test data for testConvert function. */
	public function convertProvider(): array
	{
		return [
			['decimal', 'decimal', ['lat' => 52.23155431436567, 'lon' => 21.00499528120955], ['lat' => 52.23155431436567, 'lon' => 21.00499528120955]],
			['decimal', 'degrees', ['lat' => 52.23155431436567, 'lon' => 21.00499528120955], ['lat' => '52°13\'53.5955"N', 'lon' => '21°00\'17.983"E']],
			['decimal', 'degrees', ['lat' => 30, 'lon' => 33.3], ['lat' => '30°N', 'lon' => '33°18\'E']],
			['degrees', 'decimal', ['lat' => 'N 50°12\'13.1188"', 'lon' => 'E 21°0\'17.983"'], ['lat' => 50.203644111111, 'lon' => 21.004995277778]],
			['degrees', 'decimal', ['lat' => 'N50°12\'13.1188"', 'lon' => 'E21°0\'17.983"'], ['lat' => 50.203644111111, 'lon' => 21.004995277778]],
			['degrees', 'decimal', ['lat' => '50°12\'13.1188" N', 'lon' => '21°0\'17.983" E'], ['lat' => 50.203644111111, 'lon' => 21.004995277778]],
			['degrees', 'decimal', ['lat' => '50°12\'13.1188"N', 'lon' => '21°0\'17.983"E'], ['lat' => 50.203644111111, 'lon' => 21.004995277778]],
			['decimal', 'codeplus', ['lat' => 52.23155431436567, 'lon' => 21.00499528120955], '9G4362J3+JXH5'],
			['codeplus', 'decimal', '9G4362J3+GR', ['lat' => 52.23131249999999, 'lon' => 21.004562500000006]],
		];
	}

	/**
	 * Testing process `\App\Fields\MapCoordinates::convert` function.
	 *
	 * @dataProvider convertProvider
	 *
	 * @see \App\Fields\MapCoordinates::convert()
	 *
	 * @param string $from
	 * @param string $to
	 * @param mixed  $value
	 * @param mixed  $result
	 */
	public function testConvert(string $from, string $to, $value, $result): void
	{
		$this->assertEquals($result, \App\Fields\MapCoordinates::convert($from, $to, $value));
	}

	/**
	 * Exception testing for conversion.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testConvertExceptionFrom(): void
	{
		$this->expectException(\App\Exceptions\AppException::class);
		\App\Fields\MapCoordinates::convert('test', 'decimal', ['lat' => 52.23155431436567, 'lon' => 21.00499528120955]);
	}

	/**
	 * Exception testing for conversion.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testConvertExceptionTo(): void
	{
		$this->expectException(\App\Exceptions\AppException::class);
		\App\Fields\MapCoordinates::convert('decimal', 'test', ['lat' => 52.23155431436567, 'lon' => 21.00499528120955]);
	}
}
