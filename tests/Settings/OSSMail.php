<?php
/**
 * OSSMail test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$configurator->set('product_name', 'YetiForce_Test');
		$configurator->set('default_host', ['ssl://imap.gmail.com', 'ssl://imap.YT_Test.com']);
		$configurator->create();
		$this->assertSame('YetiForce_Test', \App\Config::module('OSSMail', 'product_name'));
		$this->assertCount(0, array_diff(\App\Config::module('OSSMail', 'default_host'), ['ssl://imap.gmail.com', 'ssl://imap.YT_Test.com']));
	}
}
