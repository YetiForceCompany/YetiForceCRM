<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Profiles Record Model Class.
 */
class Settings_Profiles_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Profile field inactive.
	 *
	 * @var int
	 */
	const PROFILE_FIELD_INACTIVE = 0;

	/**
	 * Profile field readonly.
	 *
	 * @var int
	 */
	const PROFILE_FIELD_READONLY = 1;

	/**
	 * Profile field readwrite.
	 *
	 * @var int
	 */
	const PROFILE_FIELD_READWRITE = 2;

	/**
	 * Field locked UI types.
	 *
	 * @var array
	 */
	private static $fieldLockedUiTypes = [70];

	/**
	 * Function to get the Id.
	 *
	 * @return int Profile Id
	 */
	public function getId()
	{
		return $this->get('profileid');
	}

	/**
	 * Function to get the Id.
	 *
	 * @param mixed $id
	 *
	 * @return int Profile Id
	 */
	protected function setId($id)
	{
		$this->set('profileid', $id);

		return $this;
	}

	/**
	 * Function to get the Profile Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('profilename');
	}

	/**
	 * Function to get the description of the Profile.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->get('description');
	}

	/**
	 * Function to get the Edit View Url for the Profile.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return '?module=Profiles&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View Url for the Profile.
	 *
	 * @return string
	 */
	public function getDuplicateViewUrl()
	{
		return '?module=Profiles&parent=Settings&view=Edit&from_record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Action Url for the Profile.
	 *
	 * @return string
	 */
	public function getDeleteAjaxUrl()
	{
		return '?module=Profiles&parent=Settings&action=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current profile.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=Profiles&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Return global permissions.
	 *
	 * @return array
	 */
	public function getGlobalPermissions()
	{
		if (!isset($this->global_permissions)) {
			$globalPermissions = [];
			$globalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW] = $globalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT] = Settings_Profiles_Module_Model::GLOBAL_ACTION_DEFAULT_VALUE;
			if ($this->getId()) {
				$globalPermissions = (new App\Db\Query())
					->select(['globalactionid', 'globalactionpermission'])
					->from('vtiger_profile2globalpermissions')
					->where(['profileid' => $this->getId()])
					->createCommand()->queryAllByGroup(0);
			}
			$this->global_permissions = $globalPermissions;
		}
		return $this->global_permissions;
	}

	/**
	 * Check if has global read permission.
	 *
	 * @return bool
	 */
	public function hasGlobalReadPermission()
	{
		$globalPermissions = $this->getGlobalPermissions();
		$viewAllPermission = $globalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW];
		if (Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $viewAllPermission) {
			return true;
		}
		return false;
	}

	/**
	 * Check if has global write permission.
	 *
	 * @return bool
	 */
	public function hasGlobalWritePermission()
	{
		$globalPermissions = $this->getGlobalPermissions();
		$editAllPermission = $globalPermissions[Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT];
		if ($this->hasGlobalReadPermission()
			&& Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $editAllPermission) {
			return true;
		}
		return false;
	}

	/**
	 * Check if has module permission.
	 *
	 * @param string $module
	 *
	 * @return bool
	 */
	public function hasModulePermission($module)
	{
		$moduleModule = $this->getProfileTabModel($module);
		$modulePermissions = $moduleModule->get('permissions');
		$moduleAccessPermission = $modulePermissions['is_permitted'];
		if (isset($modulePermissions['is_permitted']) && Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $moduleAccessPermission) {
			return true;
		}
		return false;
	}

	/**
	 * Check if has module action permission.
	 *
	 * @param string              $module
	 * @param Vtiger_Action_Model $action
	 *
	 * @return bool
	 */
	public function hasModuleActionPermission($module, $action)
	{
		$actionId = false;
		if (\is_object($action) && is_a($action, 'Vtiger_Action_Model')) {
			$actionId = $action->getId();
		} else {
			$action = Vtiger_Action_Model::getInstance($action);
			$actionId = $action->getId();
		}
		if (!$actionId) {
			return false;
		}

		$moduleModel = $this->getProfileTabModel($module);
		$modulePermissions = $moduleModel->get('permissions');
		$moduleAccessPermission = $modulePermissions['is_permitted'];
		if (Settings_Profiles_Module_Model::IS_PERMITTED_VALUE != $moduleAccessPermission) {
			return false;
		}
		$moduleActionPermissions = $modulePermissions['actions'];
		$moduleActionPermission = $moduleActionPermissions[$actionId];
		if (isset($moduleActionPermissions[$actionId]) && Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $moduleActionPermission) {
			return true;
		}
		return false;
	}

	/**
	 * Check if has module field permission.
	 *
	 * @param string             $module
	 * @param Vtiger_Field_Model $field
	 *
	 * @return bool
	 */
	public function hasModuleFieldPermission($module, $field)
	{
		$fieldModel = $this->getProfileTabFieldModel($module, $field);
		$fieldPermissions = $fieldModel->get('permissions');
		$fieldAccessPermission = $fieldPermissions['visible'];
		if (!(4 === $fieldModel->getDisplayType() || \in_array($fieldModel->get('presence'), [1, 3]))
			&& Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $fieldAccessPermission) {
			return true;
		}
		return false;
	}

	/**
	 * Check if has module field write permission.
	 *
	 * @param string $module
	 * @param int    $field
	 *
	 * @return bool
	 */
	public function hasModuleFieldWritePermission($module, $field)
	{
		$fieldModel = $this->getProfileTabFieldModel($module, $field);
		$fieldPermissions = $fieldModel->get('permissions');
		$fieldAccessPermission = $fieldPermissions['visible'];
		$fieldReadOnlyPermission = $fieldPermissions['readonly'];
		if (!(
				4 === $fieldModel->getDisplayType() || \in_array($fieldModel->get('presence'), [1, 3])
				|| 5 === $fieldModel->getDisplayType() || 0 === strcasecmp($fieldModel->getFieldDataType(), 'autogenerated')
				|| 0 === strcasecmp($fieldModel->getFieldDataType(), 'id')
			)
			&& Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $fieldAccessPermission && Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $fieldReadOnlyPermission) {
			return true;
		}
		return false;
	}

	/**
	 * Return module field permission value.
	 *
	 * @param string             $module
	 * @param Vtiger_Field_Model $field
	 *
	 * @return int
	 */
	public function getModuleFieldPermissionValue($module, $field)
	{
		if (!$this->hasModuleFieldPermission($module, $field)) {
			return self::PROFILE_FIELD_INACTIVE;
		}
		if ($this->hasModuleFieldWritePermission($module, $field)) {
			return self::PROFILE_FIELD_READWRITE;
		}
		return self::PROFILE_FIELD_READONLY;
	}

	/**
	 * Check if module field is locked.
	 *
	 * @param string $module
	 * @param int    $field
	 *
	 * @return bool
	 */
	public function isModuleFieldLocked($module, $field)
	{
		$fieldModel = $this->getProfileTabFieldModel($module, $field);
		return $fieldModel->isMandatory() || \in_array($fieldModel->get('presence'), [1, 3])
			|| \in_array($fieldModel->getUIType(), self::$fieldLockedUiTypes)
			|| 0 === strcasecmp($fieldModel->getFieldDataType(), 'autogenerated')
			|| 0 === strcasecmp($fieldModel->getFieldDataType(), 'id')
			|| (1 !== $fieldModel->getDisplayType() && 10 !== $fieldModel->getDisplayType());
	}

	/**
	 * Return profile tab model.
	 *
	 * @param Vtiger_Module_Model $module
	 *
	 * @return bool|Vtiger_Module_Model
	 */
	public function getProfileTabModel($module)
	{
		$tabId = false;
		if (\is_object($module) && is_a($module, 'Vtiger_Module_Model')) {
			$tabId = $module->getId();
		} else {
			$module = Vtiger_Module_Model::getInstance($module);
			$tabId = $module->getId();
		}
		if (!$tabId) {
			return false;
		}
		$allModulePermissions = $this->getModulePermissions();
		return $allModulePermissions[$tabId];
	}

	/**
	 * Return profile tab field model.
	 *
	 * @param string             $module
	 * @param Vtiger_Field_Model $field
	 *
	 * @return bool|Vtiger_Field_Model
	 */
	public function getProfileTabFieldModel($module, $field)
	{
		$profileTabModel = $this->getProfileTabModel($module);
		if (\is_object($field) && is_a($field, 'Vtiger_Field_Model')) {
			$fieldId = $field->getId();
		} else {
			$field = Vtiger_Field_Model::getInstance($field, $profileTabModel);
			$fieldId = $field->getId();
		}
		if (!$fieldId) {
			return false;
		}
		$moduleFields = $profileTabModel->getFields();

		return $moduleFields[$field->getName()];
	}

	/**
	 * Function to get permissions for modules.
	 *
	 * @return array
	 */
	public function getProfileTabPermissions()
	{
		if (!isset($this->profile_tab_permissions)) {
			$profile2TabPermissions = [];
			if ($this->getId()) {
				$profile2TabPermissions = (new App\Db\Query())->select(['tabid', 'permissions'])
					->from('vtiger_profile2tab')
					->where(['profileid' => $this->getId()])
					->createCommand()->queryAllByGroup(0);
			}
			$this->profile_tab_permissions = $profile2TabPermissions;
		}
		return $this->profile_tab_permissions;
	}

	/**
	 * Return profile tab field permissions.
	 *
	 * @param int $tabId
	 *
	 * @return array
	 */
	public function getProfileTabFieldPermissions($tabId)
	{
		if (!isset($this->profile_tab_field_permissions[$tabId])) {
			$profile2TabFieldPermissions = [];
			if ($this->getId()) {
				$dataReader = (new App\Db\Query())->from('vtiger_profile2field')
					->where(['profileid' => $this->getId(), 'tabid' => $tabId])
					->createCommand()->query();
				while ($row = $dataReader->read()) {
					$fieldId = $row['fieldid'];
					$visible = $row['visible'];
					$readOnly = $row['readonly'];
					$profile2TabFieldPermissions[$fieldId]['visible'] = $visible;
					$profile2TabFieldPermissions[$fieldId]['readonly'] = $readOnly;
				}
				$dataReader->close();
			}
			$this->profile_tab_field_permissions[$tabId] = $profile2TabFieldPermissions;
		}
		return $this->profile_tab_field_permissions[$tabId];
	}

	/**
	 * Function to get permission for actions.
	 *
	 * @return array
	 */
	public function getProfileActionPermissions()
	{
		if (!isset($this->profile_action_permissions)) {
			$profile2ActionPermissions = [];
			if ($this->getId()) {
				$dataReader = (new App\Db\Query())
					->from('vtiger_profile2standardpermissions')
					->where(['profileid' => $this->getId()])
					->createCommand()->query();
				while ($row = $dataReader->read()) {
					$profile2ActionPermissions[$row['tabid']][$row['operation']] = $row['permissions'];
				}
				$dataReader->close();
			}
			$this->profile_action_permissions = $profile2ActionPermissions;
		}
		return $this->profile_action_permissions;
	}

	/**
	 * Function to get permissions for utility actions.
	 *
	 * @return array
	 */
	public function getProfileUtilityPermissions()
	{
		if (!isset($this->profile_utility_permissions)) {
			$profile2UtilityPermissions = [];
			if ($this->getId()) {
				$dataReader = (new App\Db\Query())
					->from('vtiger_profile2utility')
					->where(['profileid' => $this->getId()])
					->createCommand()->query();
				while ($row = $dataReader->read()) {
					$profile2UtilityPermissions[$row['tabid']][$row['activityid']] = $row['permission'];
				}
				$dataReader->close();
			}
			$this->profile_utility_permissions = $profile2UtilityPermissions;
		}
		return $this->profile_utility_permissions;
	}

	/**
	 * Return module permissions.
	 *
	 * @return array
	 */
	public function getModulePermissions()
	{
		if (!isset($this->module_permissions)) {
			$allModules = Vtiger_Module_Model::getAll([0], Settings_Profiles_Module_Model::getNonVisibleModulesList());
			$profileTabPermissions = $this->getProfileTabPermissions();
			$profileActionPermissions = $this->getProfileActionPermissions();
			$profileUtilityPermissions = $this->getProfileUtilityPermissions();
			$allTabActions = Vtiger_Action_Model::getAll(true);
			$defaultFieldPermission = $this->getId() ? Settings_Profiles_Module_Model::NOT_PERMITTED_VALUE : Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;

			foreach ($allModules as $id => $moduleModel) {
				$permissions = [];
				$permissions['is_permitted'] = Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
				if (isset($profileTabPermissions[$id])) {
					$permissions['is_permitted'] = $profileTabPermissions[$id];
				}
				$permissions['actions'] = [];
				foreach ($allTabActions as $actionModel) {
					$actionId = $actionModel->getId();
					if (isset($profileActionPermissions[$id][$actionId])) {
						$permissions['actions'][$actionId] = $profileActionPermissions[$id][$actionId];
					} elseif (isset($profileUtilityPermissions[$id][$actionId])) {
						$permissions['actions'][$actionId] = $profileUtilityPermissions[$id][$actionId];
					} else {
						$permissions['actions'][$actionId] = Settings_Profiles_Module_Model::NOT_PERMITTED_VALUE;
					}
				}
				$moduleFields = $moduleModel->getFields();
				$allFieldPermissions = $this->getProfileTabFieldPermissions($id);
				foreach ($moduleFields as $fieldModel) {
					$fieldPermissions = [];
					$fieldId = $fieldModel->getId();
					$fieldPermissions['visible'] = $defaultFieldPermission;
					if (isset($allFieldPermissions[$fieldId]['visible'])) {
						$fieldPermissions['visible'] = $allFieldPermissions[$fieldId]['visible'];
					}
					$fieldPermissions['readonly'] = $defaultFieldPermission;
					if (isset($allFieldPermissions[$fieldId]['readonly'])) {
						$fieldPermissions['readonly'] = $allFieldPermissions[$fieldId]['readonly'];
					}
					$fieldModel->set('permissions', $fieldPermissions);
				}
				$moduleModel->set('permissions', $permissions);
			}
			$this->module_permissions = $allModules;
		}
		return $this->module_permissions;
	}

	/**
	 * Delete record and optionally transfer assigments to other record.
	 *
	 * @param Settings_Profiles_Record_Model $transferToRecord
	 */
	public function delete($transferToRecord)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$profileId = $this->getId();
		$transferProfileId = $transferToRecord->getId();
		$dbCommand->delete('vtiger_profile2globalpermissions', ['profileid' => $profileId])->execute();
		$dbCommand->delete('vtiger_profile2tab', ['profileid' => $profileId])->execute();
		$dbCommand->delete('vtiger_profile2standardpermissions', ['profileid' => $profileId])->execute();
		$dbCommand->delete('vtiger_profile2utility', ['profileid' => $profileId])->execute();
		$dbCommand->delete('vtiger_profile2field', ['profileid' => $profileId])->execute();
		$dataReader = (new App\Db\Query())->select(['roleid', 'profilecount' => new yii\db\Expression('count(profileid)')])
			->from('vtiger_role2profile')
			->where(['roleid' => (new App\Db\Query())->select(['roleid'])->from('vtiger_role2profile')->where(['profileid' => $profileId])])
			->groupBy('roleid')
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$roleId = $row['roleid'];
			$profileCount = $row['profilecount'];
			if ($profileCount > 1) {
				$dbCommand->delete('vtiger_role2profile', ['roleid' => $roleId, 'profileid' => $profileId])->execute();
			} else {
				$dbCommand->update('vtiger_role2profile', ['profileid' => $transferProfileId], ['roleid' => $roleId, 'profileid' => $profileId])->execute();
			}
		}
		$dataReader->close();
		$dbCommand->delete('vtiger_profile', ['profileid' => $profileId])->execute();
		vtlib\Access::syncSharingAccess();
	}

	/**
	 * Save record to database.
	 *
	 * @return int
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$profileName = $this->get('profilename');
		$description = $this->get('description');
		$profilePermissions = $this->get('profile_permissions');
		$isProfileDirectlyRelatedToRole = 0;
		if ($this->has('directly_related_to_role')) {
			$isProfileDirectlyRelatedToRole = $this->get('directly_related_to_role');
		}
		$profileId = $this->getId();
		if (!$profileId) {
			$db->createCommand()->insert('vtiger_profile', [
				'profilename' => $profileName,
				'description' => $description,
				'directly_related_to_role' => $isProfileDirectlyRelatedToRole,
			])->execute();
			$profileId = $db->getLastInsertID('vtiger_profile_profileid_seq');
			$this->setId($profileId);
		} else {
			$db->createCommand()->update('vtiger_profile', [
				'profilename' => $profileName,
				'description' => $description,
				'directly_related_to_role' => $isProfileDirectlyRelatedToRole,
			], ['profileid' => $profileId])->execute();
			$db->createCommand()->delete('vtiger_profile2globalpermissions', ['profileid' => $profileId])->execute();
		}
		$db->createCommand()->insert('vtiger_profile2globalpermissions', [
			'profileid' => $profileId,
			'globalactionid' => Settings_Profiles_Module_Model::GLOBAL_ACTION_VIEW,
			'globalactionpermission' => $this->tranformInputPermissionValue($this->get('editall')),
		])->execute();
		$db->createCommand()->insert('vtiger_profile2globalpermissions', [
			'profileid' => $profileId,
			'globalactionid' => Settings_Profiles_Module_Model::GLOBAL_ACTION_EDIT,
			'globalactionpermission' => $this->tranformInputPermissionValue($this->get('viewall')),
		])->execute();
		$allModuleModules = Vtiger_Module_Model::getAll([0], Settings_Profiles_Module_Model::getNonVisibleModulesList());
		if (\count($allModuleModules) > 0) {
			$actionModels = Vtiger_Action_Model::getAll(true);
			foreach ($allModuleModules as $moduleModel) {
				$moduleId = $moduleModel->getId();
				if ($moduleModel->isActive() && isset($profilePermissions[$moduleId])) {
					foreach ($actionModels as $actionModel) {
						$actionId = $actionModel->getId();
						if (!isset($profilePermissions[$moduleId]['actions'][$actionId]) && $actionModel->isModuleEnabled($moduleModel)) {
							$profilePermissions[$moduleId]['actions'][$actionId] = Settings_Profiles_Module_Model::NOT_PERMITTED_VALUE;
						}
					}
					$this->saveModulePermissions($moduleModel, $profilePermissions[$moduleId]);
				} else {
					$permissions = [];
					$permissions['is_permitted'] = Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
					if ($moduleModel->isEntityModule()) {
						$permissions['actions'] = [];
						foreach ($actionModels as $actionModel) {
							if ($actionModel->isModuleEnabled($moduleModel)) {
								$permissions['actions'][$actionModel->getId()] = Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
							}
						}
						$permissions['fields'] = [];
						$moduleFields = $moduleModel->getFields();
						foreach ($moduleFields as $fieldModel) {
							if ($fieldModel->isWritable()) {
								$permissions['fields'][$fieldModel->getId()] = self::PROFILE_FIELD_READWRITE;
							} elseif ($fieldModel->isViewEnabled()) {
								$permissions['fields'][$fieldModel->getId()] = self::PROFILE_FIELD_READONLY;
							} else {
								$permissions['fields'][$fieldModel->getId()] = self::PROFILE_FIELD_INACTIVE;
							}
						}
					}
					$this->saveModulePermissions($moduleModel, $permissions);
				}
			}
		}

		$this->recalculate();

		return $profileId;
	}

	/**
	 * Save module permissions to database.
	 *
	 * @param Vtiger_Module_Model $moduleModel
	 * @param array               $permissions
	 */
	protected function saveModulePermissions($moduleModel, $permissions)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$profileId = $this->getId();
		$tabId = $moduleModel->getId();
		$profileUtilityPermissions = $this->getProfileUtilityPermissions();
		$profileTabPermissionsBase = $this->getProfileTabPermissions();
		$profileTabPermissions = $profileTabPermissionsBase[$tabId] ?? false;
		$profileActionPermissions = $this->getProfileActionPermissions();
		$profileActionPermissions = $profileActionPermissions[$tabId] ?? false;
		$dbCommand->delete('vtiger_profile2tab', ['profileid' => $profileId, 'tabid' => $tabId])->execute();
		$actionPermissions = [];
		$actionEnabled = false;
		if ($moduleModel->isEntityModule() || $moduleModel->isUtilityActionEnabled()) {
			if (isset($permissions['actions']) || $moduleModel->isUtilityActionEnabled()) {
				$actionPermissions = $permissions['actions'] ?? [];
				$actionsIdsList = Vtiger_Action_Model::$standardActions;
				//Dividing on actions
				$utilityIdsList = [];
				foreach ($actionPermissions as $actionId => $permission) {
					if (isset($actionsIdsList[$actionId])) {
						$actionsIdsList[$actionId] = $permission;
					} else {
						$utilityIdsList[$actionId] = $permission;
					}
				}
				//Update process
				if ($profileActionPermissions || isset($profileTabPermissionsBase[$tabId]) || isset($profileUtilityPermissions[$tabId])) {
					//Standard permissions
					if (!$moduleModel->isEntityModule()) {
						$actionEnabled = true;
					} elseif ($actionsIdsList) {
						$caseExpression = 'CASE';
						foreach ($actionsIdsList as $actionId => $permission) {
							if (\in_array($permission, Vtiger_Action_Model::$nonConfigurableActions)) {
								$permission = 'on';
							}
							$permissionValue = $this->tranformInputPermissionValue($permission);
							if (isset(Vtiger_Action_Model::$standardActions[$actionId])) {
								if (Settings_Profiles_Module_Model::IS_PERMITTED_VALUE == $permission) {
									$actionEnabled = true;
								}
								$caseExpression .= " WHEN operation = {$db->quoteValue($actionId)} THEN {$db->quoteValue($permissionValue)} ";
							}
						}
						$caseExpression .= 'ELSE permissions END ';
						$dbCommand->update('vtiger_profile2standardpermissions', [
							'permissions' => new \yii\db\Expression($caseExpression),
						], ['profileid' => $profileId, 'tabid' => $tabId])->execute();
					}

					foreach (Vtiger_Action_Model::$utilityActions as $utilityActionId => $utilityActionName) {
						if (!isset($utilityIdsList[$utilityActionId])) {
							$utilityIdsList[$utilityActionId] = 'off';
						}
					}
					//Utility permissions
					if ($utilityIdsList) {
						$actionEnabled = true;
						$caseExpression = 'CASE';
						foreach ($utilityIdsList as $actionId => $permission) {
							$permissionValue = $this->tranformInputPermissionValue($permission);
							$caseExpression .= " WHEN activityid = {$db->quoteValue($actionId)} THEN {$db->quoteValue($permissionValue)} ";
						}
						$caseExpression .= " ELSE {$db->quoteValue(1)} END ";
						$dbCommand->update('vtiger_profile2utility', [
							'permission' => new \yii\db\Expression($caseExpression),
						], ['profileid' => $profileId, 'tabid' => $tabId])->execute();
					}
				} else {
					//Insert Process
					//Standard permissions
					$dataToInsert = [];
					foreach ($actionsIdsList as $actionId => $permission) {
						if (\in_array($permission, Vtiger_Action_Model::$nonConfigurableActions)) {
							$permission = 'on';
						}
						$dataToInsert[] = [$profileId, $tabId, $actionId, $this->tranformInputPermissionValue($permission)];
					}
					if ($actionsIdsList && ($moduleModel->isEntityModule())) {
						$actionEnabled = true;
						$dbCommand->batchInsert('vtiger_profile2standardpermissions', ['profileid', 'tabid', 'operation', 'permissions'], $dataToInsert)->execute();
					}
					//Utility permissions
					$dataToInsert = [];
					foreach ($utilityIdsList as $actionId => $permission) {
						$dataToInsert[] = [$profileId, $tabId, $actionId, $this->tranformInputPermissionValue($permission)];
					}
					if ($utilityIdsList) {
						$actionEnabled = true;
						$dbCommand->batchInsert('vtiger_profile2utility', ['profileid', 'tabid', 'activityid', 'permission'], $dataToInsert)->execute();
					}
				}
			}
		} else {
			$actionEnabled = true;
		}

		// Enable module permission in profile2tab table only if either its an extension module or the entity module has atleast 1 action enabled
		if ($actionEnabled && isset($permissions['is_permitted'])) {
			$isModulePermitted = $this->tranformInputPermissionValue($permissions['is_permitted']);
		} else {
			$isModulePermitted = Settings_Profiles_Module_Model::NOT_PERMITTED_VALUE;
		}
		if ($isModulePermitted != $profileTabPermissions) {
			\App\Privilege::setUpdater($moduleModel->getName());
		}
		$dbCommand->insert('vtiger_profile2tab', [
			'profileid' => $profileId,
			'tabid' => $tabId,
			'permissions' => $isModulePermitted,
		])->execute();
		if (isset($permissions['fields']) && \is_array($permissions['fields'])) {
			foreach ($permissions['fields'] as $fieldId => $stateValue) {
				$dbCommand->delete('vtiger_profile2field', ['profileid' => $profileId, 'tabid' => $tabId, 'fieldid' => $fieldId])->execute();
				if (self::PROFILE_FIELD_INACTIVE == $stateValue) {
					$visible = Settings_Profiles_Module_Model::FIELD_INACTIVE;
					$readOnly = Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
				} elseif (self::PROFILE_FIELD_READONLY == $stateValue) {
					$visible = Settings_Profiles_Module_Model::FIELD_ACTIVE;
					$readOnly = Settings_Profiles_Module_Model::FIELD_READONLY;
				} else {
					$visible = Settings_Profiles_Module_Model::FIELD_ACTIVE;
					$readOnly = Settings_Profiles_Module_Model::FIELD_READWRITE;
				}
				$dbCommand->insert('vtiger_profile2field', [
					'profileid' => $profileId,
					'tabid' => $tabId,
					'fieldid' => $fieldId,
					'visible' => $visible,
					'readonly' => $readOnly,
				])->execute();
			}
		}
	}

	/**
	 * Transform input permission value.
	 *
	 * @param string $value
	 *
	 * @return int
	 */
	protected function tranformInputPermissionValue($value)
	{
		if ('on' === $value || '1' === $value) {
			return Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
		}
		return Settings_Profiles_Module_Model::NOT_PERMITTED_VALUE;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];

		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DUPLICATE_RECORD',
				'linkurl' => $this->getDuplicateViewUrl(),
				'linkicon' => 'fas fa-clone',
				'linkclass' => 'btn btn-primary btn-sm',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'" . $this->getDeleteActionUrl() . "')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-danger btn-sm',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get all the profiles linked to the given role.
	 *
	 * @param string $roleId
	 *
	 * @return Settings_Profiles_Record_Model[] Array of Settings_Profiles_Record_Model instances
	 */
	public static function getAllByRole($roleId)
	{
		$dataReader = (new App\Db\Query())->select(['vtiger_profile.*'])
			->from('vtiger_profile')
			->innerJoin('vtiger_role2profile', 'vtiger_role2profile.profileid = vtiger_profile.profileid')
			->where(['vtiger_role2profile.roleid' => $roleId])
			->createCommand()->query();
		$profiles = [];
		while ($row = $dataReader->read()) {
			$profile = new self();
			$profile->setData($row);
			$profiles[$profile->getId()] = $profile;
		}
		$dataReader->close();

		return $profiles;
	}

	/**
	 * Function to get all the profiles.
	 *
	 * @return Settings_Profiles_Record_Model[] Array of Settings_Profiles_Record_Model instances
	 */
	public static function getAll()
	{
		$dataReader = (new App\Db\Query())->from('vtiger_profile')
			->createCommand()->query();
		$profiles = [];
		while ($row = $dataReader->read()) {
			$profile = new self();
			$profile->setData($row);
			$profiles[$profile->getId()] = $profile;
		}
		$dataReader->close();

		return $profiles;
	}

	/**
	 * Function to get the instance of Profile model, given profile id.
	 *
	 * @param int $profileId
	 *
	 * @return Settings_Profiles_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($profileId)
	{
		if (App\Cache::has('ProfilesRecordModelById', $profileId)) {
			return App\Cache::get('ProfilesRecordModelById', $profileId);
		}
		$row = (new App\Db\Query())->from('vtiger_profile')
			->where(['profileid' => $profileId])
			->one();
		$profile = null;
		if ($row) {
			$profile = new self();
			$profile->setData($row);
		}
		App\Cache::save('ProfilesRecordModelById', $profileId, $profile);

		return $profile;
	}

	/**
	 * Create instance by profile name.
	 *
	 * @param string $profileName
	 * @param bool   $checkOnlyDirectlyRelated
	 * @param array  $excludedRecordId
	 *
	 * @return \self
	 */
	public static function getInstanceByName($profileName, $checkOnlyDirectlyRelated = false, $excludedRecordId = [])
	{
		$query = (new \App\Db\Query())->from('vtiger_profile')->where(['profilename' => $profileName]);
		if ($checkOnlyDirectlyRelated) {
			$query->andWhere(['directly_related_to_role' => 1]);
		}
		if (!empty($excludedRecordId)) {
			$query->andWhere(['NOT IN', 'profileid', $excludedRecordId]);
		}
		$row = $query->one();
		if ($row) {
			$profile = new self();
			$profile->setData($row);

			return $profile;
		}
		return null;
	}

	/**
	 * Function to get the Detail Url for the current group.
	 *
	 * @return string
	 */
	public function getDetailViewUrl()
	{
		return '?module=Profiles&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to check whether the profiles is directly related to role.
	 *
	 * @return bool
	 */
	public function isDirectlyRelated()
	{
		$isDirectlyRelated = $this->get('directly_related_to_role');
		if (1 == $isDirectlyRelated) {
			return true;
		}
		return false;
	}

	/**
	 * Function recalculate the sharing rules.
	 */
	public function recalculate()
	{
		$php_max_execution_time = \App\Config::main('php_max_execution_time');
		set_time_limit($php_max_execution_time);

		$userIdsList = self::getUsersList($this->getId());
		if ($userIdsList) {
			foreach ($userIdsList as $userId) {
				\App\UserPrivilegesFile::createUserPrivilegesfile($userId);
			}
		}
	}

	/**
	 * Function to get Users list from this Profile.
	 *
	 * @param int $profileId
	 *
	 * @return int[] list of user ids
	 */
	public static function getUsersList($profileId = false)
	{
		$query = (new App\Db\Query())->select(['id'])->from('vtiger_users')
			->innerJoin('vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id')
			->innerJoin('vtiger_role2profile', 'vtiger_role2profile.roleid = vtiger_user2role.roleid')
			->where(['vtiger_users.deleted' => 0]);
		if ($profileId) {
			$query->andWhere(['vtiger_role2profile.profileid' => $profileId]);
		}
		return $query->column();
	}
}
