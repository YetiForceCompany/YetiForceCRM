<?php
/**
 * Plugin file to check allowed domains in urls.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tkur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Extension\HTMLPurifier;

/**
 * Plugin class to check allowed domains in urls.
 */
class Domain extends \HTMLPurifier_URIFilter
{
	/** {@inheritdoc} */
	public $name = 'Domain';

	/** @var string[] Allowed domains. */
	private $allowedDomains = [];

	/** {@inheritdoc} */
	public function prepare($config)
	{
		$this->allowedDomains = \Config\Security::$purifierAllowedDomains ?? [];
	}

	/** {@inheritdoc} */
	public function filter(&$uri, $config, $context)
	{
		$host = $uri->host;
		if (null === $uri->scheme) {
			$host = parse_url('xxx://' . $uri->path)['host'] ?? '';
		} elseif ('data' === $uri->scheme || 'mailto' === $uri->scheme || 'tel' === $uri->scheme) {
			return true;
		}
		if (null !== $host && false !== strpos($host, '.') && false !== $this->allowedDomains && !\in_array($host, $this->allowedDomains)) {
			return false;
		}
		return true;
	}
}
