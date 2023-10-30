<?php
/**
 * TextUtils test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Tests\App;

/**
 * TextUtils test class.
 */
class TextUtils extends \Tests\Base
{
	/**
	 * Tests `\App\TextUtils::getTextLength` methods.
	 *
	 * @see \App\TextUtils::getTextLength()
	 */
	public function testTextLength()
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

	/**
	 * Tests `\App\TextUtils::htmlTruncate` methods.
	 *
	 * @see \App\TextUtils::htmlTruncate()
	 */
	public function testHtmlTruncate()
	{
		$htmlTruncate = \App\TextUtils::htmlTruncate(\Tests\Base\C_RecordActions::createLoremIpsumHtml(), 200);
		$this->assertSame(18, \strlen(strip_tags($htmlTruncate)), 'html should be truncated in expected format (length=18)');
		$this->assertSame(138, \strlen($htmlTruncate), 'html should be truncated in expected format (default length=138)');
	}

	/**
	 * Tests `\App\TextUtils::getTagAttributes` methods.
	 *
	 * @see \App\TextUtils::getTagAttributes()
	 */
	public function testGetTagAttributes()
	{
		$attributes = \App\TextUtils::getTagAttributes('<yetiforce type="Documents" crm-id="448" attachment-id="19"></yetiforce>');
		$this->assertArrayHasKey('type', $attributes);
		$this->assertArrayHasKey('crm-id', $attributes);
		$this->assertArrayHasKey('attachment-id', $attributes);
		$this->assertNotEmpty($attributes);
	}
}
