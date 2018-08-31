<?php

/**
 * Social media class for config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_SocialMedia_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('CONFIG_TWITTER', new Settings_SocialMedia_Config_Model('twitter'));
		$viewer->assign('THIS_VIEW', $this);
		$viewer->view('Index.tpl', $request->getModule(false));
	}

	/**
	 * Get logs from db.
	 *
	 * @return \Generator
	 */
	public function getLogs()
	{
		$dataReader = (new \App\Db\Query())->from('s_#__social_media_logs')
			->orderBy(['date_log' => SORT_DESC])
			->limit(1000)
			->createCommand()->query();
		while (($row = $dataReader->read())) {
			yield $row;
		}
	}
}
