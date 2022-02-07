<?php

/**
 * Vtiger ProductsServices widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_ProductsServices_Widget extends Vtiger_Basic_Widget
{
	public $allowedModules = ['Accounts'];

	public function getUrl()
	{
		$defaultModule = $this->Data['filter'] ?? 'Products';
		return 'module=Products&view=Widget&fromModule=' . $this->Module . '&record=' . $this->Record . "&mode=showProductsServices&page=1&mod={$defaultModule}&limit=" . $this->Data['limit'];
	}

	public function getWidget()
	{
		$this->Config['url'] = $this->getUrl();
		$this->Config['tpl'] = 'ProductsServicesBasic.tpl';

		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'ProductsServicesConfig';
	}
}
