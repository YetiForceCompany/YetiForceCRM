<?php

/**
 * TotalEmails class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author YetiForce S.A.
 */
class TotalEmails
{
	public $name = 'Total emails';
	public $sequence = 1;
	public $reference = 'OSSMailView';

	/**
	 * Function get number of emails.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int - Number of emails
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $this->reference);

		return (int) $relationListView->getRelatedEntriesCount();
	}
}
