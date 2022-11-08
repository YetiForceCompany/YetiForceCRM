<?php
/**
 * Basic class to OAuth provider.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Integrations;

/**
 * Basic class to OAuth provider.
 */
class OAuth extends \App\Base
{
	/**
	 * Get providers.
	 *
	 * @return array
	 */
	public static function getProviders(): array
	{
		$providers = [];
		$iterator = new \DirectoryIterator(__DIR__ . '/OAuth');
		foreach ($iterator as $item) {
			if ($item->isFile() && 'AbstractProvider.php' !== $item->getFilename() && 'php' === $item->getExtension()
				&& $provider = self::getProviderByName($item->getBasename('.php'))
			) {
				$providers[$provider->getName()] = $provider;
			}
		}

		return $providers;
	}

	/**
	 * Get provider by name.
	 *
	 * @param string $name
	 *
	 * @return OAuth\Provider|null
	 */
	public static function getProviderByName(string $name): ?OAuth\AbstractProvider
	{
		$className = "\\App\\Integrations\\OAuth\\{$name}";
		return class_exists($className) ? new $className() : null;
	}
}
