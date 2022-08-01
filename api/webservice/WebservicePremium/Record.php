<?php
/**
 * The file contains: Record class.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium;

/**
 * Class Record.
 */
class Record
{
	/**
	 * Get the price from the pricebook.
	 *
	 * @param int $accountId
	 * @param int $productId
	 *
	 * @return float|null
	 */
	public static function getPriceFromPricebook(int $accountId, int $productId): ?float
	{
		$returnVal = (new \App\Db\Query())
			->select(['vtiger_pricebookproductrel.listprice'])
			->from('vtiger_account')
			->leftJoin('vtiger_pricebookproductrel', 'vtiger_pricebookproductrel.pricebookid = vtiger_account.pricebook_id')
			->where(['vtiger_account.accountid' => $accountId])
			->andWhere(['vtiger_pricebookproductrel.productid' => $productId])
			->scalar();
		return false === $returnVal ? null : (float) $returnVal;
	}

	/**
	 * Returns taxparam.
	 *
	 * @param string $availableTaxes
	 * @param string $groupTaxes
	 * @param string $regionalTaxes
	 *
	 * @return array
	 */
	public static function getTaxParam(string $availableTaxes, string $groupTaxes, string $regionalTaxes): array
	{
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$globalTaxes = \Vtiger_Inventory_Model::getGlobalTaxes();
		$taxParam = [];
		$availableTaxes = explode(' |##| ', $availableTaxes);
		if (\in_array('LBL_REGIONAL_TAX', $availableTaxes) && !empty($regionalTaxes) && \in_array(3, $taxConfig['taxs'])) {
			$taxParam['aggregationType'][] = 'regional';
			$taxId = explode(',', $regionalTaxes)[0];
			$taxParam['regionalTax'] = \App\Fields\Double::formatToDb($globalTaxes[$taxId]['value']);
		}
		if (\in_array('LBL_GROUP_TAX', $availableTaxes) && !empty($groupTaxes) && \in_array(1, $taxConfig['taxs'])) {
			$taxParam['aggregationType'][] = 'group';
			$taxId = explode(',', $groupTaxes)[0];
			$taxParam['groupTax'] = \App\Fields\Double::formatToDb($globalTaxes[$taxId]['value']);
		}
		return $taxParam;
	}
}
