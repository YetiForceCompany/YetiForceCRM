<?php
/**
 * EmailParser test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class EmailParser extends \Tests\Base
{
	/**
	 * Test record instance.
	 *
	 * @var \App\EmailParser
	 */
	private static $parserRecord;
	/**
	 * Test clean instance.
	 *
	 * @var \App\EmailParser
	 */
	private static $parserClean;
	/**
	 * Test clean instance with module.
	 *
	 * @var \App\EmailParser
	 */
	private static $parserCleanModule;

	/**
	 * Testing instances creation.
	 */
	public function testInstancesCreation()
	{
		self::$parserClean = \App\EmailParser::getInstance();
		$this->assertInstanceOf('\App\EmailParser', self::$parserClean, 'Expected clean instance without module of \App\EmailParser');

		self::$parserCleanModule = \App\EmailParser::getInstance('Leads');
		$this->assertInstanceOf('\App\EmailParser', self::$parserCleanModule, 'Expected clean instance with module Leads of \App\EmailParser');

		$this->assertInstanceOf('\App\EmailParser', \App\EmailParser::getInstanceById(\Tests\Base\C_RecordActions::createLeadRecord()->getId(), 'Leads'), 'Expected instance from lead id and module string of \App\TextParser');

		self::$parserRecord = \App\EmailParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord());
		$this->assertInstanceOf('\App\EmailParser', self::$parserRecord, 'Expected instance from record model of \App\EmailParser');
	}

	/**
	 * Tests empty content condition.
	 */
	public function testEmptyContent()
	{
		$this->assertSame('', self::$parserClean
			->setContent('')
			->parse()
			->getContent(), 'Clean instance: empty content should return empty result');
	}

	/**
	 * Testing get content function.
	 */
	public function testGetContent()
	{
		$this->assertSame(['test0@yetiforce.com', 'test1@yetiforce.com' => 'Test One ', 'test2@yetiforce.com'], self::$parserClean
			->setContent('test0@yetiforce.com,Test One &lt;test1@yetiforce.com&gt;,test2@yetiforce.com,-,')
			->parse()
			->getContent(true), 'Clean instance: content should be equal');
	}

	/**
	 * Testing use value function.
	 */
	public function testUseValue()
	{
		$this->assertSame(['test0@yetiforce.com', 'test1@yetiforce.com' => 'Test One ', 'test2@yetiforce.com'], \App\EmailParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord())
			->setContent('test0@yetiforce.com,Test One &lt;test1@yetiforce.com&gt;,test2@yetiforce.com,-,,$(record : email)$')
			->parse()
			->getContent(true), 'content should be equal');

		\Tests\Base\C_RecordActions::createLeadRecord()->set('email', 'test3@yetiforce.com');
		\Tests\Base\C_RecordActions::createLeadRecord()->save();
		$this->assertSame(['test0@yetiforce.com', 'test1@yetiforce.com' => 'Test One ', 'test2@yetiforce.com'], \App\EmailParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord())
			->setContent('test0@yetiforce.com,Test One &lt;test1@yetiforce.com&gt;,test2@yetiforce.com,-,,$(record : email)$')
			->parse()
			->getContent(true), 'content should be equal');

		\Tests\Base\C_RecordActions::createLeadRecord()->set('noapprovalemails', '1');
		\Tests\Base\C_RecordActions::createLeadRecord()->save();
		$this->assertSame(['test0@yetiforce.com', 'test1@yetiforce.com' => 'Test One ', 'test2@yetiforce.com', 'test3@yetiforce.com'], \App\EmailParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord())
			->setContent('test0@yetiforce.com,Test One &lt;test1@yetiforce.com&gt;,test2@yetiforce.com,-,,$(record : email)$')
			->parse()
			->getContent(true), 'content should be equal');
		$tmpInstance = \App\EmailParser::getInstanceByModel(\Tests\Base\C_RecordActions::createLeadRecord());
		$tmpInstance->emailoptout = false;
		$this->assertSame(['test0@yetiforce.com', 'test1@yetiforce.com' => 'Test One ', 'test2@yetiforce.com', 'test3@yetiforce.com'], $tmpInstance->setContent('test0@yetiforce.com,Test One &lt;test1@yetiforce.com&gt;,test2@yetiforce.com,-,,$(record : email)$')
			->parse()
			->getContent(true), 'content should be equal');
		\Tests\Base\C_RecordActions::createLeadRecord(false);
	}
}
