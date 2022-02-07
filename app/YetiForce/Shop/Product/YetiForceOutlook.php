<?php
/**
 * YetiForce shop YetiForceOutlook file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public $website = 'https://yetiforce.com/en/yetiforce-outlook-integration-panel-en';

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
		if (\App\YetiForce\Register::getProducts('YetiForceOutlook')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceOutlook');
		} else {
			$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
			$status = !\in_array('https://appsforoffice.microsoft.com', \Config\Security::$allowedScriptDomains)
			&& !\in_array('https://ajax.aspnetcdn.com', \Config\Security::$allowedScriptDomains);
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function analyzeConfiguration(): array
	{
		if (empty($this->expirationDate) || \Settings_MailIntegration_Activate_Model::isActive('outlook')) {
			return [];
		}
		return [
			'message' => \App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', 'Settings:MailIntegration', 'Outlook'),
			'type' => 'LBL_REQUIRES_INTERVENTION',
			'href' => 'index.php?parent=Settings&module=MailIntegration&view=Index',
		];
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

	/** {@inheritdoc} */
	public function getSwitchButton(): ?\Vtiger_Link_Model
	{
		$link = null;
		if (\App\Security\AdminAccess::isPermitted('MailIntegration')) {
			$link = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => \App\Language::translate('LBL_ACTIVATE_DEACTIVATE_SERVICE', 'Settings:YetiForce'),
				'linkdata' => [
					'url-on' => 'index.php?module=MailIntegration&parent=Settings&action=Activate&source=outlook&mode=activate',
					'url-off' => 'index.php?module=MailIntegration&parent=Settings&action=Activate&source=outlook&mode=deactivate',
					'confirm' => \App\Language::translate('LBL_ACTIVATE_DEACTIVATE_SERVICE_CONFIRM_DESC', 'Settings:YetiForce'),
				],
			]);
		}
		return $link;
	}

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return \Settings_MailIntegration_Activate_Model::isActive('outlook');
	}
}
