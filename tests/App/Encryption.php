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
			['aes-256-cbc', '1234567890123456'],
			['aes-256-ctr', '1234567890123456'],
			['aes-192-cbc', '1234567890123456'],
			['aes-192-ctr', '1234567890123456'],
			['des-ede3-cbc', '12354678'],
			['des-ede3-cfb', '12354678']
		];
	}

	/**
	 * Testing process function.
	 *
	 * @param string $method
	 * @param string $password
	 *
	 * @dataProvider encryptionProvider
	 */
	public function testEncryptionWithPass(string $method, string $password)
	{
		\App\Config::set('securityKeys', 'encryptionMethod', $method);
		\App\Config::set('securityKeys', 'encryptionPass', $password);
		$instance = new \App\Encryption();
		$instance->set('method', $method);
		$instance->set('vector', $password);
		$instance->set('pass', \App\Config::securityKeys('encryptionPass'));
		if (!$instance->isActive()) {
			echo 'function_exists(\'openssl_encrypt\'): ' . var_export(\function_exists('openssl_encrypt'), true) . PHP_EOL;
			echo 'isEmpty(\'method\'): ' . var_export($instance->isEmpty('method'), true) . PHP_EOL;
			echo 'method !== : securityKeys(\'encryptionMethod\'): ' . var_export($instance->get('method') !== \App\Config::securityKeys('encryptionMethod'), true) . PHP_EOL;
			echo 'method in getMethods: ' . var_export(\in_array($instance->get('method'), \App\Encryption::getMethods()), true) . PHP_EOL;
			if (!\in_array($instance->get('method'), \App\Encryption::getMethods())) {
				echo 'Methods:  ' . \App\Utils::varExport(\App\Encryption::getMethods()) . PHP_EOL;
			}
		}
		$this->assertTrue($instance->isActive(), 'The encryption mechanism is not active');
		$testText = 'TEST TEXT';
		$encryptText = $instance->encrypt($testText);
		$this->assertTrue(!empty($encryptText), 'Encryption is not available');
		$this->assertFalse($testText === $encryptText, 'Encryption is not working');
		$this->assertSame($testText, $instance->decrypt($encryptText), 'The decrypted text does not match the encrypted text');
	}
}
