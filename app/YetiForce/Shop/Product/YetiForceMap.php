<?php
/**
 * YetiForce shop YetiForceMap file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceMap class.
 */
class YetiForceMap extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Map';

	/** {@inheritdoc} */
	public $category = 'Addons';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-map-en';

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
		if (\App\YetiForce\Register::getProducts('YetiForceMap')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceMap');
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
				'linkicon' => 'fas fa-globe mr-2',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => $this->website,
				'linkclass' => 'btn-info',
				'showLabel' => 1,
			]),
		];
		if (\App\Security\AdminAccess::isPermitted('Map')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_MAP',
				'relatedModuleName' => 'Settings:_Base',
				'linkicon' => 'far fa-map',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=Map&view=Config',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
