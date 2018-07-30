<?php
/**
 * Languages test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Language extends \Tests\Base
{
	/**
	 * Testing get language function.
	 */
	public function testGetLanguage()
	{
		$currLang = \App\Language::getLanguage();
		\App\Session::set('language', $currLang);
		$this->assertSame($currLang, \App\Language::getLanguage());
		$this->assertSame(explode('_', $currLang), explode('-', strtolower(\App\Language::getLanguageTag())));
	}
}
