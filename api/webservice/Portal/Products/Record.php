<?php
/**
 * The file contains: Get record detail class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\Products;

/**
 * Get record detail class.
 */
class Record extends \Api\Portal\BaseModule\Record
{
	/**
	 * {@inheritdoc}
	 */
	public function get(): array
	{
		$response = parent::get();
		$recordId = $this->controller->request->getInteger('record');
		$pricebookId = $this->getPricebookId();
		if ($pricebookId) {
			$price = \Vtiger_Record_Model::getInstanceById($pricebookId)->getProductsListPrice($recordId);
			if (null !== $price && false !== $price) {
				if (isset($response['rawData'])) {
					$response['rawData']['unit_price'] = (float) $price;
				}
				$response['data']['unit_price'] = \CurrencyField::convertToUserFormatSymbol((float) $price);
			}
		}
		return $response;
	}
}
