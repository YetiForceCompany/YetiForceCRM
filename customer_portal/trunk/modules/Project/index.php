<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
class Project extends BaseModule{

	public function detail($targetid){	
		$this->targetid=$targetid;
		$sparams2 = array(
			'id' => $this->targetid, 
			'module'=>$this->module,
			'customerid'=>$_SESSION["loggeduser"]['id'],
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);
		$lmod2 = $GLOBALS["sclient"]->call('get_project_tickets', $sparams2);
		
		if(isset($lmod2) && count($lmod2)>0 && $lmod2!=""){
			$data['relatedticketlist']=$lmod2[1][$this->module]['data'];
			$data['relatedtickettableheader']=$lmod2[0][$this->module]['head'][0];
		}
		
		$sparams3 = array(
			'id' => $this->targetid, 
			'block'=>"ProjectTask",
			'contactid'=>$_SESSION["loggeduser"]['id'],
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);
		
		$lmod3 = $GLOBALS["sclient"]->call('get_project_components', $sparams3);
		
		if(isset($lmod3) && count($lmod3)>0 && $lmod3!=""){
			$data['relatedtaskslist']=$lmod3[1]['ProjectTask']['data'];
			$data['relatedtaskstableheader']=$lmod3[0]['ProjectTask']['head'][0];
		}
		
		$sparams4 = array(
			'id' => $this->targetid, 
			'block'=>"ProjectMilestone",
			'contactid'=>$_SESSION["loggeduser"]['id'],
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);

		$lmod4 = $GLOBALS["sclient"]->call('get_project_components', $sparams4);
		
		if(isset($lmod4) && count($lmod4)>0 && $lmod4!=""){
			$data['relatedmilestoneslist']=$lmod4[1]['ProjectMilestone']['data'];
			$data['relatedmilestonestableheader']=$lmod4[0]['ProjectMilestone']['head'][0];
		}
		$data['recordinfo']=parent::detail($this->targetid,false);
		$data['recordid']=$this->targetid;
		Template::display($this->module,$data,'detail');
	}
}