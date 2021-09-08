<?php
/**
 * Languages test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * Languages test class.
 */
class Language extends \Tests\Base
{
	/**
	 * Testing get language function.
	 */
	public function testGetLanguage()
	{
		\App\Session::set('language', 'pl-PL');
		$currLang = \App\Language::getLanguage();
		$this->assertSame($currLang, \App\Language::getLanguage());
		$this->assertContains(\App\Language::getLanguageLabel('pl-PL'), ['Język Polski', 'Polski']);
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
		$this->assertNotEmpty(\App\Language::getLangInfo('pl-PL'));
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
		$this->assertSame('TestString', \App\Language::translate('TestString', \App\Module::getModuleId('Leads')));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		$this->assertSame('Leady', \App\Language::translateEncodeHtml('Leads', 'Leads'));

		$this->assertSame('pl-PL', \App\Language::getLanguage());
		$this->assertSame('miesiąc', \App\Language::translate('LBL_MONTH'));
		$this->assertTrue('aaa z bbb są poprawne dla wybranego szablonu.' === \App\Language::translateArgs('LBL_VALID_RECORDS', 'Vtiger', 'aaa', 'bbb'));
		$this->assertTrue('Ostrzeżenie systemowe' === \App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 1));
		$this->assertTrue('Ostrzeżenia systemowe' === \App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 2));
		$this->assertTrue('Ostrzeżeń systemowych' === \App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', 'Settings::Vtiger', 9));

		\App\Language::setTemporaryLanguage('pt-BR');
		$this->assertSame('Leads_1', \App\Language::translatePluralized('Leads', 'Leads', 5));
		\App\Language::setTemporaryLanguage('en-US');
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		\App\Language::setTemporaryLanguage('ru-RU');
		$this->assertSame('Leads_0', \App\Language::translatePluralized('Leads', 'Leads', 1));
		$this->assertSame('Leads_1', \App\Language::translatePluralized('Leads', 'Leads', 53));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 5));
		\App\Language::setTemporaryLanguage('ro-RO');
		$this->assertSame('Leads_0', \App\Language::translatePluralized('Leads', 'Leads', 1));
		$this->assertSame('Leads_1', \App\Language::translatePluralized('Leads', 'Leads', 53));
		$this->assertSame('Leads_2', \App\Language::translatePluralized('Leads', 'Leads', 0));

		\App\Language::setTemporaryLanguage('pl-PL');
	}

	/**
	 * Testing get from file method.
	 */
	public function testGetFromFile()
	{
		$this->assertNotNull(\App\Language::getFromFile('Leads', 'pl-PL'));
		$this->assertNotNull(\App\Language::getFromFile('Leads', 'pl-PL'));
	}

	/**
	 * Testing load language file function.
	 */
	public function testLoadLanguageFile()
	{
		$this->assertNull(\App\Language::loadLanguageFile('pl-PL', 'Leads'));
		$this->assertNull(\App\Language::loadLanguageFile('pl-PL', 'Leads'));
	}

	/**
	 * Testing translation files modification method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testTranslationModify()
	{
		$this->assertNull(\App\Language::translationModify('pl-PL', 'Leads', 'PHP', 'FileTestString', 'file_test_string_content', false));
		$this->assertNull(\App\Language::translationModify('pl-PL', 'Leads', 'PHP', 'FileTestString', 'file_test_string_content', true));
	}
}
