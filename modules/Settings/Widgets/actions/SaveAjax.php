<?php

/**
 * Settings widgets SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Settings_Widgets_SaveAjax_Action constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveWidget');
		$this->exposeMethod('removeWidget');
		$this->exposeMethod('updateSequence');
	}

	/**
	 * Save widget.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function saveWidget(\App\Request $request)
	{
		$params = $request->getMultiDimensionArray('params', [
			'tabid' => 'Integer',
			'data' => [
				'wid' => 'Integer',
				'type' => 'Alnum',
				'label' => 'Text',
				'relatedmodule' => 'Integer',
				'relatedfields' => ['Text'],
				'viewtype' => 'Alnum',
				'limit' => 'Integer',
				'action' => 'Integer',
				'actionSelect' => 'Integer',
				'no_result_text' => 'Integer',
				'switchHeader' => 'Text',
				'filter' => 'Text',
				'checkbox' => 'Text',
				'field_name' => 'Alnum'
			]
		]);
		if (!$this->validateLimit($params)) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||limit||' . $params['data']['limit'], 406);
		}
		Settings_Widgets_Module_Model::saveWidget($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => 1,
			'message' => \App\Language::translate('Saved changes', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function removeWidget(\App\Request $request)
	{
		$params = $request->getMultiDimensionArray('params', [
			'wid' => 'Integer',
		]);
		Settings_Widgets_Module_Model::removeWidget($params['wid']);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => 1,
			'message' => \App\Language::translate('Removed widget', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateSequence(\App\Request $request)
	{
		$params = $request->getMultiDimensionArray('params', [
			'tabid' => 'Integer',
			'data' => [[
				'index' => 'Integer',
				'column' => 'Integer',
			]]]);
		Settings_Widgets_Module_Model::updateSequence($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => 1,
			'message' => \App\Language::translate('Update has been completed', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Validate limit for widgets.
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	private function validateLimit(array $params): bool
	{
		$returnVal = false;
		if (isset($params['data']['type'])) {
			switch ($params['data']['type']) {
				case 'WYSIWYG':
				case 'SummaryCategory':
				case 'FastEdit':
				case 'DetailView':
				case 'Summary':
				case 'Updates':
					$returnVal = true;
					break;
				default:
					$returnVal = $params['data']['limit'] >= 1;
					break;
			}
		}
		return $returnVal;
	}
}
