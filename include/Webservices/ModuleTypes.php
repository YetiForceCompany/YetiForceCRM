<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Webservice list types
 * @staticvar boolean $webserviceEntities
 * @staticvar array $types
 * @param array $fieldTypeList
 * @param Users_Record_Model $user
 * @return array
 * @throws WebServiceException
 */
function vtws_listtypes($fieldTypeList, Users_Record_Model $user)
{
	// Bulk Save Mode: For re-using information
	static $webserviceEntities = false;
	// END

	static $types = [];
	if (!empty($fieldTypeList)) {
		$fieldTypeList = array_map(strtolower, $fieldTypeList);
		sort($fieldTypeList);
		$fieldTypeString = implode(',', $fieldTypeList);
	} else {
		$fieldTypeString = 'all';
	}
	if (!empty($types[$user->id][$fieldTypeString])) {
		return $types[$user->id][$fieldTypeString];
	}
	try {


		vtws_preserveGlobal('current_user', $user);
		//get All the modules the current user is permitted to Access.
		$allModuleNames = getPermittedModuleNames();
		if (array_search('Calendar', $allModuleNames) !== false) {
			array_push($allModuleNames, 'Events');
		}

		if (!empty($fieldTypeList)) {
			$query = (new \App\Db\Query())->select(['(vtiger_field.tabid) as tabid'])
					->from('vtiger_field')
					->leftJoin('vtiger_ws_fieldtype', 'vtiger_field.uitype=vtiger_ws_fieldtype.uitype')
					->innerJoin('vtiger_profile2field', 'vtiger_field.fieldid = vtiger_profile2field.fieldid')
					->innerJoin('vtiger_def_org_field', 'vtiger_def_org_field.fieldid = vtiger_field.fieldid')
					->innerJoin('vtiger_role2profile', 'vtiger_profile2field.profileid = vtiger_role2profile.profileid')
					->innerJoin('vtiger_user2role', 'vtiger_user2role.roleid = vtiger_role2profile.roleid')
					->where(['vtiger_profile2field.visible' => 0, 'vtiger_def_org_field.visible' => 0, 'vtiger_field.presence' => [0, 2], 'vtiger_user2role.userid' => $user->id, 'fieldtype' => $fieldTypeList])->distinct();
			$moduleList = [];
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$moduleList[] = \App\Module::getModuleName($row['tabid']);
			}
			$allModuleNames = array_intersect($moduleList, $allModuleNames);

			$entityList = (new \App\Db\Query())->select(['name'])->from('vtiger_ws_entity')
					->innerJoin('vtiger_ws_entity_tables', 'vtiger_ws_entity.id=vtiger_ws_entity_tables.webservice_entity_id')
					->innerJoin('vtiger_ws_entity_fieldtype', 'vtiger_ws_entity_fieldtype.table_name=vtiger_ws_entity_tables.table_name')
					->where(['fieldtype' => $fieldTypeList])->column();
		}
		//get All the CRM entity names.
		if ($webserviceEntities === false) {
			// Bulk Save Mode: For re-using information
			$webserviceEntities = vtws_getWebserviceEntities();
		}

		$accessibleModules = array_values(array_intersect($webserviceEntities['module'], $allModuleNames));
		$entities = $webserviceEntities['entity'];
		$accessibleEntities = [];
		if (empty($fieldTypeList)) {
			foreach ($entities as $entity) {
				$webserviceObject = VtigerWebserviceObject::fromName($db, $entity);
				$handlerPath = $webserviceObject->getHandlerPath();
				$handlerClass = $webserviceObject->getHandlerClass();

				require_once $handlerPath;
				$handler = new $handlerClass($webserviceObject, $user, $db, $log);
				$meta = $handler->getMeta();
				if ($meta->hasAccess() === true) {
					array_push($accessibleEntities, $entity);
				}
			}
		}
	} catch (WebServiceException $exception) {
		throw $exception;
	} catch (Exception $exception) {
		throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "An Database error occured while performing the operation");
	}

	$default_language = VTWS_PreserveGlobal::getGlobal('default_language');
	$current_language = vglobal('current_language');
	if (empty($current_language))
		$current_language = $default_language;
	$current_language = vtws_preserveGlobal('current_language', $current_language);

	$appStrings = \vtlib\Deprecated::return_app_list_strings_language($current_language);
	$appListString = \vtlib\Deprecated::return_app_list_strings_language($current_language);
	vtws_preserveGlobal('app_strings', $appStrings);
	vtws_preserveGlobal('app_list_strings', $appListString);

	$informationArray = [];
	foreach ($accessibleModules as $module) {
		$vtigerModule = ($module == 'Events') ? 'Calendar' : $module;
		$informationArray[$module] = array('isEntity' => true, 'label' => \App\Language::translate($module, $vtigerModule),
			'singular' => \App\Language::translate('SINGLE_' . $module, $vtigerModule));
	}

	foreach ($accessibleEntities as $entity) {
		$label = (isset($appStrings[$entity])) ? $appStrings[$entity] : $entity;
		$singular = (isset($appStrings['SINGLE_' . $entity])) ? $appStrings['SINGLE_' . $entity] : $entity;
		$informationArray[$entity] = array('isEntity' => false, 'label' => $label,
			'singular' => $singular);
	}

	VTWS_PreserveGlobal::flush();
	$types[$user->id][$fieldTypeString] = array("types" => array_merge($accessibleModules, $accessibleEntities),
		'information' => $informationArray);
	return $types[$user->id][$fieldTypeString];
}
