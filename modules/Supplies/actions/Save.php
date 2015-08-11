<?php

/**
 * Supplies Save Action Class
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Save_Action extends Vtiger_Save_Action
{

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request)
	{
		$recordModel = parent::saveRecord($request);
		$recordModel->saveSupplieData($request);
		return $recordModel;
	}
}
