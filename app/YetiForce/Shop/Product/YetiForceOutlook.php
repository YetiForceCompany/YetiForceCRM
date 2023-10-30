<?php
/**
 * YetiForce shop YetiForceOutlook file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceOutlook class.
 */
class YetiForceOutlook extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public function analyzeConfiguration(): array
	{
		return !\Settings_MailIntegration_Activate_Model::isActive('outlook') ? [
			'message' => \App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', 'Settings:MailIntegration', 'Outlook'),
			'type' => 'LBL_REQUIRES_INTERVENTION',
			'href' => 'index.php?parent=Settings&module=MailIntegration&view=Index',
		] : [];
	}

	/** {@inheritdoc} */
	public function isConfigured(): bool
	{
		return \Settings_MailIntegration_Activate_Model::isActive('outlook');
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
