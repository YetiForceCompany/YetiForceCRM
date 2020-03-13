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
	 * {@inheritdoc}
	 */
	public $name = 'Domain';
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
		$this->allowedDomains = \Config\Security::$purifierAllowedDomains ?? [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter(&$uri, $config, $context)
	{
		$host = $uri->host;
		if (null === $uri->scheme) {
			$host = parse_url('xxx://' . $uri->path)['host'] ?? '';
		} elseif ('data' === $uri->scheme || 'mailto' === $uri->scheme || 'tel' === $uri->scheme) {
			return true;
		}
		if (false !== strpos($host, '.') && !\in_array($host, $this->allowedDomains)) {
			return false;
		}
		return true;
	}
}
