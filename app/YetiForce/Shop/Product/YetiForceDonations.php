<?php
/**
 * YetiForce shop Donations file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop Donations class.
 */
class YetiForceDonations extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'Donate / Support our project';
	/** {@inheritdoc} */
	public $category = 'Support';
	/** {@inheritdoc} */
	public $pricesType = 'manual';
	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function getPrice(): int
	{
		return \App\User::getNumberOfUsers();
	}

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
