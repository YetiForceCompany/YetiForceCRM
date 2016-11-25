<?php

/**
 * Competition module model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Competition_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get list view query for popup window
	 * @param string $sourceModule Parent module
	 * @param string $field parent fieldname
	 * @param string $record parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if ($sourceModule == 'Campaigns') {
			$subQuery = (new App\Db\Query())->select(['crmid'])->from('vtiger_campaign_records')->where(['campaignid' => $record]);
			$queryGenerator->addNativeCondition(['not in', 'u_#__competition.competitionid', $subQuery]);
		}
	}
}
