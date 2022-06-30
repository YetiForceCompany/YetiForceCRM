<?php

/**
 * Settings RecordCollector SaveConfig action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

/**
 * Settings RecordCollector SaveConfig action class.
 */
class Settings_RecordCollector_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$config = $request->getArray('config');
		$recordCollectorName = $request->getByType('collector');
		if (empty($config) || !$recordCollectorName) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||', 406);
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_links', ['params' => \App\Json::encode($config)], ['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linklabel' => $recordCollectorName])->execute();

		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_NOTIFY_OK', $request->getModule(false))]);
		$response->emit();
	}
}
