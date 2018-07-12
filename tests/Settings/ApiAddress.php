<?php
/**
 * ApiAddress test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class ApiAddress extends \Tests\Base
{
	/**
	 * Testing global config save.
	 */
	public function testSaveConfig()
	{
		$result = \Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->setConfig([
			'min_length' => 5, 'result_num' => 15, 'api_name' => 'global'
		]);
		$this->assertTrue($result);
	}

	/**
	 * Testing global config getter.
	 */
	public function testGetConfig()
	{
		$dataReference = ['min_length' => 5, 'result_num' => 15];
		$result = \Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->getConfig('global');
		$this->assertSame((int) $result['global']['min_length'], $dataReference['min_length']);
		$this->assertSame((int) $result['global']['result_num'], $dataReference['result_num']);
	}

	/**
	 * Reset to default values.
	 */
	public function testResetToDefault()
	{
		$result = \Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->setConfig([
			'min_length' => 3, 'result_num' => 10, 'api_name' => 'global'
		]);
		$this->assertTrue($result);
	}
}
