<?php
/**
 * The file contains: PaymentsIn handler class.
 *
 * @package Handler
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
