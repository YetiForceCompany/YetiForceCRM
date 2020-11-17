<?php
/**
 * YetiForce shop YetiForceOutlook file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceOutlook class.
 */
class YetiForceOutlook extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Outlook Integration Panel';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-magento-integration';

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
	public function verify(): bool
	{
		if (\App\YetiForce\Register::getProducts('YetiForceOutlook')) {
			return \App\YetiForce\Shop::check('YetiForceOutlook');
		}
		return true;
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$links = [];
		if (\App\Security\AdminAccess::isPermitted('MailIntegration')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_MAIL_INTEGRATION',
				'relatedModuleName' => 'Settings:MailIntegration',
				'linkicon' => 'adminIcon-address',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=MailIntegration&view=Index',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
