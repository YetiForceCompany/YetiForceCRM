<?php
/**
 * YetiForce shop YetiForceInstallPortalInCloud file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceInstallPortalInCloud class.
 */
class YetiForceInstallPortalInCloud extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Portal Cloud';

	/** {@inheritdoc} */
	public $category = 'CloudHosting';

	/** {@inheritdoc} */
	public $pricesType = 'selection';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/marketplace/portal-in-the-cloud';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 65,
		'Small' => 125,
		'Medium' => 245,
		'Large' => 485,
		'Corporation' => 965,
	];

	/** {@inheritdoc} */
	public $customFields = [
		'subdomain' => [
			'label' => 'LBL_SHOP_DOMAIN_PREFIX',
			'type' => 'text',
			'append' => '.yetiforce.eu',
			'validator' => 'required,custom[onlyLetterNumber],minSize[3],maxSize[20]',
		],
		'email' => [
			'label' => 'LBL_EMAIL',
			'type' => 'email',
			'info' => 'LBL_EMAIL_INFO',
			'validator' => 'required,funcCall[Vtiger_Email_Validator_Js.invokeValidation]',
		],
	];

	/** {@inheritdoc} */
	public $companyDataForm = false;

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
