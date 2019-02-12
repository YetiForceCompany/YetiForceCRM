<?php

namespace App\TextParser;

/**
 * Gross amount in words class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class GrossAmountInWords extends Base
{
	/** @var string Class name */
	public $name = 'LBL_GROSS_AMOUNT_IN_WORDS';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		if (!$this->textParser->recordModel || !$this->textParser->recordModel->getModule()->isInventory()) {
			return '';
		}
		$gross = 0;
		foreach ($this->textParser->recordModel->getInventoryData() as $inventoryRow) {
			if ($inventoryRow['gross']) {
				$gross += $inventoryRow['gross'];
			}
		}
		return \App\Custom\NumberToWords::process($gross);
	}
}
