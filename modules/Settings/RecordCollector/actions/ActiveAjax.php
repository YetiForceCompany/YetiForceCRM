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
class Settings_RecordCollector_ActiveAjax_Action extends Settings_Vtiger_Save_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('changeStatus');
	}

	public function changeStatus(App\Request $request): void
	{
		if ($request->get('collector') && $request->get('status')) {
			$collectorName = $request->get('collector');
			$status = $request->get('status');

			if ('true' === $status) {
				\vtlib\Link::addLink(0, 'EDIT_VIEW_RECORD_COLLECTOR', $collectorName, 'App\RecordCollectors\\' . $collectorName);
			} elseif ('false' === $status) {
				\vtlib\Link::deleteLink(0, 'EDIT_VIEW_RECORD_COLLECTOR', $collectorName);
			}

			$result = ['success' => true, 'message' => 'success_message'];
			$response = new Vtiger_Response();
			$response->setResult($result);
			$response->emit();
			return;
		}

		$result = ['success' => false, 'message' => 'error_message'];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
