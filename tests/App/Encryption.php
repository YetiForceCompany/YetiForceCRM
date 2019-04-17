<?php
/**
 * Encryption test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Tests\App;

/**
 * Class Encryption tests.
 */
class Encryption extends \Tests\Base
{
	/**
	 * Test encrypt data when encryption is disabled.
	 */
	public function testEncryptWithoutPass()
	{
		$instance = new \App\Encryption();
		$testText = 'TEST TEXT';
		$this->assertSame($testText, $instance->encrypt($testText), 'Encryption should be disabeld');
	}

	/**
	 * Test decrypt data when encryption is disabled.
	 */
	public function testDecryptWithoutPass()
	{
		$instance = new \App\Encryption();
		$testText = 'TEST TEXT';
		$this->assertSame($testText, $instance->decrypt($testText), 'Encryption should be disabeld');
	}

	public function testAvailableMethods()
	{
		$this->assertTrue(!empty(\App\Encryption::getMethods()), 'The system does not support any methods to encryption');
	}

	/**
	 * Provide test data for testEncryptionWithPass function.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function encryptionProvider()
	{
		return [
			['AES-256-CBC', '1234567890123456'],
			['AES-256-CTR', '1234567890123456'],
			['AES-192-CBC', '1234567890123456'],
			['AES-192-CTR', '1234567890123456'],
			['DES-EDE3-CBC', '12354678'],
			['DES-EDE3-CFB', '12354678']
		];
	}

	/**
	 * Testing process function.
	 *
	 * @dataProvider encryptionProvider
	 *
	 * @param string $method
	 * @param string $password
	 */
	public function testEncryptionWithPass(string $method, string $password)
	{
		\App\Config::set('securityKeys', 'encryptionMethod', $method);
		\App\Config::set('securityKeys', 'encryptionPass', $password);
		$instance = new \App\Encryption();
		$instance->set('method', $method);
		$instance->set('vector', $password);
		$instance->set('pass', \App\Config::securityKeys('encryptionPass'));
		$testText = 'TEST TEXT';
		$encryptText = $instance->encrypt($testText);
		$this->assertTrue(!empty($encryptText), 'Encryption is not available');
		$this->assertFalse($testText === $encryptText, 'Encryption is disabled');
		$this->assertSame($testText, $instance->decrypt($encryptText), 'Encryption is disabled');
	}
}
