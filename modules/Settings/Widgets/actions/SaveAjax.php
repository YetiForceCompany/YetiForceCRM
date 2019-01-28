<?php

/**
 * Settings widgets SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveWidget');
		$this->exposeMethod('removeWidget');
		$this->exposeMethod('updateSequence');
	}

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
				'filter' => 'Alnum',
				'checkbox' => 'Text',
			]
		]);
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
}
