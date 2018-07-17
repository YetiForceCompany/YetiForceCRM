<?php
/**
 * TextParser test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class TextParser extends \Tests\Base
{
	/**
	 * Test record id.
	 */
	private static $testRecordId;
	/**
	 * Test record module.
	 */
	private static $testRecordModule;
	/**
	 * Test record instance.
	 */
	private static $testInstanceRecord;
	/**
	 * Test clean instance.
	 */
	private static $testInstanceClean;
	/**
	 * Test clean instance with module.
	 */
	private static $testInstanceCleanModule;

	/**
	 * Create test records in database and set id/module variables.
	 *
	 * @throws \Exception
	 */
	private function populateTestRecords()
	{
		static::$testRecordModule = 'Leads';
		$recordModel = \Vtiger_Record_Model::getCleanInstance(static::$testRecordModule);
		$recordModel->set('description', 'autogenerated test lead for \App\TextParser tests');
		$recordModel->save();
		static::$testRecordId = $recordModel->getId();
	}

	/**
	 * Testing instances creation.
	 */
	public function testInstancesCreation()
	{
		if (!static::$testRecordId || !static::$testRecordModule) {
			$this->populateTestRecords();
		}
		static::$testInstanceClean = \App\TextParser::getInstance();
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceClean, 'Expected clean instance without module of \App\TextParser');
		static::$testInstanceCleanModule = \App\TextParser::getInstance('Leads');
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceCleanModule, 'Expected clean instance with module Leads of \App\TextParser');
		$this->assertInstanceOf('\App\TextParser', \App\TextParser::getInstanceById(static::$testRecordId, static::$testRecordModule), 'Expected instance from lead id and module string of \App\TextParser');
		static::$testInstanceRecord = \App\TextParser::getInstanceByModel(\Vtiger_Record_Model::getInstanceById(static::$testRecordId, static::$testRecordModule));
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceRecord, 'Expected instance from record model of \App\TextParser');
	}

	/**
	 * Testing clean instance based field placeholder replacement.
	 */
	public function testCIBasicFieldPlaceholder()
	{
		\App\User::setCurrentUserId(1);
		$text = '+ $(employee : last_name)$ +';
		$this->assertSame('+  +', static::$testInstanceClean
			->setContent($text)
			->parse()
			->getContent(), 'By default employee last name should be empty');
	}

	/**
	 * Testing RecordModel based instance field placeholder replacement.
	 */
	public function testRMIBasicFieldPlaceholder()
	{
		\App\User::setCurrentUserId(1);
		$text = '+ $(employee : last_name)$ +';
		$this->assertSame('+  +', static::$testInstanceRecord
			->setContent($text)
			->parse()
			->getContent(), 'By default employee last name should be empty');
	}
}
