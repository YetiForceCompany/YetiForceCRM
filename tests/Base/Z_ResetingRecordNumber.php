<?php
/**
 * Reseting record number test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace Tests\Base;

/**
 * Class RecordNumber.
 *
 * @codeCoverageIgnore
 */
class RecordNumber extends \App\Fields\RecordNumber
{
	/**
	 * @var array of dates
	 */
	public static $dates = [
		'2015-01-01',
		'2015-03-03',
		'2015-03-03',
		'2015-03-03',
		'2015-03-04',
		'2015-03-04',
		'2015-03-05',
		'2015-11-09',
		'2015-11-10',
		'2015-11-11',
		'2015-11-28',
		'2016-11-29',
		'2017-03-15',
		'2017-03-18',
		'2017-07-19',
		'2018-01-01',
		'2018-01-02',
		'2018-01-02',
		'2018-02-03',
		'2018-05-05'
	];

	/**
	 * @var int
	 */
	public static $currentDateIndex = 0;

	/**
	 * Date method mock for testing purposes.
	 *
	 * @param string   $format
	 * @param int|null $time
	 *
	 * @return false|string
	 */
	public static function date($format, $time = null)
	{
		if (!isset(self::$dates[self::$currentDateIndex])) {
			self::$currentDateIndex = 0;
		}
		return date($format, strtotime(self::$dates[self::$currentDateIndex]));
	}
}

class Z_ResetingRecordNumber extends \Tests\Base
{
	/**
	 * Database transaction pointer.
	 *
	 * @var \yii\db\Transaction
	 */
	private static $transaction;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass(): void
	{
		static::$transaction = \App\Db::getInstance()->beginTransaction();
	}

	/**
	 * Test method "DateMock".
	 */
	public function testDateMock()
	{
		$this->assertCount(20, RecordNumber::$dates);
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$this->assertSame($date, RecordNumber::date('Y-m-d'));
		}
	}

	/**
	 * Test method "StandardNumber".
	 */
	public function testSequenceNumber()
	{
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$this->assertSame($parts[0], RecordNumber::getSequenceNumber('Y'));
			$this->assertSame($parts[0] . $parts[1], RecordNumber::getSequenceNumber('M'));
			$this->assertSame($parts[0] . $parts[1] . $parts[2], RecordNumber::getSequenceNumber('D'));
		}
	}

	/**
	 * Test method "IncrementNumberStandard".
	 */
	public function testIncrementNumberStandard()
	{
		$instance = RecordNumber::getInstance('FInvoice')->set('prefix', 'F-I')->set('cur_id', 1);
		$instance->save();
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => \App\Module::getModuleId('FInvoice')])->one();
		$this->assertSame('F-I', $originalRow['prefix']);
		$this->assertSame('', $originalRow['postfix']);
		$this->assertSame(null, $originalRow['reset_sequence']);
		$this->assertSame('', $originalRow['cur_sequence']);
		$actualNumber = $originalRow['cur_id'];
		foreach (RecordNumber::$dates as $index => $date) {
			$this->assertSame("F-I$actualNumber", $instance->getIncrementNumber());
			$number = RecordNumber::getInstance('FInvoice');
			++$actualNumber;
			$this->assertSame($actualNumber, $number->get('cur_id'));
			$this->assertSame(null, $number->get('reset_sequence'));
			$this->assertSame('', $number->get('cur_sequence'));
			$this->assertSame('F-I', $number->get('prefix'));
			$this->assertSame('', $number->get('postfix'));
		}
	}

	/**
	 * Test method "Parse".
	 * Test parsing method for record numbers on different dates.
	 */
	public function testParse()
	{
		$instance = RecordNumber::getInstance('FInvoice');
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$instance->set('prefix', '{{DD}}/');
			$this->assertSame($parts[2] . '/1', $instance->parseNumber(1));
			$instance->set('prefix', '{{MM}}/');
			$this->assertSame($parts[1] . '/1', $instance->parseNumber(1));
			$instance->set('prefix', '{{YYYY}}/');
			$this->assertSame($parts[0] . '/1', $instance->parseNumber(1));
		}
	}

	/**
	 * Test method "IncrementNumberDay".
	 * Test record number resetting with new day.
	 */
	public function testIncrementNumberDay()
	{
		$parts = explode('-', RecordNumber::$dates[0]);
		$actualNumber = 1;
		$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
		$postfix = '';
		$resetSequence = 'D';
		$curSequence = ($parts[0] . $parts[1] . $parts[2]);
		$instance = RecordNumber::getInstance('FInvoice');
		$instance->set('prefix', $prefix);
		$instance->set('cur_id', $actualNumber);
		$instance->set('postfix', $postfix);
		$instance->set('leading_zeros', 0);
		$instance->set('reset_sequence', $resetSequence);
		$instance->set('cur_sequence', $curSequence);
		$result = $instance->save();
		$this->assertSame(1, $result);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame($prefix, $originalRow['prefix']);
		$this->assertSame(0, $originalRow['leading_zeros']);
		$this->assertSame($postfix, $originalRow['postfix']);
		$this->assertSame($resetSequence, $originalRow['reset_sequence']);
		$this->assertSame($curSequence, $originalRow['cur_sequence']);
		$this->assertSame($actualNumber, $originalRow['cur_id']);
		$currentNumber = 1;
		$currentDate = '';
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$sequence = str_replace('-', '', $date);
			if ($sequence === $currentDate) {
				++$currentNumber;
			} else {
				$currentNumber = 1;
				$currentDate = $sequence;
			}
			$this->assertSame("$date/$currentNumber", $instance->getIncrementNumber());
			$number = RecordNumber::getInstance('FInvoice');
			$this->assertSame($currentNumber + 1, $number->get('cur_id'));
			$this->assertSame(0, $number->get('leading_zeros'));
			$this->assertSame($resetSequence, $number->get('reset_sequence'));
			$this->assertSame($sequence, $number->get('cur_sequence'));
			$this->assertSame($prefix, $number->get('prefix'));
			$this->assertSame($postfix, $number->get('postfix'));
		}
	}

	/**
	 * Test method "IncrementNumberMonth".
	 * Test record number resetting with new month.
	 */
	public function testIncrementNumberMonth()
	{
		$actualNumber = 1;
		$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
		$postfix = '';
		$resetSequence = 'M';
		$curSequence = '';
		$instance = RecordNumber::getInstance('FInvoice');
		$instance->set('prefix', $prefix);
		$instance->set('cur_id', $actualNumber);
		$instance->set('postfix', $postfix);
		$instance->set('leading_zeros', 0);
		$instance->set('reset_sequence', $resetSequence);
		$instance->set('cur_sequence', $curSequence);
		$result = $instance->save();
		$this->assertSame(1, $result);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame($prefix, $originalRow['prefix']);
		$this->assertSame(0, $originalRow['leading_zeros']);
		$this->assertSame($postfix, $originalRow['postfix']);
		$this->assertSame($resetSequence, $originalRow['reset_sequence']);
		$this->assertSame($curSequence, $originalRow['cur_sequence']);
		$this->assertSame($actualNumber, $originalRow['cur_id']);
		$currentNumber = 1;
		$currentDate = '';
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$sequence = $parts[0] . $parts[1];
			if ($sequence === $currentDate) {
				++$currentNumber;
			} else {
				$currentNumber = 1;
				$currentDate = $sequence;
			}
			$this->assertSame("$date/$currentNumber", $instance->getIncrementNumber());
			$number = RecordNumber::getInstance('FInvoice');
			$this->assertSame($currentNumber + 1, $number->get('cur_id'));
			$this->assertSame(0, $number->get('leading_zeros'));
			$this->assertSame($resetSequence, $number->get('reset_sequence'));
			$this->assertSame($sequence, $number->get('cur_sequence'));
			$this->assertSame($prefix, $number->get('prefix'));
			$this->assertSame($postfix, $number->get('postfix'));
		}
	}

	/**
	 * Test method "IncrementNumberYear".
	 * Test record number resetting with new year.
	 */
	public function testIncrementNumberYear()
	{
		$actualNumber = 1;
		$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
		$postfix = '';
		$resetSequence = 'Y';
		$curSequence = '';
		$instance = RecordNumber::getInstance('FInvoice');
		$instance->set('prefix', $prefix);
		$instance->set('cur_id', $actualNumber);
		$instance->set('postfix', $postfix);
		$instance->set('leading_zeros', 0);
		$instance->set('reset_sequence', $resetSequence);
		$instance->set('cur_sequence', $curSequence);
		$result = $instance->save();
		$this->assertSame(1, $result);
		$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
		$this->assertSame($prefix, $originalRow['prefix']);
		$this->assertSame(0, $originalRow['leading_zeros']);
		$this->assertSame($postfix, $originalRow['postfix']);
		$this->assertSame($resetSequence, $originalRow['reset_sequence']);
		$this->assertSame($curSequence, $originalRow['cur_sequence']);
		$this->assertSame($actualNumber, $originalRow['cur_id']);
		$currentNumber = 1;
		$currentDate = '';
		foreach (RecordNumber::$dates as $index => $date) {
			RecordNumber::$currentDateIndex = $index;
			$parts = explode('-', $date);
			$sequence = $parts[0];
			if ($sequence === $currentDate) {
				++$currentNumber;
			} else {
				$currentNumber = 1;
				$currentDate = $sequence;
			}
			$this->assertSame("$date/$currentNumber", $instance->getIncrementNumber());
			$number = RecordNumber::getInstance('FInvoice');
			$this->assertSame($currentNumber + 1, $number->get('cur_id'));
			$this->assertSame(0, $number->get('leading_zeros'));
			$this->assertSame($resetSequence, $number->get('reset_sequence'));
			$this->assertSame($sequence, $number->get('cur_sequence'));
			$this->assertSame($prefix, $number->get('prefix'));
			$this->assertSame($postfix, $number->get('postfix'));
		}
	}

	/**
	 * Test method "LeadingZeros"
	 * Test leading zeros in numbers generation.
	 */
	public function testLeadingZeros()
	{
		for ($leadingZeros = 0; $leadingZeros < 7; ++$leadingZeros) {
			$actualNumber = 1;
			$prefix = '{{YYYY}}-{{MM}}-{{DD}}/';
			$postfix = '';
			$resetSequence = 'Y';
			$curSequence = '';
			$instance = RecordNumber::getInstance('FInvoice');
			$instance->set('prefix', $prefix);
			$instance->set('cur_id', $actualNumber);
			$instance->set('postfix', $postfix);
			$instance->set('leading_zeros', $leadingZeros);
			$instance->set('reset_sequence', $resetSequence);
			$instance->set('cur_sequence', $curSequence);
			$result = $instance->save();
			$this->assertSame(1, $result);
			$originalRow = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => 95])->one();
			$this->assertSame($prefix, $originalRow['prefix']);
			$this->assertSame($leadingZeros, $originalRow['leading_zeros']);
			$this->assertSame($postfix, $originalRow['postfix']);
			$this->assertSame($resetSequence, $originalRow['reset_sequence']);
			$this->assertSame($curSequence, $originalRow['cur_sequence']);
			$this->assertSame($actualNumber, $originalRow['cur_id']);
			$currentNumber = 1;
			$currentDate = '';
			foreach (RecordNumber::$dates as $index => $date) {
				RecordNumber::$currentDateIndex = $index;
				$parts = explode('-', $date);
				$sequence = $parts[0];
				if ($sequence === $currentDate) {
					++$currentNumber;
				} else {
					$currentNumber = 1;
					$currentDate = $sequence;
				}
				$currentNumber = \str_pad($currentNumber, $leadingZeros, '0', \STR_PAD_LEFT);
				$this->assertSame("$date/$currentNumber", $instance->getIncrementNumber());
				$number = RecordNumber::getInstance('FInvoice');
				$this->assertSame($currentNumber + 1, $number->get('cur_id'));
				$this->assertSame($leadingZeros, $number->get('leading_zeros'));
				$this->assertSame($resetSequence, $number->get('reset_sequence'));
				$this->assertSame($sequence, $number->get('cur_sequence'));
				$this->assertSame($prefix, $number->get('prefix'));
				$this->assertSame($postfix, $number->get('postfix'));
			}
		}
	}

	/**
	 * @codeCoverageIgnore
	 * Cleaning after tests.
	 */
	public static function tearDownAfterClass()
	{
		static::$transaction->rollBack();
		\App\Cache::clear();
	}
}
