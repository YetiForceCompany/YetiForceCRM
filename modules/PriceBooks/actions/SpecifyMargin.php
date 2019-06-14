<?php
/**
 * The file contains: Class for setting margins.
 *
 * @package
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class for setting margins.
 */
class PriceBooks_SpecifyMargin_Action extends Vtiger_RelationAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$margin = $request->getByType('margin', 'Double');
		$recordId = $request->getInteger('record');
		$queryGenerator = static::getQuery($request);
		$queryGenerator->setField('purchase');
		$currencyId = Vtiger_Record_Model::getInstanceById($recordId, 'PriceBooks')->get('currency_id');
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!\App\Json::isEmpty($row['purchase'])) {
				$purchase = \App\Json::decode($row['purchase']);
				$price = ((100.00 + $margin) / 100.00) * (float) $purchase['currencies'][$currencyId]['price'];
				$dbCommand->update('vtiger_pricebookproductrel', ['listprice' => $price], ['productid' => $row['id']])->execute();
			}
		}
		$dataReader->close();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
