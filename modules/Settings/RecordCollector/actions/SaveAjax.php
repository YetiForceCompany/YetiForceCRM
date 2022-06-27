<?php
/**
 * RecordCollector  active action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

/**
 * RecordCollector active action class.
 */
class Settings_RecordCollector_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('changeStatus');
	}

	/**
	 * Function changing status of activity for collectors.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function changeStatus(App\Request $request): void
	{
		$collectorName = $request->getByType('collector');
		$status = $request->getBoolean('status');

		if ($status) {
			\vtlib\Link::addLink(0, 'EDIT_VIEW_RECORD_COLLECTOR', $collectorName, 'App\RecordCollectors\\' . $collectorName);
		} elseif (false === $status) {
			\vtlib\Link::deleteLink(0, 'EDIT_VIEW_RECORD_COLLECTOR', $collectorName);
		}

		$result = ['success' => true, 'message' => 'success_message'];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
