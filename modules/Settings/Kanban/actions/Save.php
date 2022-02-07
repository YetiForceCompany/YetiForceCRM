<?php
/**
 * Settings Kanban Save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings Kanban Save action class.
 */
class Settings_Kanban_Save_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('add');
		$this->exposeMethod('update');
		$this->exposeMethod('delete');
		$this->exposeMethod('sequence');
	}

	/**
	 * Add board.
	 *
	 * @param \App\Request $request
	 */
	public function add(App\Request $request)
	{
		\App\Utils\Kanban::addBoard($request->getInteger('field'));
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update board.
	 *
	 * @param \App\Request $request
	 */
	public function update(App\Request $request)
	{
		$type = $request->getByType('type', \App\Purifier::ALNUM_EXTENDED);
		if (!\in_array($type, ['detail_fields', 'sum_fields'])) {
			throw new \App\Exceptions\NotAllowedMethod('LBL_PERMISSION_DENIED');
		}
		\App\Utils\Kanban::updateBoard($request->getInteger('board'), $type, $request->getArray('value'));
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Delete board.
	 *
	 * @param \App\Request $request
	 */
	public function delete(App\Request $request)
	{
		\App\Utils\Kanban::deleteBoard($request->getInteger('board'));
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update boards sequence.
	 *
	 * @param \App\Request $request
	 */
	public function sequence(App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', \App\Purifier::STANDARD);
		$boards = $request->getArray('boards', \App\Purifier::INTEGER, [], \App\Purifier::INTEGER);
		\App\Utils\Kanban::updateSequence($sourceModule, $boards);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}
}
