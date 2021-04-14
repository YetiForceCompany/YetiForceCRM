<?php
/**
 * TextParser test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Purifier extends \Tests\Base
{
	/**
	 * @var string Decimal numbers separator
	 */
	public static $separatorDecimal;
	/**
	 * @var string Numbers grouping separator
	 */
	public static $separatorGrouping;
	/**
	 * @var string Currency symbol placement
	 */
	public static $symbolPlacement;
	/**
	 * @var string Numbers grouping pattern
	 */
	public static $patternGrouping;
	/**
	 * @var int Decimal places count
	 */
	public static $decimalNum;
	/**
	 * @var bool Truncate zeros in decimal numbers
	 */
	public static $truncateTrailingZeros;

	/**
	 * @var string Hour format.
	 */
	public static $hourFormat;

	/**
	 * @var string Timezone.
	 */
	public static $timeZone;

	/**
	 * @var User string Timezone.
	 */
	public static $userTimeZone;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		\App\User::setCurrentUserId(\App\User::getActiveAdminId());
		$userModel = \App\User::getCurrentUserModel();
		self::$separatorDecimal = $userModel->getDetail('currency_decimal_separator');
		self::$separatorGrouping = $userModel->getDetail('currency_grouping_separator');
		self::$symbolPlacement = $userModel->getDetail('currency_symbol_placement');
		self::$patternGrouping = $userModel->getDetail('currency_grouping_pattern');
		self::$decimalNum = $userModel->getDetail('no_of_currency_decimals');
		self::$hourFormat = $userModel->getDetail('hour_format');
		self::$truncateTrailingZeros = $userModel->getDetail('truncate_trailing_zeros');
		self::$userTimeZone = $userModel->getDetail('time_zone');
		self::$timeZone = date_default_timezone_get();
		$userRecordModel = \Vtiger_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$userRecordModel->set('currency_decimal_separator', '.');
		$userRecordModel->set('currency_grouping_separator', ' ');
		$userRecordModel->set('currency_symbol_placement', '1.0$');
		$userRecordModel->set('currency_grouping_pattern', '123456789');
		$userRecordModel->set('no_of_currency_decimals', '2');
		$userRecordModel->set('truncate_trailing_zeros', 1);
		$userRecordModel->set('hour_format', '24');
		$userRecordModel->set('time_zone', \App\Fields\DateTime::getTimeZone());
		$userRecordModel->save();
		\date_default_timezone_set(\App\Fields\DateTime::getTimeZone());
	}

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
			['Standard', 'Same', 'Test-text-string-for-purifier', 'Test-text-string-for-purifier', 'Sample text should be unchanged', null],
			['Standard', 'Same', ['Test-text-string-for-purifier', 'Test-text-string-for-purifier'], ['Test-text-string-for-purifier', 'Test-text-string-for-purifier'], 'Sample text should be unchanged(array)', null],
			['Standard', 'NotSame', 'Test-text-string-for-purifier%$54#T$#BR', 'Test-text-string-for-purifier%$54#T$#BR', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Alnum', 'Same', 'Test_text_alnum_4_purifier', 'Test_text_alnum_4_purifier', 'Sample text should be unchanged', null],
			['Alnum', 'NotSame', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			[2, 'Same', 'Test_text_alnum_4_purifier', 'Test_text_alnum_4_purifier', 'Sample text should be unchanged', null],
			[2, 'NotSame', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Test_text_alnum_4_purifier%$54#T$#BR-', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['DateInUserFormat', 'Same', date('Y-m-d'), date('Y-m-d'), 'Sample text should be unchanged', null],
			['DateInUserFormat', 'NotSame', date('Y.m.d'), date('Y.m.d'), 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['DateRangeUserFormat', 'Same', [date('Y-m-d'), date('Y-m-d', \strtotime('+1 day'))], date('Y-m-d') . ',' . date('Y-m-d', \strtotime('+1 day')), 'Sample text should be unchanged', null],
			['DateRangeUserFormat', 'NotSame', date('Y.m.d') . ',' . date('Y.m.d', \strtotime('+1 day')), date('Y.m.d') . ',' . date('Y.m.d', \strtotime('+1 day')), 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Date', 'Same', date('Y-m-d'), date('Y-m-d'), 'Sample text should be unchanged', null],
			['Date', 'NotSame', '201X-07-26', '201X-07-26', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Time', 'Same', date('H:i:s'), date('H:i:s'), 'Sample text should be unchanged', null],
			['Time', 'NotSame', '24:12:20', '24:12:20', 'Sample text should be unchanged', \App\Exceptions\IllegalValue::class],
			['TimeInUserFormat', 'Same', date('H:i'), date('H:i'), 'Sample text should be unchanged', null],
			['Bool', 'Same', true, true, 'Sample text should be unchanged', null],
			['Bool', 'NotSame', 'Test-text', 'Test-text', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['NumberInUserFormat', 'Same', 1234567890.0, '1234567890', 'Sample text should be unchanged and converted to decimal', null],
			['NumberInUserFormat', 'NotSame', '12345X7890', '12345X7890', 'Sample text should be purified', \App\Exceptions\IllegalValue::class],
			['Integer', 'Same', 1234, 1234, 'Sample integer should be unchanged', null],
			['Integer', 'NotSame', '12X4', '12X4', 'Sample integer should be purified', \App\Exceptions\IllegalValue::class],
			['Digital', 'Same', '43453453', '43453453', 'Sample number should be unchanged', null],
			['Digital', 'NotSame', '43453C53', '43453C53', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Color', 'Same', '#3A13F5', '#3A13F5', 'Sample number should be unchanged', null],
			['Color', 'NotSame', '#3A13FZ', '#3A13FZ', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Year', 'Same', date('Y'), date('Y'), 'Sample number should be unchanged', null],
			['Year', 'NotSame', '201X', '201X', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Text', 'Same', 'Test-text-string-for-purifier', 'Test-text-string-for-purifier', 'Sample number should be unchanged', null],
			['Text', 'NotSame', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['Default', 'Same', 'Test-text-string-for-purifier', 'Test-text-string-for-purifier', 'Sample number should be unchanged', null],
			['Default', 'NotSame', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!) &lt;svg/onabort=alert(3)//  <svg/onload=alert(1) onfocus=alert(2)//', 'Sample number should be purified', \App\Exceptions\IllegalValue::class],
			['MailId', 'Same', '<5FB2B5EF@xx.cc.it> (added by postmaster@cc.it)', '<5FB2B5EF@xx.cc.it> (added by postmaster@cc.it)', 'Sample text should be unchanged', null],
			['MailId', 'Same', '<30.123.12.JavaMail."admin.azure"@A-PROXY01>', '<30.123.12.JavaMail."admin.azure"@A-PROXY01>', 'Sample text should be unchanged', null],
			['MailId', 'Same', '<CAK01GN-UtTiM90_wQNB07OnE6aBm=w@mail.g.c>', '<CAK01GN-UtTiM90_wQNB07OnE6aBm=w@mail.g.c>', 'Sample text should be unchanged', null],
		];
	}

	/**
	 * Testing purify empty values.
	 */
	public function testEmptyValues()
	{
		$logToFile = \App\Log::$logToFile;
		\App\Log::$logToFile = false;
		$this->assertSame('', \App\Purifier::purify(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::purifyHtml(''), 'Empty text should be unchanged');
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes(''), 'Empty text should not throw exception');
		$this->assertSame('', \App\Purifier::purifySql(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::encodeHtml(''), 'Empty text should be unchanged');
		$this->assertSame('', \App\Purifier::decodeHtml(''), 'Empty text should be unchanged');
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$this->assertSame('', \App\Purifier::purifySql('', false), 'Empty text should be unchanged');
		\App\Log::$logToFile = $logToFile;
	}

	/**
	 * Testing purify text values.
	 */
	public function testTextValues()
	{
		$logToFile = \App\Log::$logToFile;
		\App\Log::$logToFile = false;
		$this->assertSame('Test text string for purifier', \App\Purifier::purify('Test text string for purifier'), 'Sample text should be unchanged');
		$this->assertSame('Test text string for purifier', \App\Purifier::purify('Test text string for purifier'), 'Sample text should be unchanged(cached)');
		$this->assertSame(['Test text string for purifier', 'Test text string for purifier'], \App\Purifier::purify(['Test text string for purifier', 'Test text string for purifier']), 'Sample text should be unchanged(array)');
		$this->assertSame('Test text string for purifier', \App\Purifier::purifyHtml('Test text string for purifier'), 'Sample text should be unchanged');
		$this->assertNull(\App\Purifier::purifyHtmlEventAttributes('Test text string for purifier'), 'Sample text should be unchanged');
		\App\Log::$logToFile = $logToFile;
	}

	/**
	 * @dataProvider dataProviderByType
	 *
	 * @param mixed       $type
	 * @param mixed       $assertion
	 * @param mixed       $expected
	 * @param mixed       $text
	 * @param string      $message
	 * @param string|null $exception
	 */
	public function testPurifyByType($type, $assertion, $expected, $text, string $message, ?string $exception): void
	{
		$logToFile = \App\Log::$logToFile;
		\App\Log::$logToFile = false;
		$assertion = 'assert' . $assertion;
		if ($exception) {
			$this->expectException($exception);
		}
		$this->{$assertion}($expected, \App\Purifier::purifyByType($text, $type), $message);
		\App\Log::$logToFile = $logToFile;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function purifyHtmlProviderFailure(): array
	{
		$rows = [];
		$file = \App\Fields\File::loadFromUrl('https://raw.githubusercontent.com/YetiForceCompany/YetiForceCRM-Tests/main/xss-payload.txt');
		$fileRows = explode("\n", $file->getContents());
		// $fileRows = explode("\n", file_get_contents('c:\www\YetiForceCRM-Tests\xss-payload.txt'));
		foreach ($fileRows as $row) {
			if ($row) {
				$rows[] = [$row];
			}
		}
		return $rows;
	}

	/**
	 * Testing html purifier failure.
	 *
	 * @dataProvider purifyHtmlProviderFailure
	 *
	 * @param string $text
	 */
	public function testPurifyHtmlFailure(string $text): void
	{
		$this->expectException(\App\Exceptions\IllegalValue::class);
		$logToFile = \App\Log::$logToFile;
		\App\Log::$logToFile = false;
		try {
			$purifyHtml = \App\Purifier::purifyHtml($text);
			if ($purifyHtml !== $text) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
			}
			throw new \Exception('Illegal value !!! ' . $text);
		} catch (\Throwable $th) {
			// echo \get_class($th);
			throw $th;
		}
		\App\Log::$logToFile = $logToFile;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function purifyHtmlProviderSuccess(): array
	{
		return [
			['<div>Test-text-string-for-purifier</div>', '<div>Test-text-string-for-purifier</div>', true],
			['ę€ółśążźćń23{}":?>><>?:"{}+_)', 'ę€ółśążźćń23{}":?&gt;&gt;&lt;&gt;?:"{}+_)', true],
			['ę€ółśążźćń23{}":?>><>?:"{}+_)(*&^%$#@!)', 'ę€ółśążźćń23{}":?&gt;&gt;&lt;&gt;?:"{}+_)(*&amp;^%$#@!)', true],
		];
	}

	/**
	 * Testing html purifier success.
	 *
	 * @dataProvider purifyHtmlProviderSuccess
	 *
	 * @param string $text
	 * @param string $expected
	 */
	public function testPurifyHtmlSuccess(string $text, string $expected): void
	{
		$this->assertSame($expected, \App\Purifier::purifyHtml($text), 'Sample text should be unchanged');
	}

	/**
	 * Restore current user preferences.
	 *
	 * @codeCoverageIgnore
	 *
	 * @throws \Exception
	 */
	public static function tearDownAfterClass(): void
	{
		$userModel = \Vtiger_Record_Model::getInstanceById(\App\User::getCurrentUserId(), 'Users');
		$userModel->set('currency_decimal_separator', self::$separatorDecimal);
		$userModel->set('currency_grouping_separator', self::$separatorGrouping);
		$userModel->set('currency_symbol_placement', self::$symbolPlacement);
		$userModel->set('currency_grouping_pattern', self::$patternGrouping);
		$userModel->set('no_of_currency_decimals', self::$decimalNum);
		$userModel->set('truncate_trailing_zeros', self::$truncateTrailingZeros);
		$userModel->set('hour_format', self::$hourFormat);
		$userModel->set('time_zone', self::$userTimeZone);
		$userModel->save();
		\date_default_timezone_set(self::$timeZone);
		parent::tearDownAfterClass();
	}
}
