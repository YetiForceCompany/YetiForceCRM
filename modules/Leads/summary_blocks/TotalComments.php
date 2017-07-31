<?php

/**
 * TotalComments class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TotalComments
{

	public $name = 'Total comments';
	public $sequence = 2;
	public $reference = 'Comments';

	public function process($instance)
	{

		\App\Log::trace("Entering TotalComments::process() method ...");
		$adb = PearDatabase::getInstance();
		$modcomments = 'SELECT COUNT(modcommentsid) AS comments FROM vtiger_modcomments
			WHERE vtiger_modcomments.related_to = ?';
		$result_modcomments = $adb->pquery($modcomments, array($instance->getId()));
		$count = $adb->query_result($result_modcomments, 0, 'comments');
		\App\Log::trace("Exiting TotalComments::process() method ...");
		return $count;
	}
}
