<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSMailView_Module_Model extends Vtiger_Module_Model
{

	public function getSettingLinks()
	{
		$settingsLinks = parent::getSettingLinks();
		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT fieldid FROM vtiger_settings_field WHERE name =  'OSSMailView' && description =  'OSSMailView'", true);
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=OSSMailView&parent=Settings&view=index&block=4&fieldid=' . $db->getSingleValue($result),
			'linkicon' => $layoutEditorImagePath
		);
		return $settingsLinks;
	}

	public function isPermitted($actionName)
	{
		if ($actionName == 'EditView' || $actionName == 'CreateView') {
			return false;
		} else {
			return ($this->isActive() && Users_Privileges_Model::isPermitted($this->getName(), $actionName));
		}
	}

	public function getMailCount($owner, $dateFilter)
	{
		$db = PearDatabase::getInstance();

		if (!$owner) {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$owner = $currenUserModel->getId();
		} else if ($owner === 'all') {
			$owner = '';
		}

		$params = array();
		if (!empty($owner)) {
			$ownerSql = ' && smownerid = ? ';
			$params[] = $owner;
		}
		if (!empty($dateFilter)) {
			$dateFilterSql = ' && createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'] . ' 00:00:00';
			$params[] = $dateFilter['end'] . ' 23:59:59';
		}

		$result = $db->pquery('SELECT COUNT(*) count, ossmailview_sendtype FROM vtiger_ossmailview
						INNER JOIN vtiger_crmentity ON vtiger_ossmailview.ossmailviewid = vtiger_crmentity.crmid
						AND deleted = 0 ' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . $dateFilterSql . ' GROUP BY ossmailview_sendtype', $params);

		$response = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$saleStage = $db->query_result($result, $i, 'ossmailview_sendtype');
			$response[$i][0] = $saleStage;
			$response[$i][1] = $db->query_result($result, $i, 'count');
			$response[$i][2] = vtranslate($saleStage, $this->getName());
		}
		return $response;
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationModel = false, $relationListViewModel = false)
	{
		if ($functionName === 'get_record2mails') {
			$query = $this->reletedQueryRecords2Mail($recordId, $relatedModule, $relationModel);
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationModel);
		}

		return $query;
	}

	public function reletedQueryRecords2Mail($recordId, $relatedModule, $relationModel)
	{
		$relatedModuleName = $relatedModule->getName();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
		$relatedListFields = [];
		if ($relationModel)
			$relatedListFields = $relationModel->getRelationFields(true, true);
		if (count($relatedListFields) == 0) {
			$relatedListFields = $relatedModule->getConfigureRelatedListFields();
		}
		$queryGenerator->setCustomColumn('vtiger_crmentity.crmid');
		$queryGenerator->setFields($relatedListFields); //ossmailviewid
		$queryGenerator->setCustomFrom([
			'joinType' => 'INNER',
			'relatedTable' => 'vtiger_ossmailview_relation',
			'relatedIndex' => 'crmid',
			'baseTable' => 'vtiger_crmentity',
			'baseIndex' => 'crmid',
		]);
		$queryGenerator->setCustomCondition([
			'glue' => 'AND',
			'column' => 'vtiger_ossmailview_relation.ossmailviewid',
			'operator' => '=',
			'value' => $recordId
		]);
		$query = $queryGenerator->getQuery();
		return $query;
	}

	public function reletedQueryMail2Records($recordId, $relatedModule, $relationModel)
	{
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_ossmailview.*, vtiger_crmentity.modifiedtime, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name FROM vtiger_ossmailview 
			INNER JOIN vtiger_ossmailview_relation ON vtiger_ossmailview_relation.ossmailviewid = vtiger_ossmailview.ossmailviewid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid 
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid 
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			WHERE vtiger_crmentity.deleted = 0 && vtiger_ossmailview_relation.crmid = " . $recordId . " ";
		return $query;
	}

	public function getPreviewViewUrl($id)
	{
		return 'index.php?module=' . $this->get('name') . '&view=preview&record=' . $id;
	}

	public function isQuickCreateSupported()
	{
		return false;
	}
}
