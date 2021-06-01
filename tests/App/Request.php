<?php
/**
 * Request test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Request extends \Tests\Base
{
	/**
	 * Testing object construction.
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('\App\Request', \App\Request::init());
	}

	/**
	 * Testing getAll function.
	 */
	public function testGetAll()
	{
		$this->assertIsArray(\App\Request::init()->getAll());
		$this->assertIsArray(\App\Request::init()->getAllRaw());
	}

	/**
	 * Testing getHeaders method.
	 */
	public function testGetHeaders()
	{
		$this->assertEmpty(\App\Request::init()->getHeaders(), 'In CLI mode should not return any headers');
	}
}
