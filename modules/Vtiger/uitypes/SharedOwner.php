<?php

/**
 * UIType sharedOwner Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_SharedOwner_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/SharedOwner.tpl';
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/SharedOwnerFieldSearchView.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$displayValue = '';

		if ($recordInstance !== false) {
			$moduleName = $recordInstance->getModuleName();
		} elseif ($record !== false) {
			$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($record);
			$moduleName = $recordMetaData['setype'];
		}

		$shownersTable = self::getShownerTable($moduleName);
		$result = $db->pquery('SELECT DISTINCT userid FROM ' . $shownersTable . ' WHERE crmid = ?', [$record]);
		while (($shownerid = $db->getSingleValue($result)) !== false) {
			if (Vtiger_Owner_UIType::getOwnerType($shownerid) === 'User') {
				if ($currentUser->isAdminUser() && !$rawText) {
					$displayValue .= '<a href="index.php?module=User&view=Detail&record=' . $shownerid . '">' . rtrim(getOwnerName($shownerid)) . '</a>,';
				} else {
					$displayValue .= rtrim(getOwnerName($shownerid)) . ',';
				}
			} else {
				if ($currentUser->isAdminUser() && !$rawText) {
					$displayValue .= '<a href="index.php?module=Groups&parent=Settings&view=Detail&record=' . $shownerid . '">' . rtrim(getOwnerName($shownerid)) . '</a>,';
				} else {
					$displayValue .= rtrim(getOwnerName($shownerid)) . ',';
				}
			}
		}
		return rtrim($displayValue, ',');
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		if($record === false){
			return [];
		}
		$db = PearDatabase::getInstance();
		$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($record);
		$moduleName = $recordMetaData['setype'];
		$shownersTable = self::getShownerTable($moduleName);
		
		$result = $db->pquery('SELECT DISTINCT userid FROM '.$shownersTable.' WHERE crmid = ?', [$record]);
		$values = [];
		while (($shownerid = $db->getSingleValue($result)) !== false) {
			$values[] = $shownerid;
		}
		return $values;
	}

	/**
	 * Function to get the share users list
	 * @param int $record record ID
	 * @param bool $returnArray whether return data in an array
	 * @return array
	 */
	public static function getSharedOwners($record, $moduleName = false)
	{
		$shownerid = Vtiger_Cache::get('SharedOwner', $record);
		if ($shownerid) {
			return $shownerid;
		}
		
		$db = PearDatabase::getInstance();
		if ($moduleName === false) {
			$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($parentRecord);
			$moduleName = $recordMetaData['setype'];
		}
		$shownersTable = self::getShownerTable($moduleName);
		$result = $db->pquery('SELECT DISTINCT userid FROM ' . $shownersTable . ' WHERE crmid = ?', [$record]);
		$values = [];
		while (($shownerid = $db->getSingleValue($result)) !== false) {
			$values[] = $shownerid;
		}
		Vtiger_Cache::set('SharedOwner', $record, $values);
		return $values;
	}

	public static function getShownerTable($module)
	{
		return 'vtiger_' . strtolower(rtrim($module, 's')) . '_showners';
	}
}
