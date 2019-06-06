<?php

/**
 * Product field map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

class Product extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = ['ean' => 'sku', 'productname' => 'name', 'unit_price' => 'price'];
	/**
	 * {@inheritdoc}
	 */
	public static $nonEditableFields = ['ean' => 'sku'];

	/**
	 * Method to get sku or name if ean does not exist.
	 *
	 * @return string
	 */
	public function getSku()
	{
		return !empty($this->dataCrm['ean']) ? $this->dataCrm['ean'] : \App\TextParser::textTruncate($this->dataCrm['productname'], 60, false);
	}
}
