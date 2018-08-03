<?php
/**
 * Zip test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Zip extends \Tests\Base
{
	/**
	 * Testing instance from file with no file name provided.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testInstanceOpenNoFileName()
	{
		$this->expectException(\App\Exceptions\AppException::class);
		\App\Zip::openFile(false);
	}

	/**
	 * Testing instance from not existing file.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testInstanceOpenFileNotExists()
	{
		$this->expectException(\App\Exceptions\AppException::class);
		\App\Zip::openFile('tests/data/NxFile.zip');
	}

	/**
	 * Testing instance from linux generated zip file.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testInstanceOpenLinuxFile()
	{
		$instanceOpen = \App\Zip::openFile('tests/data/TestLinux.zip');
		$this->assertInstanceOf('\App\Zip', $instanceOpen, 'Expected zip object instance');
	}
}
