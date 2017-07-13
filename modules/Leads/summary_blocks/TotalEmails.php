<?php

/**
 * TotalEmails class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
