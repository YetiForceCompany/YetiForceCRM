<?php
/**
 * DynamicInventoryColumnsTable class.
 *
 * @package 	App
 *
 * @copyright	YetiForce Sp. z o.o.
 * @license		YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author 		Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author		Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

class DynamicInventoryColumnsTable extends Base
{
	/** @var string Class name */
	public $name = 'LBL_DYNAMIC_INVENTORY_TABLE';

	/** @var string Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		if ($this->textParser->recordModel->getModule()->isInventory()) {
			$columns = $this->textParser->getParam('pdf')->getVariable('inventoryColumns');
			$html = $this->textParser->getInventoryTable([
				'type' => 'table',
				'columns' => \App\Pdf\InventoryColumns::getInventoryColumnsForRecord($this->textParser->recordModel->getId(), $this->textParser->recordModel->getModuleName(), $columns),
				'href' => false,
			]);
		}
		return $html;
	}
}
