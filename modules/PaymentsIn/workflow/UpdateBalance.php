<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 *************************************************************************************************************************************/
function UpdateBalance($entityData) {
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$salesId = 0;

	if ($moduleName == 'PaymentsIn' || $moduleName == 'PaymentsOut') {
		$salesId = explode('x', $entityData->get('salesid'));
		$salesId = $salesId[1];
	}
	elseif ($moduleName == 'Invoice') {
		$salesId = $entityId;
	}

	UpdateInvoice($salesId);
}

function UpdateInvoice( $salesId ) {
	$adb = PearDatabase::getInstance();
	$moduleBalance = 'Invoice';
	$StatusBalance = 'Paid';

	if ($salesId == 0 || $salesId == '' || Vtiger_Functions::getCRMRecordType($salesId) != $moduleBalance) {
		return false;
	}
	$sql = 'SELECT SUM(`paymentsvalue`) as suma FROM `vtiger_paymentsin` WHERE `salesid` = ? AND `paymentsin_status` = ?;';
	$result = $adb->pquery($sql, array($salesId, $StatusBalance), false);
	$paymentsIn = $adb->query_result($result, 0, 'suma');

	$sql = 'SELECT SUM(`paymentsvalue`) as suma FROM `vtiger_paymentsout` WHERE `salesid` = ? AND `paymentsout_status` = ?;';
	$result = $adb->pquery($sql, array($salesId, $StatusBalance), false);
	$paymentsOut = $adb->query_result($result, 0, 'suma');

	$sum = $paymentsIn - $paymentsOut;
	$model = Vtiger_Record_Model::getInstanceById($salesId, $moduleBalance);
	$hdnGrandTotal = $model->get('hdnGrandTotal');
	$balance = $hdnGrandTotal - $sum;

	$sql = 'UPDATE `vtiger_invoice` SET `payment_balance` = ? WHERE `invoiceid` = ? LIMIT 1;';
	$params = array( $balance, $salesId );
	$adb->pquery($sql, $params, true);

	$accountId = $model->get('account_id');
	$potentialId = $model->get('potentialid');
	UpdateAccounts($accountId);
	UpdatePotential( $potentialId );
}

function UpdateAccounts( $accountId ) {
	if ($accountId == 0 || $accountId == '' || Vtiger_Functions::getCRMRecordType($accountId) != 'Accounts') {
		return false;
	}
	$adb = PearDatabase::getInstance();
	$sql = 'SELECT SUM(payment_balance) as suma FROM vtiger_invoice WHERE accountid = ?';
	$result = $adb->pquery($sql, array($accountId), true);
	$sum = $adb->query_result($result, 0, 'suma');
	$adb->query("UPDATE vtiger_account SET payment_balance = '$sum' WHERE accountid = '$accountId' ", true);
}

function UpdatePotential( $potentialId ) {
	if ( !isRecordExists($potentialId) || Vtiger_Functions::getCRMRecordType( $potentialId ) != 'Potentials') {
		return false;
	}

	$db = PearDatabase::getInstance();
	$potentialTotal = 0;
	$paymentsIn     = 0;
	$paymentsOut    = 0;

	$sql = 'SELECT SUM(i.`total`) AS total FROM `vtiger_invoice` i INNER JOIN `vtiger_potential` p ON i.`potentialid` = p.`potentialid` WHERE p.`potentialid` = ?;';
	$params = array( $potentialId );
	$result = $db->pquery( $sql, $params );
	$potentialTotal = $db->query_result( $result, 0, 'total' );

	$sql = 'SELECT i.`invoiceid` FROM `vtiger_invoice` i INNER JOIN `vtiger_potential` p ON i.`potentialid` = p.`potentialid` WHERE p.`potentialid` = ?;';
	$params = array( $potentialId );
	$result = $db->pquery( $sql, $params );
	$invNum = $db->num_rows( $result );


	if ( $invNum > 0 ) {
		for( $i=0; $i<$invNum; $i++ ) {
			$invoiceId = $db->query_result( $result, $i, 'invoiceid' );

			if ( !isRecordExists($invoiceId) ) {
				continue;
			}

			// get sum of payments in
			$sql = 'SELECT SUM(`paymentsvalue`) AS suma FROM `vtiger_paymentsin` WHERE `salesid` = ? AND `paymentsin_status` = ?;';
			$params = array( $invoiceId, 'Paid' );
			$inResult = $db->pquery( $sql, $params );
			$paymentsIn += $db->query_result( $inResult, 0, 'suma' );

			// get sum of payments out
			$sql = 'SELECT SUM(`paymentsvalue`) AS suma FROM `vtiger_paymentsout` WHERE `salesid` = ? AND `paymentsout_status` = ?;';
			$inResult = $db->pquery( $sql, $params );
			$paymentsOut += $db->query_result( $inResult, 0, 'suma' );
		}
	}

	$paymentsSum = $paymentsIn - $paymentsOut;
	$balance = $potentialTotal - $paymentsSum;

	$sql = 'UPDATE `vtiger_potential` SET `payment_balance` = ? WHERE `potentialid` = ? LIMIT 1;';
	$params = array( $balance, $potentialId );
	$db->pquery( $sql, $params );
}