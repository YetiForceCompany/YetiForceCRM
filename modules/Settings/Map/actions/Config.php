<?php

/**
 * Settings map config action file.
 *
 * @package   Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings map config action class.
 */
class Settings_Map_Config_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setTileLayer');
	}

	/**
	 * Set tile layer url.
	 *
	 * @param App\Request $request
	 */
	public function setTileLayer(App\Request $request)
	{
		$value = $request->getByType('vale', 'Text');
		$oldValue = \App\Config::module('OpenStreetMap', 'tileLayerUrlTemplate');
		$all = \App\Config::module('OpenStreetMap', 'tileLayerServers');
		$result = false;
		try {
			$osm = new \App\ConfigFile('module', 'OpenStreetMap');
			$osm->set('tileLayerUrlTemplate', $all[$value]);
			$osm->create();

			$allowedImageDomains = \App\Config::security('allowedImageDomains', []);
			if ('yetiforce.com' !== $oldValue) {
				$oldValue = str_replace('{s}', '*', parse_url($oldValue, PHP_URL_HOST));
				$key = array_search($oldValue, $allowedImageDomains);
				if (false !== $key) {
					unset($allowedImageDomains[$key]);
				}
			}
			if ('YetiForceMap' !== $value) {
				$value = str_replace('{s}', '*', parse_url($all[$value], PHP_URL_HOST));
				if (!\in_array($value, $allowedImageDomains)) {
					$allowedImageDomains[] = $value;
				}
			}
			$security = new \App\ConfigFile('security');
			$security->set('allowedImageDomains', array_values($allowedImageDomains));
			$security->create();

			$result = true;
		} catch (\Throwable $th) {
			\App\Log::error('Error: ' . $th->getMessage(), __CLASS__);
			throw $th;
		}
		if ($result) {
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_CHANGES_SAVED')];
		} else {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_ERROR')];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
