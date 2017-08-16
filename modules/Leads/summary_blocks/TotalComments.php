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
		$count = (new \App\Db\Query())->from('vtiger_modcomments')->where(['vtiger_modcomments.related_to' => $instance->getId()])->count();
		\App\Log::trace("Exiting TotalComments::process() method ...");
		return $count;
	}
}
