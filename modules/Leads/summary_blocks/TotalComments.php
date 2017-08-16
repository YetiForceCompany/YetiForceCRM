<?php
/**
 * TotalComments class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */

/**
 * Summary block total comments for module leads
 */
class TotalComments
{

	/**
	 * Name
	 * @var string
	 */
	public $name = 'Total comments';

	/**
	 * Sequence
	 * @var int
	 */
	public $sequence = 2;

	/**
	 * Reference
	 * @var string
	 */
	public $reference = 'Comments';

	/**
	 * Process
	 * @param object $instance
	 * @return int
	 */
	public function process($instance)
	{

		\App\Log::trace("Entering TotalComments::process() method ...");
		$count = (new \App\Db\Query())->from('vtiger_modcomments')->where(['vtiger_modcomments.related_to' => $instance->getId()])->count();
		\App\Log::trace("Exiting TotalComments::process() method ...");
		return $count;
	}
}
