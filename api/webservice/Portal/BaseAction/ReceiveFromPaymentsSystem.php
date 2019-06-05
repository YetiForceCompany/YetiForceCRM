<?php
/**
 * The file contains: Class to handling payment information.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

/**
 * Class to handling payment information.
 */
class ReceiveFromPaymentsSystem extends \Api\Core\BaseAction
{
	/**
	 * Map of statuses.
	 */
	const PAYMENT_STATUS = [
		1 => 'Created',
		2 => 'Created',
		3 => 'Paid',
		4 => 'Denied',
	];

	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['PUT'];

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * Handling payment information.
	 *
	 * @return array
	 */
	public function put()
	{
		$request = $this->controller->request;
		$paymentSystem = $request->getByType('payment_system', 'Alnum');
		$transactionId = $request->getByType('transaction_id', 'Alnum');
		$paymentsInId = (new \App\Db\Query())
			->select(['paymentsinid'])
			->from('vtiger_paymentsin')
			->where(['transaction_id' => $transactionId])
			->andWhere(['payment_system' => $paymentSystem])
			->scalar();
		if ($paymentsInId) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($paymentsInId);
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('PaymentsIn');
		}
		$orderId = $request->getInteger('ssingleordersid');
		$recordModelOrder = \Vtiger_Record_Model::getInstanceById($orderId, 'SSingleOrders');
		$recordModel->set('assigned_user_id', $recordModelOrder->get('assigned_user_id'));
		$recordModel->set('relatedid', $recordModelOrder->get('accountid'));
		$recordModel->set('ssingleordersid', $orderId);
		$recordModel->set('paymentsin_status', static::PAYMENT_STATUS[$request->getInteger('paymentsin_status')]);
		$recordModel->set('transaction_id', $transactionId);
		$recordModel->set('paymentsvalue', $request->getByType('paymentsvalue', 'Double'));
		$recordModel->set('currency_id', \App\Fields\Currency::getCurrencyIdByCode($request->getByType('currency_id')));
		$recordModel->set('paymentstitle', $request->getByType('paymentstitle', 'Text'));
		$recordModel->set('payment_system', $paymentSystem);
		$recordModel->save();
		return ['id' => $recordModel->getId()];
	}
}
