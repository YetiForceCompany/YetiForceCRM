<?php

/**
 * Add Kanban modal view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Add Kanban modal view class.
 */
class Settings_Kanban_AddModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_ADD_BOARD';

	/** {@inheritdoc} */
	public $successBtn = 'LBL_ADD';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-plus';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sourceModuleName = $request->getByType('sourceModule', 'Alnum');
		$boards = \App\Utils\Kanban::getBoards($sourceModuleName);
		$fields = \App\Utils\Kanban::getSupportedFields($sourceModuleName);
		foreach ($fields as $key => &$field) {
			if (isset($boards[$field->getId()])) {
				unset($fields[$key]);
				continue;
			}
			$field = $field->getFullLabelTranslation();
		}
		if (empty($fields)) {
			$this->successBtn = '';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS', $fields);
		$viewer->view('AddModal.tpl', $request->getModule(false));
	}
}
