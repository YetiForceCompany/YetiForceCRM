<?php

/**
 * Settings ApiAddress SaveConfig action class.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ApiAddress_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('provider');
		$this->exposeMethod('global');
	}

	/**
	 * Save provider configuration.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function provider(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$provider = \App\Map\Address::getInstance($request->getByType('provider', \App\Purifier::STANDARD));
		$data = $provider->getDataFromRequest($request);
		Settings_ApiAddress_Module_Model::getInstance($moduleName)->setConfig($data);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_NOTIFY_OK', $moduleName)]);
		$response->emit();
	}

	/**
	 * Save global configuration.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function global(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$data = [];
		$type = 'global';
		foreach (['min_length', 'result_num', 'default_provider', 'active'] as $fieldName) {
			if ($request->has($fieldName)) {
				switch ($fieldName) {
					case 'min_length':
						$value = $request->getByType($fieldName, \App\Purifier::INTEGER);
						if ($value < 0 || $value > 100) {
							throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $fieldName . '||' . $moduleName . '||' . $value, 406);
						}
						$data[] = ['name' => $fieldName, 'type' => $type, 'val' => (int) $value];
						break;
					case 'result_num':
						$value = $request->getByType($fieldName, \App\Purifier::INTEGER);
						if ($value < 1 || $value > 100) {
							throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $fieldName . '||' . $moduleName . '||' . $value, 406);
						}
						$data[] = ['name' => $fieldName, 'type' => $type, 'val' => (int) $value];
						break;
					case 'default_provider':
						$value = $request->getByType($fieldName, \App\Purifier::STANDARD);
						if (!isset(\App\Map\Address::getAllProviders()[$value])) {
							throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $fieldName . '||' . $moduleName . '||' . $value, 406);
						}
						$data[] = ['name' => $fieldName, 'type' => $type, 'val' => $value];
						break;
					case 'active':
						$values = $request->getArray($fieldName, \App\Purifier::BOOL, [], \App\Purifier::STANDARD);
						$providers = \App\Map\Address::getAllProviders();
						foreach ($values as $provider => $active) {
							if (!isset($providers[$provider])) {
								throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $fieldName . '||' . $moduleName . '||' . implode(',', $values), 406);
							}
							$data[] = ['name' => $fieldName, 'type' => $provider, 'val' => (int) $active];
						}
						break;
					default:
						break;
				}
			}
		}
		Settings_ApiAddress_Module_Model::getInstance($moduleName)->setConfig($data);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_NOTIFY_OK', $moduleName)]);
		$response->emit();
	}
}
