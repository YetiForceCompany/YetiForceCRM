<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSPdf_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();

		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'General Configuration',
			'linkurl' => 'index.php?module=' . $this->getName() . '&view=Index&parent=Settings&block=4',
			'linkicon' => ''
		);
		return $settingsLinks;
	}

	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$fields = $focus->column_fields;
		foreach ($fields as $fieldName => $fieldValue) {
			$fieldValue = $recordModel->get($fieldName);
			if (is_array($fieldValue)) {
				$focus->column_fields[$fieldName] = $fieldValue;
			} else if ($fieldValue !== null) {
				if (in_array($fieldName, array('header_content', 'content', 'footer_content'))) {
					$focus->column_fields[$fieldName] = $fieldValue;
				} else {
					$focus->column_fields[$fieldName] = decode_html($fieldValue);
				}
			}
		}
		$focus->mode = $recordModel->get('mode');
		$focus->id = $recordModel->getId();
		$focus->save($moduleName);
		return $recordModel->setId($focus->id);
	}

	public static function moduleIsActive($moduleName) {
		$db = PearDatabase::getInstance();

		$sql = "SELECT * FROM vtiger_tab WHERE name = ? AND presence = ?";
		$result = $db->pquery($sql, array($moduleName, 0), TRUE);

		return !!$db->num_rows($result);
	}

	function add_links($name) {
		include_once( 'vtlib/Vtiger/Module.php' );
		$modCommentsModule = Vtiger_Module::getInstance($name);
		$mod = '$MODULE$';
		$cat = '$CATEGORY$';
		$record = '$RECORD$';
		$tekst = "PDFselectedRecords('$mod','$cat');";


		$tekst = "javascript:QuickGenerate('$mod','$cat','$record');";
		$modCommentsModule->addLink('DETAILVIEWBASIC', 'Generate default PDF', $tekst, 'glyphicon glyphicon-download-alt');

		$tekst = "javascript:QuickGenerateMail('$mod','$cat','$record');";
		//	$modCommentsModule->addLink('DETAILVIEWBASIC', 'LBL_QUICK_GENERATE_MAIL' , $tekst, 'Smarty/templates/modules/OSSPdf/wyslij_domysle_dok.png');

		$modCommentsModule->addLink('DETAILVIEWSIDEBARWIDGET', 'Pdf', 'module=OSSPdf&view=ExportPDFRecords&fromdetailview=true');
		$modCommentsModule->addLink('LISTVIEWSIDEBARWIDGET', 'Pdf', 'module=OSSPdf&view=ListViewExportPDFRecords&usingmodule=' . $name);
	}

	public static function getListTpl($module) {

		$db = PearDatabase::getInstance();

		$tabid = getTabid($module);
		$tplList = array();

		if ($tabid) {
			$sql = "SELECT osspdfid, title FROM vtiger_osspdf WHERE moduleid = ?";
			$result = $db->pquery($sql, array($tabid), true);

			for ($i = 0; $i < $db->num_rows($result); $i++) {
				$tplList[$i]['id'] = $db->query_result($result, $i, 'osspdfid');
				$tplList[$i]['name'] = $db->query_result($result, $i, 'title');
			}
		}

		return $tplList;
	}

}
