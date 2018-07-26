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
	public function testEmptyValues()
	{
		$this->assertSame('', \App\Purifier::purify(''), 'Empty text should be unchanged');
		\App\Purifier::$collectErrors = true;
		$this->assertSame('', \App\Purifier::purifyHtml(''), 'Empty text should be unchanged');
		\App\Purifier::$collectErrors = false;
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes(''), 'Empty text should not throw exception');
		$this->assertSame('', \App\Purifier::purifySql(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::encodeHtml(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::decodeHtml(''), 'Empty text should be unchanged');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertSame('', \App\Purifier::purifySql('', false), 'Empty text should be unchanged');
	}

	public function testTextValues()
	{
		$this->assertSame('Test text string for purifier', \App\Purifier::purify('Test text string for purifier'), 'Sample text should be unchanged');
		$this->assertSame('Test text string for purifier', \App\Purifier::purify('Test text string for purifier'), 'Sample text should be unchanged(cached)');
		$this->assertSame(['Test text string for purifier', 'Test text string for purifier'], \App\Purifier::purify(['Test text string for purifier', 'Test text string for purifier']), 'Sample text should be unchanged(array)');
		$this->assertSame('Test text string for purifier', \App\Purifier::purifyHtml('Test text string for purifier'), 'Sample text should be unchanged');
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes('Test text string for purifier'), 'Sample text should be unchanged');
	}

	public function testPurifyByTypeStandard()
	{
		$text = 'Test-text-string-for-purifier';
		$textBad = 'Test-text-string-for-purifier%$54#T$#BR';
		$type = 'Standard';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}

	public function testPurifyByTypeAlnum()
	{
		$text = 'Test_text_alnum_4_purifier';
		$textBad = 'Test_text_alnum_4_purifier%$54#T$#BR-';
		$type = 'Alnum';
		$this->assertSame($text, \App\Purifier::purifyByType($text, $type), 'Sample text should be unchanged');
		$this->assertSame([$text, $text], \App\Purifier::purifyByType([$text, $text], $type), 'Sample text should be unchanged(array)');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertNotSame($textBad, \App\Purifier::purifyByType($textBad, $type), 'Sample text should be purified');
		$this->assertNotSame([$textBad, $textBad], \App\Purifier::purifyByType([$textBad, $textBad], $type), 'Sample text should be purified(array)');
	}
}
