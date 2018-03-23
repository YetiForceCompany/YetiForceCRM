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
		$configurator = \Settings_OSSMail_Config_Model::getCleanIntance();
		$configurator->set('product_name', 'YetiForce_Test');
		$configurator->set('default_host', ['ssl://imap.gmail.com', 'ssl://imap.YT_Test.com']);
		$configurator->save();
		$configuration = \Settings_OSSMail_Config_Model::getInstance();
		$this->assertSame($configuration->get('product_name'), $configurator->get('product_name'));
		$this->assertSame(array_values($configuration->get('default_host')), $configurator->get('default_host'));
	}
}
