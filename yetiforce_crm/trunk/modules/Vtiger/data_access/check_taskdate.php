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
Class DataAccess_check_taskdate{
    var $config = false;
	
    public function process( $ModuleName,$ID,$record_form,$config ) {
		$projectmilestoneid = $record_form['projectmilestoneid'];
		if( !isset($projectmilestoneid)|| $projectmilestoneid == 0 || $projectmilestoneid == '')
			return Array('save_record' => true );
		$targetenddate = strtotime($record_form['targetenddate']);
		$moduleModel = Vtiger_Record_Model::getInstanceById($projectmilestoneid, 'ProjectMilestone');
		$projectmilestonedate = $moduleModel->get('projectmilestonedate');
		if( !isset($projectmilestonedate)|| $projectmilestonedate == 0 || $projectmilestonedate == '')
			return Array('save_record' => true );
		
		if($targetenddate > strtotime($projectmilestonedate)){
			return Array(
				'save_record' => false,
				'type'=>0,
				'info'=>Array(
					'text'=> vtranslate('Date can not be greater', 'DataAccess').' ( '.$record_form['targetenddate'].' < '.$projectmilestonedate.')',
					'type'=> 'error'
				)
			);
		}else{
			return Array('save_record' => true );
		}
    }
    public function getConfig( $id,$module,$baseModule ) {
		return Array();
    }
}