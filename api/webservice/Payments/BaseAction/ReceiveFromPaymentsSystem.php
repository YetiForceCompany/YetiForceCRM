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

namespace Api\Payments\BaseAction;

/**
 * Class to handling payment information.
 */
class ReceiveFromPaymentsSystem extends \Api\Core\BaseAction
{
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
		if (!\App\Fields\Picklist::isExists('payment_system', $paymentSystem)) {
			throw new \Api\Core\Exception('Unknown payment system');
		}
		$transactionId = $request->getByType('transaction_id', 'Alnum');
		$orderId = $request->getInteger('ssingleordersid');
		$recordModelOrder = \Vtiger_Record_Model::getInstanceById($orderId, 'SSingleOrders');
		$userId = (int) $recordModelOrder->get('assigned_user_id');
		$queryGenerator = new \App\QueryGenerator('PaymentsIn', $userId);
		$queryGenerator->setFields(['paymentsinid']);
		$queryGenerator->addCondition('transaction_id', $transactionId, 'e');
		$queryGenerator->addCondition('payment_system', $paymentSystem, 'e');
		$paymentsInId = $queryGenerator->createQuery()->limit(1)->scalar();
		if ($paymentsInId) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($paymentsInId, 'PaymentsIn');
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('PaymentsIn');
			$recordModel->set('assigned_user_id', $userId);
			$recordModel->set('relatedid', $recordModelOrder->get('accountid'));
			$recordModel->set('ssingleordersid', $orderId);
			$recordModel->set('transaction_id', $transactionId);
			$recordModel->set('paymentsvalue', $request->getByType('paymentsvalue', 'Double'));
			$recordModel->set('paymentscurrency', \App\Fields\Currency::getIdByCode($request->getByType('currency_id')));
			$recordModel->set('paymentstitle', $request->getByType('paymentstitle', 'Text'));
			$recordModel->set('payment_system', $paymentSystem);
		}
		$recordModel->set('paymentsin_status', $request->getByType('paymentsin_status', 'Alnum'));
		$recordModel->save();
		return ['id' => $recordModel->getId()];
	}
}
