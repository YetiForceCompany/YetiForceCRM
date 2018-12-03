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
	/**
	 * Testing initialize function.
	 */
	public function testInitialize()
	{
		$this->assertNull(\App\Custom\NumberToWords::initialize(), 'Expected null');
	}

	/**
	 * integerNumberToWords tests data provider.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function int2wordProvider()
	{
		return [
			[0.00, 'zero'],
			['0.00', 'zero'],
			['-150', 'minus sto pięćdziesiąt'],
			['-1', 'minus jeden'],
			['-0', 'zero'],
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

	/**
	 * Provide test data for process function.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function processProvider()
	{
		return [
			['0.00', 'zero zł zero gr', 'pl_pl'],
			[0.00, 'zero zł zero gr', 'pl_pl'],
			['0.00', 'zero zł zero gr', 'en_us'],
			[0.00, 'zero zł zero gr', 'en_us'],
			[0.01, 'zero zł jeden gr', 'pl_pl'],
			[0.01, 'zero zł one gr', 'en_us'],
			[15, 'piętnaście zł zero gr', 'pl_pl'],
			[15, 'fifteen zł zero gr', 'en_us'],
			[115.50, 'sto piętnaście zł pięćdziesiąt gr', 'pl_pl'],
			[115.50, 'hundred fifteen zł fifty gr', 'en_us'],
			[2115.50, 'dwa tysiące sto piętnaście zł pięćdziesiąt gr', 'pl_pl'],
			[2115.50, 'two thousand hundred fifteen zł fifty gr', 'en_us'],
		];
	}

	/**
	 * Testing process function.
	 *
	 * @dataProvider processProvider
	 *
	 * @param number $amount
	 * @param string $expected
	 * @param string $lang
	 */
	public function testProcess($amount, $expected, $lang)
	{
		\App\Language::setTemporaryLanguage($lang);
		$this->assertSame($expected, \App\Custom\NumberToWords::process($amount), 'Expected amount ' . $amount . ' translates to ' . $expected);
		\App\Language::clearTemporaryLanguage();
	}

	/**
	 * Testing process function with bad amount.
	 *
	 * @throws \Exception
	 */
	public function testProcessBadAmount()
	{
		$this->expectException(\Exception::class);
		\App\Custom\NumberToWords::process('115$');
	}
}
