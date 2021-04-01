<?php
/**
 * YetiForce shop YetiForce Widgets Premium file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce Widgets Premium class.
 */
class YetiForceWidgets extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Premium Widgets';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-widgets-premium';

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
		if (\App\YetiForce\Register::getProducts('YetiForceWidgets')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceWidgets');
		} else {
			$dashboardUpdates = (new \App\Db\Query())
				->from('vtiger_module_dashboard')
				->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard.linkid')
				->where(['vtiger_links.linkurl' => 'index.php?module=ModTracker&view=ShowWidget&name=Updates'])
				->exists();
			$pdfViewer = (new \App\Db\Query())->from('vtiger_widgets')->where(['type' => 'PDFViewer'])->exists();
			if ($dashboardUpdates || $pdfViewer) {
				$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
				$status = false;
			}
		}
		return ['status' => $status, 'message' => $message];
	}

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
