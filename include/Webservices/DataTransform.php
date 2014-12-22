<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
	
	class DataTransform{
		
		public static $recordString = "record_id";
		public static $recordModuleString = 'record_module';
		
		function sanitizeDataWithColumn($row,$meta){
			
			$newRow = array();
			if(isset($row['count(*)'])){
				return DataTransform::sanitizeDataWithCountColumn($row,$meta);
			}
			$fieldColumnMapping = $meta->getFieldColumnMapping();
			$columnFieldMapping = array_flip($fieldColumnMapping);
			foreach($row as $col=>$val){
				if(array_key_exists($col,$columnFieldMapping))
					$newRow[$columnFieldMapping[$col]] = $val;
			}
			$newRow = DataTransform::sanitizeData($newRow,$meta,true);
			return $newRow;
		}
		
		function sanitizeDataWithCountColumn($row,$meta){
			$newRow = array();
			foreach($row as $col=>$val){
				$newRow['count'] = $val;
			}
			return $newRow;
		}
		
		function filterAndSanitize($row,$meta){
			
			$row = DataTransform::filterAllColumns($row,$meta);
			$row = DataTransform::sanitizeData($row,$meta);
			return $row;
		}
		
		function sanitizeData($newRow,$meta,$t=null){
			
			$newRow = DataTransform::sanitizeReferences($newRow,$meta);
			$newRow = DataTransform::sanitizeOwnerFields($newRow,$meta,$t);
			$newRow = DataTransform::sanitizeFields($newRow,$meta);
			return $newRow;
		}
		
		function sanitizeForInsert($row,$meta){
			global $adb;
			$associatedToUser = false;
			$parentTypeId = null;
			if(strtolower($meta->getEntityName()) == "emails"){
				if(isset($row['parent_id'])){
					$components = vtws_getIdComponents($row['parent_id']);
					$userObj = VtigerWebserviceObject::fromName($adb,'Users');
					$parentTypeId = $components[0];
 		            if($components[0] == $userObj->getEntityId()){
						$associatedToUser = true;
					}
				}
			}
                        // added to handle the setting reminder time
			if(strtolower($meta->getEntityName()) == "events"){
				if(isset($row['reminder_time'])&& $row['reminder_time']!= null && $row['reminder_time'] != 0){
					$_REQUEST['set_reminder'] = "Yes";
					$_REQUEST['mode'] = 'edit';

					$reminder = $row['reminder_time'];
					$seconds = (int)$reminder%60;
					$minutes = (int)($reminder/60)%60;
					$hours = (int)($reminder/(60*60))%24;
					$days =  (int)($reminder/(60*60*24));

					//at vtiger there cant be 0 minutes reminder so we are setting to 1
					if($minutes == 0){
							$minutes = 1;
					}

					$_REQUEST['remmin'] = $minutes;
					$_REQUEST['remhrs'] = $hours;
					$_REQUEST['remdays'] = $days;
				} else {
					$_REQUEST['set_reminder'] = "No";
				}
			} elseif(strtolower($meta->getEntityName()) == "calendar") {
				if(empty($row['sendnotification']) || strtolower($row['sendnotificaiton'])=='no'
						|| $row['sendnotificaiton'] == '0' || $row['sendnotificaiton'] == 'false'
						|| strtolower($row['sendnotificaiton']) == 'n') {
					unset($row['sendnotification']);
				}
			}
			$references = $meta->getReferenceFieldDetails();
			foreach($references as $field=>$typeList){
				if(strpos($row[$field],'x')!==false){
					$row[$field] = vtws_getIdComponents($row[$field]);
					$row[$field] = $row[$field][1];
				}
			}
			$ownerFields = $meta->getOwnerFields();
			foreach($ownerFields as $index=>$field){
				if(isset($row[$field]) && $row[$field]!=null){
					$ownerDetails = vtws_getIdComponents($row[$field]);
					$row[$field] = $ownerDetails[1];
				}
			}
			if(strtolower($meta->getEntityName()) == "emails"){
				if(isset($row['parent_id'])){
					if($associatedToUser === true){
						$_REQUEST['module'] = 'Emails';
						$row['parent_id'] = $row['parent_id']."@-1|";
						$_REQUEST['parent_id'] = $row['parent_id'];
					}else{
						$referenceHandler = vtws_getModuleHandlerFromId($parentTypeId,
								$meta->getUser());
						$referenceMeta = $referenceHandler->getMeta();
						$fieldId = getEmailFieldId($referenceMeta, $row['parent_id']);
						$row['parent_id'] .= "@$fieldId|";
					}
				}
			}
			if($row["id"]){
				unset($row["id"]);
			}
			if(isset($row[$meta->getObectIndexColumn()])){
				unset($row[$meta->getObectIndexColumn()]);
			}

			$row = DataTransform::sanitizeDateFieldsForInsert($row,$meta);
			$row = DataTransform::sanitizeCurrencyFieldsForInsert($row,$meta);

			return $row;
			
		}
		
		function filterAllColumns($row,$meta){
			
			$recordString = DataTransform::$recordString;
			
			$allFields = $meta->getFieldColumnMapping();
			$newRow = array();
			foreach($allFields as $field=>$col){
				$newRow[$field] = $row[$field];
			}
			if(isset($row[$recordString])){
				$newRow[$recordString] = $row[$recordString];
			}
			return $newRow;
			
		}
		
		function sanitizeFields($row,$meta){
			$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
			$recordString = DataTransform::$recordString;
			
			$recordModuleString = DataTransform::$recordModuleString;
			
			if(isset($row[$recordModuleString])){
				unset($row[$recordModuleString]);
			}
			
			if(isset($row['id'])){
				if(strpos($row['id'],'x')===false){
					$row['id'] = vtws_getId($meta->getEntityId(),$row['id']);
				}
			}
			
			if(isset($row[$recordString])){
				$row['id'] = vtws_getId($meta->getEntityId(),$row[$recordString]);
				unset($row[$recordString]);
			}
			
			if(!isset($row['id'])){
				if($row[$meta->getObectIndexColumn()] ){
					$row['id'] = vtws_getId($meta->getEntityId(),$row[$meta->getObectIndexColumn()]);
				}else{
					//TODO Handle this.
					//echo 'error id noy set' ;
				}
			}else if(isset($row[$meta->getObectIndexColumn()]) && strcmp($meta->getObectIndexColumn(),"id")!==0){
				unset($row[$meta->getObectIndexColumn()]);
			}
			
			foreach ($row as $field => $value) {
				$row[$field] = html_entity_decode($value, ENT_QUOTES, $default_charset);
			}
			return $row;
		}
		
		function sanitizeReferences($row,$meta){
			global $adb,$log;
			$references = $meta->getReferenceFieldDetails();
			foreach($references as $field=>$typeList){
				if(strtolower($meta->getEntityName()) == "emails"){
					if(isset($row['parent_id'])){
						list($row['parent_id'], $fieldId) = explode('@', $row['parent_id']);
					}
				}
				if($row[$field]){
					$found = false;
					foreach ($typeList as $entity) {
						$webserviceObject = VtigerWebserviceObject::fromName($adb,$entity);
						$handlerPath = $webserviceObject->getHandlerPath();
						$handlerClass = $webserviceObject->getHandlerClass();
						
						require_once $handlerPath;
						
						$handler = new $handlerClass($webserviceObject,$meta->getUser(),$adb,$log);
						$entityMeta = $handler->getMeta();
						if($entityMeta->exists($row[$field])){
							$row[$field] = vtws_getId($webserviceObject->getEntityId(),$row[$field]);
							$found = true;
							break;
						}
					}
					if($found !== true){
						//This is needed as for query operation of the related record is deleted.
						$row[$field] = null;
					}
				//0 is the default for most of the reference fields, so handle the case and return null instead as its the 
				//only valid value, which is not a reference Id.
				}elseif(isset($row[$field]) && $row[$field]==0){
					$row[$field] = null;
				}
			}
			return $row;
		}
		
		function sanitizeOwnerFields($row,$meta,$t=null){
			global $adb;
			$ownerFields = $meta->getOwnerFields();
			foreach($ownerFields as $index=>$field){
				if(isset($row[$field]) && $row[$field]!=null){
					$ownerType = vtws_getOwnerType($row[$field]);
					$webserviceObject = VtigerWebserviceObject::fromName($adb,$ownerType);
					$row[$field] = vtws_getId($webserviceObject->getEntityId(),$row[$field]);
				}
			}
			return $row;
		}

		function sanitizeDateFieldsForInsert($row,$meta){
			global $current_user;
			$moduleFields = $meta->getModuleFields();
			foreach($moduleFields as $fieldName=>$fieldObj){
				if($fieldObj->getFieldDataType()=="date"){
					if(!empty($row[$fieldName])){
						$dateFieldObj = new DateTimeField($row[$fieldName]);
						$row[$fieldName] = $dateFieldObj->getDisplayDate($current_user);
					}
				}
			}
			return $row;
		}

		function sanitizeCurrencyFieldsForInsert($row,$meta){
			global $current_user;
			$moduleFields = $meta->getModuleFields();
			foreach($moduleFields as $fieldName=>$fieldObj){
				if($fieldObj->getFieldDataType()=="currency" && !empty($row[$fieldName])) {
					if($fieldObj->getUIType() == '71') {
						$row[$fieldName] = CurrencyField::convertToUserFormat($row[$fieldName],$current_user);
					} else if($fieldObj->getUIType() == '72') {
						$row[$fieldName] = CurrencyField::convertToUserFormat($row[$fieldName],$current_user,true);
					}
				}
			}
			return $row;
		}
	}	
?>
