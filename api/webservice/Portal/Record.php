<?php
/**
 * The file contains: Record class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal;

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
	 * @return null|float
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
}
