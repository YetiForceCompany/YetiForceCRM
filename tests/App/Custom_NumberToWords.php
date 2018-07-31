<?php
/**
 * Custom\NumberToWords test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Custom_NumberToWords extends \Tests\Base
{
	public function testInitialize()
	{
		$this->assertNull(\App\Custom\NumberToWords::initialize(), 'Expected null');
	}

	/**
	 * integerNumberToWords tests data provider.
	 *
	 * @return array
	 */
	public function int2wordProvider()
	{
		return [
			['-1', 'minus jeden'],
			[0, 'zero'],
			[1, 'jeden'],
			[2, 'dwa'],
			[3, 'trzy'],
			[4, 'cztery'],
			[5, 'pięć'],
			[6, 'sześć'],
			[7, 'siedem'],
			[8, 'osiem'],
			[9, 'dziewięć'],
			[10, 'dziesięć'],
			[11, 'jedenaście'],
			[12, 'dwanaście'],
			[13, 'trzynaście'],
			[14, 'czternaście'],
			[15, 'piętnaście'],
			[16, 'szesnaście'],
			[17, 'siedemnaście'],
			[18, 'osiemnaście'],
			[100, 'sto'],
			[115, 'sto piętnaście'],
			[999, 'dziewięćset dziewięćdziesiąt dziewięć']
		];
	}

	/**
	 * Testing integerNumberToWords function.
	 *
	 * @dataProvider int2wordProvider
	 *
	 * @param int    $int
	 * @param string $expected
	 */
	public function testIntegerNumberToWords($int, $expected)
	{
		$this->assertSame($expected, \App\Custom\NumberToWords::integerNumberToWords($int), 'Expeected int ' . $int . ' translates to ' . $expected);
	}

	public function processProvider()
	{
		return [
			[0.01, 'zero zł jeden gr'],
			[15, 'piętnaście zł zero gr'],
			[115.50, 'sto piętnaście zł pięćdziesiąt gr'],
			[2115.50, 'dwa tysiące sto piętnaście zł pięćdziesiąt gr'],
		];
	}

	/**
	 * Testing process function.
	 *
	 * @dataProvider processProvider
	 *
	 * @param number $amount
	 * @param string $expected
	 */
	public function testProcess($amount, $expected)
	{
		$this->assertSame($expected, \App\Custom\NumberToWords::process($amount), 'Expected amount ' . $amount . ' translates to ' . $expected);
	}
}
