<?php
/**
 * DynamicInventoryColumnsTable class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\TextParser;

class DynamicInventoryColumnsTable extends Base
{
	/** @var string Class name */
	public $name = 'LBL_DYNAMIC_INVENTORY_TABLE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		if (!$this->textParser->recordModel->getModule()->isInventory()) {
			return $html;
		}
		$columns = [];
		if (!empty($this->textParser->getParam('inventoryColumns'))) {
			$columns = $this->textParser->getParam('inventoryColumns');
		} else {
			$columns = \Vtiger_PDF_Model::getInventoryColumnsForRecord($this->textParser->recordModel->getId(), $this->textParser->recordModel->getModule()->getName());
		}
		return $this->textParser->getInventoryTable([
			'type' => 'table',
			'columns' => $columns,
			'href' => false,
		]);
	}
}
