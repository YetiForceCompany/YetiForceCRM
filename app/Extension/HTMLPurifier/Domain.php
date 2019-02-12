<?php

namespace App\Extension\HTMLPurifier;

/**
 * Plugin to check allowed domains in urls.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tkur@yetiforce.com>
 */
class Domain extends \HTMLPurifier_URIFilter
{
	/**
	 * Allowed domains.
	 *
	 * @var string[]
	 */
	private $allowedDomains = [];

	/**
	 * {@inheritdoc}
	 */
	public function prepare($config)
	{
		$this->allowedDomains = \AppConfig::security('PURIFIER_ALLOWED_DOMAINS') ?: $this->allowedDomains;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter(&$uri, $config, $context)
	{
		if ($uri->scheme === 'data') {
			return true;
		}
		if (!in_array($uri->host, $this->allowedDomains)) {
			return false;
		}
		return true;
	}
}
