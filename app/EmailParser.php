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
		'general', 'record', 'relatedRecord', 'sourceRecord', 'organization', 'employee', 'params', 'custom', 'userVariable', 'roleEmails'
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
	 * Get users' emails belongs to role. Value 0 is for owner role level.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	public function roleEmails(string $params): string
	{
		if (!isset($this->recordModel) || !Privilege::isPermitted($this->moduleName)) {
			return '';
		}
		$recordOwnerId = $this->recordModel->get('assigned_user_id');
		if ('Groups' === Fields\Owner::getType($recordOwnerId)) {
			return '';
		}
		[$roleLevel] = array_pad(explode('|', $params, 1), 1, 0);
		$roleLevel = (int) $roleLevel;
		$usersEmails = [];
		$userModel = User::getUserModel($recordOwnerId);
		if (0 === $roleLevel) {
			$userRole = $userModel->getRole();
			$roleModel = \Settings_Roles_Record_Model::getInstanceById($userRole);
			$usersEmails = $this->getEmailsFromRoleModel($roleModel);
		} else {
			$userParentRoles = array_reverse($userModel->getParentRoles());
			$parentRolesKey = $roleLevel - 1;
			if (isset($userParentRoles[$parentRolesKey])) {
				$roleModel = \Settings_Roles_Record_Model::getInstanceById($userParentRoles[$parentRolesKey]);
				$usersEmails = $this->getEmailsFromRoleModel($roleModel);
			}
		}
		return implode(',', $usersEmails);
	}

	/**
	 * Get users' email belongs to role.
	 *
	 * @param Settings_Roles_Record_Model $roleModel
	 *
	 * @return array
	 */
	public function getEmailsFromRoleModel(\Settings_Roles_Record_Model $roleModel): array
	{
		$usersEmails = [];
		foreach ($roleModel->getUsers() as $userModel) {
			$usersEmails[] = $userModel->get('email1');
		}
		return $usersEmails;
	}
}
