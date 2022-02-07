<?php
/**
 * The file contains: PaymentsIn handler class.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
class PaymentsIn_PaymentsInHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return void
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		PaymentsIn_SSingleOrdersPaymentStatus_Model::updateIfPossible($recordModel);
		PaymentsIn_FinvoicePaymentStatus_Model::updateIfPossible($recordModel);
		PaymentsIn_FinvoiceProformaPaymentStatus_Model::updateIfPossible($recordModel);
	}
}
