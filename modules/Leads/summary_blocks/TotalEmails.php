<?php

/**
 * Total emails
 * @package YetiForce.SummaryBlock
 * @license licenses/License.html
 * @author YetiForce.com
 */
class TotalEmails
{

	public $name = 'Total emails';
	public $sequence = 1;
	public $reference = 'OSSMailView';

	/**
	 * Function get number of emails
	 * @param Vtiger_Record_Model $instance
	 * @return int - Number of emails
	 */
	public function process(Vtiger_Record_Model $instance)
	{
		$relationListView = Vtiger_RelationListView_Model::getInstance($instance, $this->reference);
		return (int) $relationListView->getRelatedEntriesCount();
	}
}
