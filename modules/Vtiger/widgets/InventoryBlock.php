<?php
/**
 * Inventory block widget file.
 *
 * @package   Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Inventory block widget class.
 */
class Vtiger_InventoryBlock_Widget extends Vtiger_Basic_Widget
{
	/** {@inheritdoc} */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && $this->moduleModel->isInventory();
	}

	/**
	 * Get URL.
	 */
	public function getUrl()
	{
		return "module={$this->Module}&view=Detail&fromModule={$this->Module}&record={$this->Record}&mode=showInventoryEntries&page=1&limit=" . $this->Data['limit'] . '&fields=' . implode(',', $this->Data['relatedfields']);
	}

	/** {@inheritdoc} */
	public function getWidget()
	{
		$this->Config['url'] = $this->getUrl();
		if (!isset($this->Config['data']['relatedmodule'])) {
			$this->Config['data']['relatedmodule'] = \App\Module::getModuleId($this->Module);
		}
		return $this->Config;
	}

	/** {@inheritdoc} */
	public function getConfigTplName()
	{
		return 'InventoryBlockConfig';
	}
}
