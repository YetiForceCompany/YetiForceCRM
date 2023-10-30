<?php
/**
 * YetiForce register encryption file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce register class.
 */
final class Encryption
{
	/** @var string Default encryption method */
	public const DEFAULT_METHOD = 'AES-128-CBC';

	/**
	 * Data encryption function.
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public function encrypt(array $data): string
	{
		return (new \App\Encryption($this->getConfig()))->encrypt(\App\Json::encode($data), true);
	}

	/**
	 * Data decryption function.
	 *
	 * @param string $data
	 *
	 * @return array
	 */
	public function decrypt(string $data): array
	{
		return \App\Json::decode((new \App\Encryption($this->getConfig()))->decrypt($data, true)) ?: [];
	}

	/**
	 * Get default configuration data for encryption.
	 *
	 * @return array
	 */
	public function getDefaultData(): array
	{
		return [
			'method' => self::DEFAULT_METHOD,
			'vector' => base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::DEFAULT_METHOD))),
			'pass' => \App\Encryption::generatePassword(10),
		];
	}

	/**
	 * Configuration data for encryption.
	 *
	 * @return array
	 */
	private function getConfig(): array
	{
		$data = (new Config())->getData();
		unset($data['key']);
		$iv = $data['vector'] ?? '';
		if ($iv) {
			$data['vector'] = base64_decode($iv);
		}

		return $data;
	}
}
