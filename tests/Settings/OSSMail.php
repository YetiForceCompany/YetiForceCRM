<?php
/**
 * OSSMail test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace tests\Settings;

class OSSMail extends \Tests\Base
{
	/**
	 * Testing change configuration for Roundcube.
	 */
	public function testChangeConfig()
	{
		$configurator = new \App\ConfigFile('module', 'OSSMail');
		$configurator->set('des_key', 'YetiForce_Test');
		$configurator->set('imap_host', ['ssl://imap.mail.yahoo.com:993' => 'ssl://imap.mail.yahoo.com', 'ssl://imap.YT_Test.com:993' => 'ssl://imap.YT_Test.com']);
		$configurator->create();
		$this->assertSame('YetiForce_Test', \App\Config::module('OSSMail', 'des_key'));
		$this->assertCount(0, array_diff(\App\Config::module('OSSMail', 'imap_host'), ['ssl://imap.mail.yahoo.com:993' => 'ssl://imap.mail.yahoo.com', 'ssl://imap.YT_Test.com:993' => 'ssl://imap.YT_Test.com']));
	}
}
