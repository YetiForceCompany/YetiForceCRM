<?php

/**
 * Social media class for save config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_SocialMedia_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Settings_SocialMedia_SaveAjax_Action constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('Twitter');
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			return $this->$mode($request);
		}
		throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 403);
	}

	/**
	 * Function to display rss sidebar widget.
	 *
	 * @param \App\Request $request
	 */
	public function Twitter(\App\Request $request)
	{
		\App\DebugerEx::log($request->getAll());

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false))
		]);
		$response->emit();
	}
}
