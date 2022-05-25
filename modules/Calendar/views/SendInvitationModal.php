<?php

/**
 * Send invitation modal file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Send invitation modal class.
 */
class Calendar_SendInvitationModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SEND_CALENDAR';
	/** {@inheritdoc} */
	public $modalIcon = 'yfi-send-invitation';
	/** {@inheritdoc} */
	public $successBtn = 'LBL_SEND';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->recordModel = $request->isEmpty('record') ? null : \Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!$this->recordModel || !($this->recordModel->isEditable() && \App\Mail::checkInternalMailClient())) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS', $this->getStructure());
		$viewer->assign('RECORD_ID', $this->recordModel->getId());
		$viewer->assign('EMAIL_FIELD_OPTION', $this->getStructure());
		$viewer->view('Modals/SendInvitationModal.tpl', $request->getModule());
	}

	/**
	 * Gets structure.
	 *
	 * @return array
	 */
	public function getStructure(): array
	{
		$structure = [];
		$moduleName = $this->recordModel->getModuleName();
		$textParser = App\TextParser::getInstanceByModel($this->recordModel);
		foreach ($textParser->getRelatedVariable('email', true) as $modules) {
			foreach ($modules as $blockName => $fields) {
				$blockName = \App\Language::translate($blockName, $moduleName);
				foreach ($fields as $field) {
					$structure[$blockName][$field['var_value']] = \App\Language::translate($field['label'], $moduleName);
				}
			}
		}
		if ($invites = $this->recordModel->getInvities()) {
			$emails = [];
			foreach ($invites as $invite) {
				$name = trim($invite['name']);
				if (!$name && ($crmId = $invite['crmid']) && \App\Record::isExists($crmId) && \App\Privilege::isPermitted(\App\Record::getType($crmId), 'DetailView', $crmId)) {
					$name = trim(\App\Record::getLabel($crmId));
				}
				if (($email = $invite['email'])) {
					$emails[] = $name ? "{$name} <{$email}>" : $email;
				}
			}
			if ($emails) {
				$structure[\App\Language::translate('Other', $moduleName)][implode(',', $emails)] = \App\Language::translate('LBL_INVITE_RECORDS', $moduleName);
			}
		}
		return $structure;
	}
}
