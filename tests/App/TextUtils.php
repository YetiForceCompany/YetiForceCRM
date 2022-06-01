<?php
/**
 * TextUtils test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * TextUtils test class.
 */
class TextParser extends \Tests\Base
{
	/**
	 * Tests `\App\TextUtils::getTextLength` methods.
	 *
	 * @see \App\TextUtils::getTextLength()
	 */
	public function getTextLength()
	{
		$this->assertSame(0, \App\TextUtils::getTextLength(null));
		$this->assertSame(4, \App\TextUtils::getTextLength('test'));
	}

	/**
	 * Tests `\App\TextUtils::textTruncate` methods.
	 *
	 * @see \App\TextUtils::textTruncate()
	 */
	public function testTextTruncate()
	{
		$this->assertSame((\App\Config::main('listview_max_textlength') + 3), \strlen(\App\TextUtils::textTruncate(\Tests\Base\C_RecordActions::createLoremIpsumText(), false, true)), 'string should be truncated in expexted format (default length)');
		$this->assertSame(13, \strlen(\App\TextUtils::textTruncate(\Tests\Base\C_RecordActions::createLoremIpsumText(), 10, true)), 'string should be truncated in expexted format (text length: 10)');
	}

	/**
	 * Tests `\App\TextUtils::htmlTruncateByWords` methods.
	 *
	 * @see \App\TextUtils::htmlTruncateByWords()
	 */
	public function testHtmlTruncateByWords()
	{
		$this->assertSame(15, \strlen(strip_tags(\App\TextUtils::htmlTruncateByWords(\Tests\Base\C_RecordActions::createLoremIpsumHtml(), 40, ''))), 'html should be truncated in expected format (text length: 10)');
	}
}
