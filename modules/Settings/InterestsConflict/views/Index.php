<?php

/**
 * Settings conflict of interests index view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings conflict of interests index view class.
 */
class Settings_InterestsConflict_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$activeTab = 'Config';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab');
		}
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('USERS', Users_Record_Model::getAll());
		$viewer->assign('CONFIG_DATA', $this->getConfig());
		$viewer->assign('DATE', implode(',', \App\Fields\Date::formatRangeToDisplay([date('Y-m-d', strtotime('-1 week')), date('Y-m-d')])));
		$viewer->assign('UNLOCK_STATUS_LIST', [
			\App\Components\InterestsConflict::UNLOCK_STATUS_NEW => \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_NEW'),
			\App\Components\InterestsConflict::UNLOCK_STATUS_ACCEPTED => \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_ACCEPTED'),
			\App\Components\InterestsConflict::UNLOCK_STATUS_REJECTED => \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_REJECTED'),
			\App\Components\InterestsConflict::UNLOCK_STATUS_CANCELED => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED'),
		]);
		$viewer->assign('CONFIRM_STATUS_LIST', [
			\App\Components\InterestsConflict::CONF_STATUS_CONFLICT_NO => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_NO'),
			\App\Components\InterestsConflict::CONF_STATUS_CONFLICT_YES => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_YES'),
			\App\Components\InterestsConflict::CONF_STATUS_CANCELED => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED'),
		]);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'components.InterestsConflict'
		]));
	}

	/** {@inheritdoc} */
	public function getJSLanguageStrings(App\Request $request)
	{
		$translate = parent::getJSLanguageStrings($request);
		$translate['JS_UNLOCK_STATUS_NEW'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_NEW');
		$translate['JS_UNLOCK_STATUS_ACCEPTED'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_ACCEPTED');
		$translate['JS_UNLOCK_STATUS_REJECTED'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_REJECTED');
		$translate['JS_UNLOCK_STATUS_CANCELED'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED');
		$translate['JS_INTERESTS_CONFLICT_SET_CANCELED'] = \App\Language::translate('BTN_INTERESTS_CONFLICT_SET_CANCELED');
		$translate['BTN_UNLOCK_STATUS_ACTION_ACCEPT'] = \App\Language::translate('BTN_UNLOCK_STATUS_ACTION_ACCEPT');
		$translate['BTN_UNLOCK_STATUS_ACTION_REJECT'] = \App\Language::translate('BTN_UNLOCK_STATUS_ACTION_REJECT');
		return $translate;
	}

	/**
	 * Get configuration data.
	 *
	 * @return array
	 */
	public function getConfig(): array
	{
		$config = \App\Config::component('InterestsConflict');
		[$config['confirmationTimeInterval'], $config['confirmationTimeIntervalList']] = array_pad(explode(' ', $config['confirmationTimeInterval'], 2), 2, '-');
		$selectedModules = [];
		foreach ($config['modules'] ?? [] as $rows) {
			foreach ($rows as $row) {
				$selectedModules[$row['key']] = $row;
			}
		}
		$config['modules'] = $selectedModules;
		return $config;
	}
}
