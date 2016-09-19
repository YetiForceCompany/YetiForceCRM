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

		$result = $db->pquery('SELECT DISTINCT userid FROM u_yf_crmentity_showners WHERE crmid = ?', [$record]);
		while (($shownerid = $db->getSingleValue($result)) !== false) {
			if (\includes\fields\Owner::getType($shownerid) === 'Users') {
				if ($currentUser->isAdminUser() && !$rawText) {
					$displayValue .= '<a href="index.php?module=User&view=Detail&record=' . $shownerid . '">' . rtrim(\includes\fields\Owner::getLabel($shownerid)) . '</a>,';
				} else {
					$displayValue .= rtrim(\includes\fields\Owner::getLabel($shownerid)) . ',';
				}
			} else {
				if ($currentUser->isAdminUser() && !$rawText) {
					$displayValue .= '<a href="index.php?module=Groups&parent=Settings&view=Detail&record=' . $shownerid . '">' . rtrim(\includes\fields\Owner::getLabel($shownerid)) . '</a>,';
				} else {
					$displayValue .= rtrim(\includes\fields\Owner::getLabel($shownerid)) . ',';
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
		if ($record == false) {
			return [];
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT DISTINCT userid FROM u_yf_crmentity_showners WHERE crmid = ?', [$record]);
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
		if ($shownerid !== false) {
			return $shownerid;
		}

		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT DISTINCT userid FROM u_yf_crmentity_showners WHERE crmid = ?', [$record]);
		$values = [];
		while (($shownerid = $db->getSingleValue($result)) !== false) {
			$values[] = $shownerid;
		}
		Vtiger_Cache::set('SharedOwner', $record, $values);
		return $values;
	}

	public function getSearchViewList($module, $view)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$queryGenerator = new QueryGenerator($module, $currentUser);
		$meta = $queryGenerator->getMeta($module);
		$baseTable = $meta->getEntityBaseTable();
		$tableIndexList = $meta->getEntityTableIndexList();
		$baseTableIndex = $tableIndexList[$baseTable];

		$queryGenerator->initForCustomViewById($view);
		$queryGenerator->setFields([]);
		$queryGenerator->setCustomColumn('userid');
		$queryGenerator->setCustomFrom([
			'joinType' => 'INNER',
			'relatedTable' => 'u_yf_crmentity_showners',
			'relatedIndex' => 'crmid',
			'baseTable' => $baseTable,
			'baseIndex' => $baseTableIndex,
		]);
		$listQuery = $queryGenerator->getQuery('SELECT DISTINCT');
		$result = $db->query($listQuery);

		$users = $group = [];
		while ($id = $db->getSingleValue($result)) {
			$name = \includes\fields\Owner::getUserLabel($id);
			if (!empty($name)) {
				$users[$id] = $name;
				continue;
			}
			$name = \includes\fields\Owner::getGroupName($id);
			if ($name !== false) {
				$group[$id] = $name;
				continue;
			}
		}
		asort($users);
		asort($group);
		return [ 'users' => $users, 'group' => $group];
	}
}
