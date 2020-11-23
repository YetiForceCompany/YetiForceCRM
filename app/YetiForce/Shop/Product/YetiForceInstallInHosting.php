<?php
/**
 * YetiForce shop InstallInHosting file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop InstallInHosting class.
 */
class YetiForceInstallInHosting extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Hosting';

	/** {@inheritdoc} */
	public $category = 'CloudHosting';

	/** {@inheritdoc} */
	public $pricesType = 'selection';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/marketplace/hosting-en';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 45,
		'Small' => 85,
		'Medium' => 165,
		'Large' => 325,
		'Corporation' => 645,
	];

	/** {@inheritdoc} */
	public $customFields = [
		'subdomain' => [
			'label' => 'LBL_SHOP_DOMAIN_PREFIX',
			'type' => 'text',
			'append' => '.yetiforce.cloud',
			'validator' => 'required,custom[onlyLetterNumber],minSize[3],maxSize[20]'
		],
		'email' => [
			'label' => 'LBL_EMAIL',
			'type' => 'email',
			'info' => 'LBL_EMAIL_INFO',
			'validator' => 'required,funcCall[Vtiger_Email_Validator_Js.invokeValidation]'
		]
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
				'linkicon' => 'fas fa-globe',
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
