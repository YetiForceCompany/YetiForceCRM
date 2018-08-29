<?php

namespace App\SocialMedia;

/**
 * Interface for SocialMedia class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
interface SocialMediaInterface
{
	/**
	 * SocialMediaInterface constructor.
	 *
	 * @param string $userName
	 */
	public function __construct($userName);

	/**
	 * Is configured.
	 *
	 * @return bool
	 */
	public static function isConfigured();

	/**
	 * Retrieve data from Api.
	 */
	public function retrieveDataFromApi();
}
