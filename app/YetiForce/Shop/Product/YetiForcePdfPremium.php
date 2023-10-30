<?php
/**
 * YetiForce shop PDF Premium file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop PDF Premium class.
 */
class YetiForcePdfPremium extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritDoc} */
	public function getAlertMessage(bool $require = true): string
	{
		$message = parent::getAlertMessage();
		if (!$this->getStatus() && $this->isConfigured()) {
			$message = 'LBL_PAID_FUNCTIONALITY';
		} elseif (!$this->getStatus() && !$this->isConfigured()) {
			$message = '';
		}

		return $message;
	}

	/** {@inheritDoc} */
	public function isConfigured(): bool
	{
		return class_exists('HeadlessChromium\BrowserFactory') && !empty(\Config\Components\Pdf::$chromiumBinaryPath);
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
		if (\App\Security\AdminAccess::isPermitted('Companies')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_PDF',
				'relatedModuleName' => 'Settings:PDF',
				'linkicon' => 'adminIcon-modules-pdf-templates',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=PDF&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
