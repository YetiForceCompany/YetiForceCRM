<?php
/**
 * YetiForce test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

/**
 * Class YetiForce tests.
 */
class YetiForce extends \Tests\Base
{
	/**
	 * Testing status getAll method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testStatusGetAll()
	{
		$this->assertCount(\count(\App\YetiForce\Status::$variables), \App\YetiForce\Status::getAll());
	}
}
