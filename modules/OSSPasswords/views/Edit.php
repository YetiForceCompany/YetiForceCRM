<?php

/**
 * OSSPasswords edit view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_Edit_View extends Vtiger_Edit_View
{
	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return array - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'modules.OSSPasswords.resources.gen_pass',
			'libraries.clipboard.dist.clipboard',
			'modules.OSSPasswords.resources.zClipDetailView',
		]), parent::getFooterScripts($request));
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		// check if passwords are encrypted
		if (file_exists('modules/OSSPasswords/config.ini')) {   // encryption key exists so passwords are encrypted
			$config = parse_ini_file('modules/OSSPasswords/config.ini');
			// let smarty know that passwords are encrypted
			$viewer->assign('ENCRYPTED', true);
			$viewer->assign('ENC_KEY', $config['key']);
		} else {
			$viewer->assign('ENCRYPTED', false);
			$viewer->assign('ENC_KEY', '');
		}
		$viewer->assign('VIEW', $request->getByType('view', 1));
		// widget button
		// get min, max, allow_chars from vtiger_passwords_config
		$passwordConfig = (new App\Db\Query())->from('vtiger_passwords_config')->one();
		$GenerateButton = 'Generate Password';
		$viewer->assign('GENERATEPASS', $GenerateButton);

		$viewer->assign('passLengthMin', $passwordConfig['pass_length_min']);
		$viewer->assign('passLengthMax', $passwordConfig['pass_length_max']);
		$viewer->assign('allowChars', $passwordConfig['pass_allow_chars']);
		parent::process($request);
	}
}
