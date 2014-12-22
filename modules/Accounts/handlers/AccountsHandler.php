<?php
class AccountsHandler extends VTEventHandler {
	public function handleEvent($handlerType, $entityData){
		if($handlerType != 'vtiger.entity.aftersave.final') {
			return false;
		}
		$moduleName = $entityData->getModuleName();

		if ($moduleName == 'SalesOrder') {
			Accounts_Record_Model::recalculateAccounts( $entityData->get('account_id') );
			Accounts_Record_Model::recalculateAccountsAverageProfit( $entityData->get('account_id') );
		}
		if ($moduleName == 'Invoice') {
			Accounts_Record_Model::recalculateAccounts( $entityData->get('account_id') );
		}
	}
}