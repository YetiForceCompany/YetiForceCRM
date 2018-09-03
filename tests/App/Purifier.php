<?php
/**
 * TextParser test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Purifier extends \Tests\Base
{
	/**
	 * Provide data for purifyByType test cases.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function dataProviderByType()
	{
		//$type, $assertion, $expected, $text, $message, $exception
		return [
			['Standard', 'Same', 'Test-text-string-for-purifier', 'Test-text-string-for-purifier', 'Sample text should be unchanged', false],
			['Standard', 'Same', ['Test-text-string-for-purifier', 'Test-text-string-for-purifier'], ['Test-text-string-for-purifier', 'Test-text-string-for-purifier'], 'Sample text should be unchanged(array)', false],
			['Standard', 'NotSame', 'Test-text-string-for-purifier%$54#T$#BR', 'Test-text-string-for-purifier%$54#T$#BR', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Alnum', 'Same', 'Test_text_alnum_4_purifier', 'Test_text_alnum_4_purifier', 'Sample text should be unchanged', false],
			['Alnum', 'NotSame', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			[2, 'Same', 'Test_text_alnum_4_purifier', 'Test_text_alnum_4_purifier', 'Sample text should be unchanged', false],
			[2, 'NotSame', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['DateInUserFormat', 'Same', date('Y-m-d'), date('Y-m-d'), 'Sample text should be unchanged', false],
			['DateInUserFormat', 'NotSame', date('Y.m.d'), date('Y.m.d'), 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['DateRangeUserFormat', 'Same', [date('Y-m-d'), date('Y-m-d', \strtotime('+1 day'))], date('Y-m-d') . ',' . date('Y-m-d', \strtotime('+1 day')), 'Sample text should be unchanged', false],
			['DateRangeUserFormat', 'NotSame', date('Y.m.d') . ',' . date('Y.m.d', \strtotime('+1 day')), date('Y.m.d') . ',' . date('Y.m.d', \strtotime('+1 day')), 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Date', 'Same', date('Y-m-d'), date('Y-m-d'), 'Sample text should be unchanged', false],
			['Date', 'NotSame', '201X-07-26', '201X-07-26', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Bool', 'Same', true, true, 'Sample text should be unchanged', false],
			['Bool', 'NotSame', 'Test-text', 'Test-text', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['NumberInUserFormat', 'Same', '1234567890', '1234567890', 'Sample text should be unchanged', false],
			['NumberInUserFormat', 'NotSame', '12345X7890', '12345X7890', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Integer', 'Same', 1234, 1234, 'Sample integer should be unchanged', false],
			['Integer', 'NotSame', '12X4', '12X4', 'Sample integer should be purified', \App\Exceptions\IllegalValue::class],
			['Digital', 'Same', '43453453', '43453453', 'Sample number should be unchanged', false],
			['Digital', 'NotSame', '43453C53', '43453C53', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Color', 'Same', '#3A13F5', '#3A13F5', 'Sample number should be unchanged', false],
			['Color', 'NotSame', '#3A13FZ', '#3A13FZ', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Year', 'Same', date('Y'), date('Y'), 'Sample number should be unchanged', false],
			['Year', 'NotSame', '201X', '201X', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Text', 'Same', 'Test-text-string-for-purifier', 'Test-text-string-for-purifier', 'Sample number should be unchanged', false],
			['Text', 'NotSame', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Default', 'Same', 'Test-text-string-for-purifier', 'Test-text-string-for-purifier', 'Sample number should be unchanged', false],
			['Default', 'NotSame', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
		];
	}

	/**
	 * Testing purify empty values.
	 */
	public function testEmptyValues()
	{
		$this->assertSame('', \App\Purifier::purify(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::purifyHtml(''), 'Empty text should be unchanged');
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes(''), 'Empty text should not throw exception');
		$this->assertSame('', \App\Purifier::purifySql(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::encodeHtml(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::decodeHtml(''), 'Empty text should be unchanged');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertSame('', \App\Purifier::purifySql('', false), 'Empty text should be unchanged');
	}

	/**
	 * Testing purify text values.
	 */
	public function testTextValues()
	{
		$this->assertSame('Test text string for purifier', \App\Purifier::purify('Test text string for purifier'), 'Sample text should be unchanged');
		$this->assertSame('Test text string for purifier', \App\Purifier::purify('Test text string for purifier'), 'Sample text should be unchanged(cached)');
		$this->assertSame(['Test text string for purifier', 'Test text string for purifier'], \App\Purifier::purify(['Test text string for purifier', 'Test text string for purifier']), 'Sample text should be unchanged(array)');
		$this->assertSame('Test text string for purifier', \App\Purifier::purifyHtml('Test text string for purifier'), 'Sample text should be unchanged');
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes('Test text string for purifier'), 'Sample text should be unchanged');
	}

	/**
	 * @param string       $type
	 * @param string|false $textOk
	 * @param string|false $textBad
	 * @dataProvider dataProviderByType
	 */
	public function testPurifyByType($type, $assertion, $expected = 'NOT_SET', $text, $message, $exception)
	{
		$assertion = 'assert' . $assertion;
		if ($exception) {
			$this->expectException($exception);
		}
		$this->$assertion($expected, \App\Purifier::purifyByType($text, $type), $message);
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function purifyHtmlProvider()
	{
		return [
			['<div>Test-text-string-for-purifier</div>', '<div>Test-text-string-for-purifier</div>', true],
			['<img src="1"onload=alert(1)>', '', true],
			['&lt;svg/onload=alert(1)onabort=alert(2)//', '&lt;svg/onload=alert(1)onabort=alert(2)//', false],
			['<img src="1" onerror=alert(1)>', '', true],
			['ę€ółśążźćń23{}":?>><>?:"{}+_)', 'ę€ółśążźćń23{}":?&gt;&gt;&lt;&gt;?:"{}+_)', true],
			['ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!)', 'ę€ółśążźćń23{}":?&gt;&gt;&lt;&gt;?:"{}+_)(*&amp;^%$#@!)', true],
			['ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//', false],
			['ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', false]
		];
	}

	/**
	 * Testing html purifier.
	 *
	 * @dataProvider purifyHtmlProvider
	 */
	public function testPurifyHtml($text, $expected, $notThrowException)
	{
		if ($notThrowException) {
			$this->assertSame($expected, \App\Purifier::purifyHtml($text), 'Sample text should be unchanged');
		} else {
			$this->expectException(\App\Exceptions\IllegalValue::class);
			$this->assertNotSame($expected, \App\Purifier::purifyHtml($text), 'Sample text should be purified');
		}
	}
}
