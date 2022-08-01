<?php
/**
 * The file contains: Class to handling payment information.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Payments\BaseAction;

/**
 * Class to handling payment information.
 */
class ReceiveFromPaymentsSystem extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
	}

	/**
	 * Handling payment information.
	 *
	 * @throws \Api\Core\Exception
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
		$paymentsInId = $queryGenerator->createQuery()->scalar();
		if ($paymentsInId) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($paymentsInId, 'PaymentsIn');
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('PaymentsIn');
			$recordModel->set('assigned_user_id', $userId);
			$recordModel->set('relatedid', $recordModelOrder->get('accountid'));
			$recordModel->set('ssingleordersid', $orderId);
			$recordModel->set('transaction_id', $transactionId);
			$recordModel->set('paymentsvalue', $request->getByType('paymentsvalue', 'Double'));
			$recordModel->set('currency_id', \App\Fields\Currency::getIdByCode($request->getByType('currency_id')));
			$recordModel->set('paymentstitle', $request->getByType('paymentstitle', 'Text'));
			$recordModel->set('payment_system', $paymentSystem);
		}
		$recordModel->set('paymentsin_status', $request->getByType('paymentsin_status', 'Alnum'));
		$recordModel->save();
		return ['id' => $recordModel->getId()];
	}
}
