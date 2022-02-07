<?php
/**
 * YetiForce shop PremiumSupport file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop PremiumSupport class.
 */
class YetiForceHelp extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Help';

	/** {@inheritdoc} */
	public $category = 'Support';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/marketplace/support';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 50,
		'Small' => 80,
		'Medium' => 200,
		'Large' => 400,
		'Corporation' => 800,
	];
	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		return [
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
	}
}
