<?php
/*
Return Description
------------------------
Info type: error, info, success
Info title: optional
Info text: mandatory
Type: 0 - notify
Type: 1 - show quick create mondal
*/
Class DataAccess_show_quick_create{
    var $config = true;
	
    public function process( $ModuleName,$ID,$record_form,$config ) {
		$db = PearDatabase::getInstance();
		if( !isset($ID)|| $ID == 0 || $ID == '')
			return Array('save_record' => true );
		return Array(
			'save_record' => false,
			'type' => 1,
			'module' => $config['modules']
		);
    }

    public function getConfig( $id,$module,$baseModule ) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery( "SELECT tabid, name FROM vtiger_tab", array() ,true);
		$modules = array();
		while ($row = $db->fetch_array($result)) {
			$modules[$row['tabid']] = $row['name'];
		}
		return Array('modules'=>$modules);
    }
}
