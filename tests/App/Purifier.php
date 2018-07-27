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
		//\App\Purifier::$collectErrors = true;
		$this->assertSame('Test text string for purifier', \App\Purifier::purifyHtml('Test text string for purifier'), 'Sample text should be unchanged');
		//\App\Purifier::$collectErrors = false;
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes('Test text string for purifier'), 'Sample text should be unchanged');
	}

	/**
	 * Testing purify by type: standard.
	 */
	public function testPurifyByTypeStandard()
	{
		$text = 'Test-text-string-for-purifier';
		$textBad = 'Test-text-string-for-purifier%$54#T$#BR';
		$type = 'Standard';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: standard.
	 */
	public function testPurifyByTypeStandardBad2()
	{
		$textBad = 'Test-text-string-for-purifier%$54#T$#BR';
		$type = 'Standard';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing purify by type: alnum.
	 */
	public function testPurifyByTypeAlnum()
	{
		$text = 'Test_text_alnum_4_purifier';
		$textBad = 'Test_text_alnum_4_purifier%$54#T$#BR-';
		$type = 'Alnum';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: alnum.
	 */
	public function testPurifyByTypeAlnumBad2()
	{
		$textBad = 'Test_text_alnum_4_purifier%$54#T$#BR-';
		$type = 'Alnum';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing purify by type: 2.
	 */
	public function testPurifyByType2()
	{
		$text = 'Test_text_alnum_4_purifier';
		$textBad = 'Test_text_alnum_4_purifier%$54#T$#BR-';
		$type = 2;
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: 2.
	 */
	public function testPurifyByType2Bad2()
	{
		$textBad = 'Test_text_alnum_4_purifier%$54#T$#BR-';
		$type = 2;
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing purify by type: date in user format.
	 */
	public function testPurifyByTypeDateUserFormat()
	{
		$userModel = \Users_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$currFormat = $userModel->get('date_format');
		$userModel->set('date_format', 'yyyy.mm.dd');
		$userModel->save();
		$text = date('Y.m.d');
		$textExpected = date('Y.m.d');
		$textBad = date('Y-m-d');
		$textBadExpected = '';
		$type = 'DateInUserFormat';
		$this->assertSame('', \App\Purifier::purifyByType('', $type), 'Sample empty date should be unchanged');
		$this->assertSame($textExpected, \App\Purifier::purifyByType($text, $type), 'Sample date should be unchanged');
		$this->assertSame([$textExpected, $textExpected], \App\Purifier::purifyByType([$text, $text], $type), 'Sample date range should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertSame($textBadExpected, \App\Purifier::purifyByType($textBad, $type), 'Sample date range should be purified') && $userModel->set('date_format', $currFormat) && $userModel->save();
	}

	/**
	 * Testing purify by type: date in user format.
	 */
	public function testPurifyByTypeDateUserFormatBad()
	{
		$userModel = \Users_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$currFormat = $userModel->get('date_format');
		$userModel->set('date_format', 'yyyy.mm.dd');
		$userModel->save();
		$textBad = date('Y-m-d');
		$textBadExpected = '';
		$type = 'DateInUserFormat';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertSame([$textBadExpected, $textBadExpected], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample date range should be purified(array)') && $userModel->set('date_format', $currFormat) && $userModel->save();
	}

	/**
	 * Testing purify by type: date range user format.
	 */
	public function testPurifyByTypeDateRange()
	{
		$userModel = \Users_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$currFormat = $userModel->get('date_format');
		$userModel->set('date_format', 'yyyy.mm.dd');
		$userModel->save();
		$text = date('Y.m.d') . ',' . date('Y.m.d', \strtotime('+1 day'));
		$textExpected = [date('Y-m-d'), date('Y-m-d', \strtotime('+1 day'))];
		$textBad = '201X-07.26,2018.07-27';
		$textBadExpected = [];
		$type = 'DateRangeUserFormat';
		$this->assertSame($textExpected, \App\Purifier::purifyByType($text, $type), 'Sample date range should be unchanged');
		$this->assertSame([$textExpected, $textExpected], \App\Purifier::purifyByType([$text, $text], $type), 'Sample date range should be unchanged(array)');
		$this->assertSame($textBadExpected, \App\Purifier::purifyByType($textBad, $type), 'Sample date range should be purified');
		$this->assertSame([$textBadExpected, $textBadExpected], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample date range should be purified(array)');
		$userModel->set('date_format', $currFormat);
		$userModel->save();
	}

	/**
	 * Testing purify by type: date.
	 */
	public function testPurifyByTypeDate()
	{
		$text = date('Y-m-d');
		$textBad = '201X-07-26';
		$type = 'Date';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: date.
	 */
	public function testPurifyByTypeDateBad()
	{
		$textBad = '201X-07-26';
		$type = 'Date';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing purify by type: bool.
	 */
	public function testPurifyByTypeBool()
	{
		$text = true;
		$textBad = 'Test-text';
		$type = 'Bool';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: bool.
	 */
	public function testPurifyByTypeBoolBad()
	{
		$textBad = 'Test-text';
		$type = 'Bool';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing purify by type: number in user format.
	 */
	public function testPurifyByTypeNumberUserFormat()
	{
		$text = '123456789';
		$textBad = '1234X6789';
		$type = 'NumberInUserFormat';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample number in user format should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample number in user format should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample number in user format should be purified');
	}

	/**
	 * Testing purify by type: number in user format.
	 */
	public function testPurifyByTypeNumberUserFormatBad()
	{
		$textBad = '1234X6789';
		$type = 'NumberInUserFormat';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample number in user format should be purified(array)');
	}

	/**
	 * Testing purify by type: integer.
	 */
	public function testPurifyByTypeInt()
	{
		$text = 1234;
		$textBad = '1234X';
		$type = 'Integer';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample integer should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample integer should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample integer should be purified');
	}

	/**
	 * Testing purify by type: integer.
	 */
	public function testPurifyByTypeIntBad()
	{
		$textBad = '1234X';
		$type = 'Integer';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample integer should be purified(array)');
	}

	/**
	 * Testing purify by type: digital.
	 */
	public function testPurifyByTypeDigital()
	{
		$text = '45353453';
		$textBad = '45353C53';
		$type = 'Digital';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample digital should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample digital should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample digital should be purified');
	}

	/**
	 * Testing purify by type: digital.
	 */
	public function testPurifyByTypeDigitalBad()
	{
		$textBad = '45353C53';
		$type = 'Digital';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample digital should be purified(array)');
	}

	/**
	 * Testing purify by type: color.
	 */
	public function testPurifyByTypeColor()
	{
		$textBad = '#3A13FZ';
		$type = 'Color';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample color should be purified(array)');
	}

	/**
	 * Testing purify by type: year.
	 */
	public function testPurifyByTypeYear()
	{
		$text = date('Y');
		$textBad = '201X';
		$type = 'Year';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample year should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample year should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample year should be purified');
	}

	/**
	 * Testing purify by type: year.
	 */
	public function testPurifyByTypeYearBad()
	{
		$textBad = '201X';
		$type = 'Year';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample year should be purified(array)');
	}

	/**
	 * Testing purify by type: text.
	 */
	public function testPurifyByTypeText()
	{
		$text = 'Test-text-string-for-purifier';
		$textBad = 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//';
		$type = 'Text';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: text.
	 */
	public function testPurifyByTypeTextBad()
	{
		$textBad = 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//';
		$type = 'Text';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing purify by type: default.
	 */
	public function testPurifyByTypeDefault()
	{
		$text = 'Test-text-string-for-purifier';
		$textBad = 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//';
		$type = 'Default';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
	}

	/**
	 * Testing purify by type: default.
	 */
	public function testPurifyByTypeDefaultBad()
	{
		$textBad = 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//';
		$type = 'Default';
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	/**
	 * Testing html purifier.
	 */
	public function testPurifyHtml()
	{
		$text = '<div>Test-text-string-for-purifier</div>';
		$textBad = 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//   <svg/onload=alert(1) onfocus=alert(2)// KdYe<XnS&#@fs
  <img src="1"onload=alert(1)>
  &lt;svg/onload=alert(1)onabort=alert(2)//
  <img src="1" onerror=alert(1)>
  ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//';
		$this->assertSame($text, \App\Purifier::purifyHtml($text), 'Sample text should be unchanged');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyHtml($textBad), 'Sample text should be purified');
	}
}
