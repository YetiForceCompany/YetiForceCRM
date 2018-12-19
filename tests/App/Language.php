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
		\App\Session::set('language', 'pl_pl');
		$currLang = \App\Language::getLanguage();
		$this->assertSame($currLang, \App\Language::getLanguage());
		$this->assertSame('Język Polski', \App\Language::getLanguageLabel('pl_pl'));
		$this->assertSame('SINGLE_Leads', \App\Language::getSingularModuleName('Leads'));
		$this->assertSame('Lead', \App\Language::translateSingularModuleName('Leads'));
		$this->assertSame('TestKey', \App\Language::translateSingleMod('TestKey'));
	}

	/**
	 * Testing get all languages data.
	 */
	public function testGetAll()
	{
		$this->assertNotEmpty(\App\Language::getAll(true, true));
		$this->assertNotEmpty(\App\Language::getAll(true, true));
	}

	/**
	 * Testing get language info by prefix.
	 */
	public function testGetLangInfo()
	{
		$this->assertNotEmpty(\App\Language::getLangInfo('pl_pl'));
	}

	/**
	 * Testing init locale function.
	 */
	public function testInitLocale()
	{
		$this->assertNull(\App\Language::initLocale());
	}

	/**
	 * Testing get javascript strings.
	 */
	public function testGetJsStrings()
	{
		$this->assertNotEmpty(\App\Language::getJsStrings('Leads'));
		$this->assertNotEmpty(\App\Language::getJsStrings('Settings'));
	}

	/**
	 * Testing translate method.
	 */
	public function testTranslate()
	{
		$this->assertSame('', \App\Language::translate('', '_Base'));
		$this->assertSame('TestString', \App\Language::translate('TestString', ['_Base']));
		$this->assertSame('TestString', \App\Language::translate('TestString', \App\Module::getModuleId('Leads')));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		$this->assertSame('Leady', \App\Language::translateEncodeHtml('Leads', 'Leads'));
		\App\Language::setTemporaryLanguage('pt_br');
		$this->assertSame('Leads_1', \App\Language::translatePluralized('Leads', 'Leads', 5));
		\App\Language::setTemporaryLanguage('en_us');
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		\App\Language::setTemporaryLanguage('ru_ru');
		$this->assertSame('Leads_0', \App\Language::translatePluralized('Leads', 'Leads', 1));
		$this->assertSame('Leads_1', \App\Language::translatePluralized('Leads', 'Leads', 53));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		\App\Language::setTemporaryLanguage('ro_ro');
		$this->assertSame('Leads_0', \App\Language::translatePluralized('Leads', 'Leads', 1));
		$this->assertSame('Leads_1', \App\Language::translatePluralized('Leads', 'Leads', 53));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 0));

		\App\Language::setTemporaryLanguage('pl_pl');
	}

	/**
	 * Testing get from file method.
	 */
	public function testGetFromFile()
	{
		$this->assertNotNull(\App\Language::getFromFile('Leads', 'pl_pl'));
		$this->assertNotNull(\App\Language::getFromFile('Leads', 'pl_pl'));
	}

	/**
	 * Testing load language file function.
	 */
	public function testLoadLanguageFile()
	{
		$this->assertNull(\App\Language::loadLanguageFile('pl_pl', 'Leads'));
		$this->assertNull(\App\Language::loadLanguageFile('pl_pl', 'Leads'));
	}

	/**
	 * Testing translation files modification method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testTranslationModify()
	{
		$this->assertNull(\App\Language::translationModify('pl_pl', 'Leads', 'PHP', 'FileTestString', 'file_test_string_content', false));
		$this->assertNull(\App\Language::translationModify('pl_pl', 'Leads', 'PHP', 'FileTestString', 'file_test_string_content', true));
	}
}
