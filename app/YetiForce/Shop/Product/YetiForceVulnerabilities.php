<?php
/**
 * YetiForce shop YetiForce Vulnerabilities file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Vulnerabilities class.
 */
class YetiForceVulnerabilities extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Vulnerabilities';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-vulnerabilities';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 5,
		'Small' => 12,
		'Medium' => 25,
		'Large' => 50,
		'Corporation' => 100,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceVulnerabilities')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceVulnerabilities');
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$links = [
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'Website',
				'relatedModuleName' => '_Base',
				'linkicon' => 'fas fa-globe',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => $this->website,
				'linkclass' => 'btn-info',
				'showLabel' => 1,
			]),
		];
		if (\App\Security\AdminAccess::isPermitted('Dav')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_DAV_KEYS',
				'relatedModuleName' => 'Settings:Dav',
				'linkicon' => 'yfi yfi-dav',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Dependencies&view=Vulnerabilities',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
