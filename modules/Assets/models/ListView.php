<?php
class Assets_ListView_Model extends Vtiger_ListView_Model {
	/*
	 * Function to get Setting links
	 * @return array of setting links
	 */
	public function getBasicLinks() {
		$basicLinks = parent::getBasicLinks();
		$createPermission = Users_Privileges_Model::isPermitted('Potentials', 'EditView');
		if($createPermission) {
			$basicLinks[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_GENERATE_EXTENSION',
				'linkurl' => 'javascript:Vtiger_List_Js.generatePotentials()',
				'linkicon' => 'icon-star-empty',
				'linkclass' => 'btn-success',
			);
		}
		return $basicLinks;
	}
}
