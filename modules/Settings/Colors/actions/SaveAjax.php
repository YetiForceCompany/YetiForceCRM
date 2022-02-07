<?php
/**
 * Basic colors action class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Slawomir Klos <s.klos@yetiforce.com>
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Ajax save actions handler class.
 */
class Settings_Colors_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateUserColor');
		$this->exposeMethod('removeUserColor');
		$this->exposeMethod('updateGroupColor');
		$this->exposeMethod('removeGroupColor');
		$this->exposeMethod('updateModuleColor');
		$this->exposeMethod('removeModuleColor');
		$this->exposeMethod('activeModuleColor');
		$this->exposeMethod('updatePicklistValueColor');
		$this->exposeMethod('removePicklistValueColor');
		$this->exposeMethod('addPicklistColorColumn');
		$this->exposeMethod('updateCalendarColor');
		$this->exposeMethod('removeCalendarColor');
		$this->exposeMethod('updateFieldColor');
		$this->exposeMethod('removeFieldColor');
	}

	/**
	 * Update user color.
	 *
	 * @param \App\Request $request
	 */
	public function updateUserColor(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		if (!$request->has('color')) {
			$color = \App\Colors::getRandomColor();
		} else {
			$color = $request->getByType('color', 'Color');
		}
		\App\Colors::updateUserColor($recordId, $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove user color.
	 *
	 * @param \App\Request $request
	 */
	public function removeUserColor(App\Request $request)
	{
		\App\Colors::updateUserColor($request->getInteger('record'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update group color.
	 *
	 * @param \App\Request $request
	 */
	public function updateGroupColor(App\Request $request)
	{
		if (!$request->has('color')) {
			$color = \App\Colors::getRandomColor();
		} else {
			$color = $request->getByType('color', 'Color');
		}
		\App\Colors::updateGroupColor($request->getInteger('record'), $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove group color.
	 *
	 * @param \App\Request $request
	 */
	public function removeGroupColor(App\Request $request)
	{
		\App\Colors::updateGroupColor($request->getInteger('record'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update module color.
	 *
	 * @param \App\Request $request
	 */
	public function updateModuleColor(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		if (!$request->has('color')) {
			$color = \App\Colors::getRandomColor();
		} else {
			$color = $request->getByType('color', 'Color');
		}
		\App\Colors::updateModuleColor($recordId, $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove module color.
	 *
	 * @param \App\Request $request
	 */
	public function removeModuleColor(App\Request $request)
	{
		\App\Colors::updateModuleColor($request->getInteger('record'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Activate/deactivate module color.
	 *
	 * @param \App\Request $request
	 */
	public function activeModuleColor(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => \App\Colors::activeModuleColor($request->getInteger('record'), $request->getBoolean('status'), ($request->isEmpty('color') || '#' === $request->getRaw('color')) ? '' : $request->getByType('color', 'Color')),
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update picklist value color.
	 *
	 * @param \App\Request $request
	 */
	public function updatePicklistValueColor(App\Request $request)
	{
		$field = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('fieldId'));
		if (!$field || !\in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
			throw new \App\Exceptions\AppException('LBL_FIELD_NOT_FOUND');
		}
		if (!$request->has('color')) {
			$color = \App\Colors::getRandomColor();
		} else {
			$color = $request->getByType('color', 'Color');
		}
		\App\Colors::updatePicklistValueColor($request->getInteger('fieldId'), $request->getInteger('fieldValueId'), $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove picklist value color.
	 *
	 * @param \App\Request $request
	 */
	public function removePicklistValueColor(App\Request $request)
	{
		$field = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('fieldId'));
		if (!$field || !\in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
			throw new \App\Exceptions\AppException('LBL_FIELD_NOT_FOUND');
		}
		\App\Colors::updatePicklistValueColor($request->getInteger('fieldId'), $request->getInteger('fieldValueId'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Add picklist color column in db table.
	 *
	 * @param \App\Request $request
	 */
	public function addPicklistColorColumn(App\Request $request)
	{
		$field = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('fieldId'));
		if (!$field || !\in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
			throw new \App\Exceptions\AppException('LBL_FIELD_NOT_FOUND');
		}
		$fieldId = $request->getInteger('fieldId');
		\App\Colors::addPicklistColorColumn($fieldId);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update calendar event type color.
	 *
	 * @param \App\Request $request
	 */
	public function updateCalendarColor(App\Request $request)
	{
		$params = [];
		$params['id'] = $request->getByType('id', 'Text');
		$params['color'] = $request->getByType('color', 'Color');
		if (!$params['color']) {
			$params['color'] = \App\Colors::getRandomColor();
		}
		if (!is_numeric($params['id'])) {
			Settings_Calendar_Module_Model::updateCalendarConfig($params);
		} else {
			$moduleInstance = vtlib\Module::getInstance('Calendar');
			$field = \Vtiger_Field_Model::getInstance(Settings_Calendar_Module_Model::getCalendarColorPicklist()[0], $moduleInstance);
			\App\Colors::updatePicklistValueColor($field->getId(), $request->getInteger('id'), $params['color']);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $params['color'],
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove calendar event type color.
	 *
	 * @param \App\Request $request
	 */
	public function removeCalendarColor(App\Request $request)
	{
		$params = [];
		$params['id'] = $request->getByType('id', 'Alnum');
		$params['color'] = '';
		Settings_Calendar_Module_Model::updateCalendarConfig($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $params['color'],
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update field color.
	 *
	 * @param \App\Request $request
	 */
	public function updateFieldColor(App\Request $request)
	{
		$fieldId = $request->getInteger('fieldId');
		if (!$request->has('color')) {
			$color = \App\Colors::getRandomColor();
		} else {
			$color = $request->getByType('color', 'Color');
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => App\Colors::updateFieldColor($fieldId, $color),
			'color' => $color,
			'message' => App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Remove field color.
	 *
	 * @param \App\Request $request
	 */
	public function removeFieldColor(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => \App\Colors::updateFieldColor($request->getInteger('fieldId'), ''),
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false)),
		]);
		$response->emit();
	}
}
