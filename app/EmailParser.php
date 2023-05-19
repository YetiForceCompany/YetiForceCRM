<?php

namespace App;

/**
 * Email parser class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class EmailParser extends TextParser
{
	/** {@inheritdoc} */
	protected const BASE_FUNCTIONS = [
		'general', 'record', 'relatedRecord', 'sourceRecord', 'organization', 'employee', 'params', 'custom', 'userVariable', 'emailRolesHierarchy'
	];
	private static $permissionToSend = [
		'Accounts' => 'emailoptout',
		'Contacts' => 'emailoptout',
		'Users' => 'emailoptout',
		'Leads' => 'noapprovalemails',
	];
	public $emailoptout = true;

	/**
	 * Check if this content can be used.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param string              $moduleName
	 *
	 * @return bool
	 */
	protected function useValue($fieldModel, $moduleName)
	{
		if ($this->emailoptout && isset(self::$permissionToSend[$moduleName])) {
			$checkFieldName = self::$permissionToSend[$moduleName];
			$permissionFieldModel = $this->recordModel->getModule()->getField($checkFieldName);
			return ($permissionFieldModel && $permissionFieldModel->isActiveField() && $this->recordModel->has($checkFieldName)) ? (bool) $this->recordModel->get($checkFieldName) : true;
		}
		return true;
	}

	/**
	 * Get content parsed for emails.
	 *
	 * @param bool $trim
	 *
	 * @return array|string
	 */
	public function getContent($trim = false)
	{
		if (!$trim) {
			return $this->content;
		}
		$emails = [];
		foreach (explode(',', $this->content) as $content) {
			$content = trim($content);
			if (empty($content) || '-' === $content) {
				continue;
			}
			if (strpos($content, '&lt;') && strpos($content, '&gt;')) {
				[$fromName, $fromEmail] = explode('&lt;', $content);
				$fromEmail = rtrim($fromEmail, '&gt;');
				$emails[$fromEmail] = $fromName;
			} else {
				$emails[] = $content;
			}
		}
		return $emails;
	}

	/** {@inheritdoc} */
	protected function relatedRecordsListPrinter(\Vtiger_RelationListView_Model $relationListView, \Vtiger_Paging_Model $pagingModel, int $maxLength): string
	{
		$relatedModuleName = $relationListView->getRelationModel()->getRelationModuleName();
		$rows = '';
		$fields = $relationListView->getHeaders();
		foreach ($relationListView->getEntries($pagingModel) as $relatedRecordModel) {
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel && 'email' === $fieldModel->getFieldDataType() && $this->useValue($fieldModel, $relatedModuleName)) {
					$rows .= $relatedRecordModel->get($fieldName) . ',';
				}
			}
		}
		return rtrim($rows, ',');
	}

	/**
	 * Get users emails according to role hierarchy level.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	public function emailRolesHierarchy(string $params): string
	{
		if (!isset($this->recordModel) || (!Privilege::isPermitted($this->moduleName))) {
			return '';
		}
		$recordOwnerId = $this->recordModel->get('assigned_user_id');
		if ('Groups' === Fields\Owner::getType($recordOwnerId)) {
			return '';
		}
		[$higherLevelRoles] = array_pad(explode('|', $params, 1), 1, 0);
		$higherLevelRoles = (int) $higherLevelRoles;
		$userModel = User::getUserModel($recordOwnerId);
		$userRole = $userModel->getRole();
		$roleModel = \Settings_Roles_Record_Model::getInstanceById($userRole);
		$usersEmails = [];
		$usersEmails = $this->getEmailsFromRoleModel($roleModel, $usersEmails);

		$userParentRoles = $userModel->getParentRoles();
		while ($higherLevelRoles && ($roleId = array_pop($userParentRoles))) {
			$roleModel = \Settings_Roles_Record_Model::getInstanceById($roleId);
			$usersEmails = $this->getEmailsFromRoleModel($roleModel, $usersEmails);
			--$higherLevelRoles;
		}
		return implode(',', $usersEmails);
	}

	/**
	 * Get users email belongs to role.
	 *
	 * @param Settings_Roles_Record_Model $roleModel
	 * @param array                       $usersEmails
	 *
	 * @return void
	 */
	public function getEmailsFromRoleModel($roleModel, $usersEmails): array
	{
		foreach ($roleModel->getUsers() as $userModel) {
			$usersEmails[] = $userModel->get('email1');
		}
		return $usersEmails;
	}
}
