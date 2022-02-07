<?php
/**
 * TotalComments class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Summary block total comments for module leads.
 */
class TotalComments
{
	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name = 'Total comments';

	/**
	 * Sequence.
	 *
	 * @var int
	 */
	public $sequence = 2;

	/**
	 * Reference.
	 *
	 * @var string
	 */
	public $reference = 'Comments';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering TotalComments::process() method ...');
		$count = (new \App\Db\Query())->from('vtiger_modcomments')->where(['vtiger_modcomments.related_to' => $recordModel->getId()])->count();
		\App\Log::trace('Exiting TotalComments::process() method ...');

		return $count;
	}
}
