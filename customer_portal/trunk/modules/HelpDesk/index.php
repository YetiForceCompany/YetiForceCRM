<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
 
class HelpDesk extends BaseModule{
	/*****************************************************************************
	* Function: HelpDesk::get_list()
	* ****************************************************************************/
	function get_list(){
		$allow_all = $GLOBALS["sclient"]->call('show_all',array('module'=>$this->module));
		if($allow_all!='true') $onlymine="true";
		
		$sparams = array(array(
			'id'=>$_SESSION["loggeduser"]['id'], 
			'sessionid'=>$_SESSION["loggeduser"]['sessionid'], 
			'user_name' => $_SESSION["loggeduser"]['user_name'], 
			'onlymine' => $onlymine, 
			'where' => "", 
			'match' => ""
		));	
		
		$lmod = $GLOBALS["sclient"]->call('get_tickets_list', $sparams);
		//echo '<h2>request</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
		if(isset($lmod) && count($lmod)>0  && $lmod!=""){
		$data['tickets']=$lmod[1]['data'];
		$data['tableheader']=$lmod[0]['head'][0];
		}
		Template::display($this->module,$data,'list');
	}

	/*****************************************************************************
	* Function: HelpDesk::detail()
	* ****************************************************************************/
	
	function detail($targetid){	
		$this->targetid=$targetid;
		$ticketid=$targetid;
		$data['ticketid'] = $ticketid;
		if(isset($_FILES['customerfile'])) $data['errors'] = $uploadres=$this->add_attachment();
		if(isset($_REQUEST['comments'])) $comm=$this->update_comment();
		if(isset($_REQUEST['fun']) && $_REQUEST['fun']=="close_ticket") $cl=$this->close_ticket($ticketid);

		$params = array(
			'id' => $_REQUEST["id"], 
			'block'=>"$this->module",
			'contactid'=>$_SESSION["loggeduser"]['id'],
			'sessionid'=>$_SESSION["loggeduser"]['sessionid']
		);
		$result = $GLOBALS["sclient"]->call('get_details', $params);
		//echo '<h2>request</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';		
		$ticketinfo = $result[0][$this->module];

		$params = array(array(
			'id'=>$_SESSION["loggeduser"]['id'], 
			'sessionid'=>$_SESSION["loggeduser"]['sessionid'], 
			'ticketid' => "$ticketid"
		));
		$data['commentresult'] = $GLOBALS["sclient"]->call('get_ticket_comments', $params);
		$data['ticketscount'] = count($result);
		$data['commentscount'] = count($commentresult);
		
		$params = array(array(
			'id'=>$_SESSION["loggeduser"]['id'], 
			'sessionid'=>$_SESSION["loggeduser"]['sessionid'], 
			'ticketid' => "$ticketid"
		));
		$creator = $GLOBALS["sclient"]->call('get_ticket_creator', $params);
		$data['ticket_status'] = '';
		$data['ticket_infos']=array();
		
		foreach($ticketinfo as $ticketfield) {
			$fieldlabel = $ticketfield['fieldlabel'];
			$fieldvalue = $ticketfield['fieldvalue'];
			$orgfieldvalue = $ticketfield['orgfieldvalue'];
			$blockname = $ticketfield['blockname'];
			if(!isset($data['ticket_infos'][$blockname])) $data['ticket_infos'][$blockname]=array();
			$data['ticket_infos'][$blockname][]=array("label"=>$fieldlabel,"value"=>$fieldvalue);
			
			if ($fieldlabel == 'Status' || $fieldlabel == 'Stato') {
				$data['ticket_status'] = $orgfieldvalue;
				$data['ticket_status_translated'] = $fieldvalue;
			}
			else if($fieldlabel == 'Ticket No') $data['ticketno'] = $fieldvalue;
		}
		
		$data['attachments']=$this->get_ticket_attachments_list($_REQUEST["id"]);
		Template::display($this->module,$data,'detail');
	}

	/*****************************************************************************
	* Function: HelpDesk::create_new()
	* ****************************************************************************/
	
	function create_new(){
		$params = array(array('id'=>$_SESSION["loggeduser"]['id'], 'sessionid'=>$_SESSION["loggeduser"]['sessionid']));
		$result = $GLOBALS["sclient"]->call('get_combo_values', $params);
		$picklists=array();

		if(isset($result[0]) && count($result[0])>0 && $result[0]!="") foreach($result[0] as $key => $pick){
			foreach($pick as $pval){
				$picklists[$key][ $pval[0] ] = $pval[1];	
			}
		}
		$data['picklists']=$picklists;

		if(isset($_REQUEST['title']) && $_REQUEST['title']!=""){
			$ticket = array(
				'title'=>'title',
				'productid'=>'productid',
				'description'=>'description',
				'priority'=>'priority',
				'category'=>'category',
				'owner'=>'owner',
				'module'=>'module'
			);

			foreach($ticket as $key => $val) $ticket[$key] = $_REQUEST[$key];
			$title = $_REQUEST['title'];
			$description = $_REQUEST['description'];
			$priority = $_REQUEST['priority'];
			$severity = $_REQUEST['severity'];
			$category = $_REQUEST['category'];
			$serviceid = $_REQUEST['servicename'];	
			$projectid = $_REQUEST['projectid'];
			$this->module = $_REQUEST['ticket_module'];	
			$productid = $_REQUEST['productidf'];
			$ticket['productid'] = $_REQUEST['productidf'];
			$ticket['owner'] = $_SESSION["loggeduser"]['user_name'];
			$parent_id = $_SESSION["loggeduser"]['id'];		
			$customerid = $_SESSION["loggeduser"]['id'];
			$sessionid = $_SESSION["loggeduser"]['sessionid'];

			$params = array(array(
				'id'=>"$customerid",
				'sessionid'=>"$sessionid",
				'title'=>"$title",
				'description'=>"$description",
				'priority'=>"$priority",
				'severity'=>"$severity",
				'category'=>"$category",
				'user_name' => "$username",
				'parent_id'=>"$parent_id",
				'product_id'=>"$productid",
				'module'=>"$this->module",
				'assigned_to'=>"$Ticket_Assigned_to",
				'serviceid'=>"$serviceid",
				'projectid'=>"$projectid"
			));
		
			$record_result = $GLOBALS["sclient"]->call('create_ticket', $params);
			if(isset($record_result[0]['new_ticket']) && $record_result[0]['new_ticket']['ticketid'] != '')
			{
				$new_record = 1;
				$ticketid = $record_result[0]['new_ticket']['ticketid'];
				header("Location: index.php?module=HelpDesk&id=".$ticketid);
			}

		}
		Template::display($this->module,$data,'create');
	}
	
	
	/*****************************************************************************
	* Function: HelpDesk::update_comment()
	* ****************************************************************************/
	
	function update_comment()
	{
		$ticketid = $_REQUEST['id'];
		$ownerid = $_SESSION["loggeduser"]['id'];
		$comments = $_REQUEST['comments'];
		$customerid = $_SESSION["loggeduser"]['id'];
		$sessionid = $_SESSION["loggeduser"]['sessionid'];
	
		$params = array(array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'ticketid'=>"$ticketid",'ownerid'=>"$customerid",'comments'=>"$comments"));
	
	    $commentresult = $GLOBALS["sclient"]->call('update_ticket_comment', $params);
	}
	
	
	/*****************************************************************************
	* Function: HelpDesk::close_ticket()
	* ****************************************************************************/
	
	function close_ticket($ticketid)
	{
	
		$customerid = $_SESSION["loggeduser"]['id'];
		$sessionid = $_SESSION["loggeduser"]['sessionid'];
		$params = array(array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'ticketid'=>"$ticketid"));
	
		$result = $GLOBALS["sclient"]->call('close_current_ticket', $params);
		return $result;
	}
	
	
	/*****************************************************************************
	* Function: HelpDesk::get_ticket_attachments_list()
	* ****************************************************************************/
	
	function get_ticket_attachments_list($ticketid)
	{
		global $client;
		
		$customer_name = $_SESSION["loggeduser"]['user_name'];
		$customerid = $_SESSION["loggeduser"]['id'];
		$sessionid = $_SESSION["loggeduser"]['sessionid'];
		$params = array(array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'ticketid'=>"$ticketid"));
		$result = $GLOBALS["sclient"]->call('get_ticket_attachments',$params);
		//echo '<h2>response</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
		return $result[0];
	}
	
	/*****************************************************************************
	* Function: HelpDesk::add_attachment()
	* ****************************************************************************/
	
	function add_attachment()
	{
		//die(print_r($_REQUEST['customerfile_hidden']));
		$ticketid = $_REQUEST['id'];
		$ownerid = $_SESSION["loggeduser"]['id'];
	
		$filename = $_FILES['customerfile']['name'];
		$filetype = $_FILES['customerfile']['type'];
		$filesize = $_FILES['customerfile']['size'];
		$fileerror = $_FILES['customerfile']['error'];
		if (isset($_REQUEST['customerfile_hidden'])) {
			$filename = $_REQUEST['customerfile_hidden'];
		}
		
		$upload_error = '';
		if($fileerror == 4)
		{
			$upload_error = 'LBL_GIVE_VALID_FILE';
		}
		elseif($fileerror == 2)
		{
			$upload_error = 'LBL_UPLOAD_FILE_LARGE';
		}
		elseif($fileerror == 3)
		{
			$upload_error = 'LBL_PROBLEM_UPLOAD';
		}
	
		if(!is_dir($GLOBALS["upload_dir"])) {
			$upload_error ='LBL_NOTSET_UPLOAD_DIR';
			return $upload_error;
		}
		if($filesize > 0)
		{
			if(move_uploaded_file($_FILES["customerfile"]["tmp_name"],$GLOBALS["upload_dir"].'/'.$filename))
			{
				$filecontents = base64_encode(fread(fopen($GLOBALS["upload_dir"].'/'.$filename, "r"), $filesize));
			}
	
			$customerid = $_SESSION["loggeduser"]['id'];
			$sessionid = $_SESSION["loggeduser"]['sessionid'];
	
			$params = array(array(
					'id'=>"$customerid",
					'sessionid'=>"$sessionid",
					'ticketid'=>"$ticketid",
					'filename'=>"$filename",
					'filetype'=>"$filetype",
					'filesize'=>"$filesize",
					'filecontents'=>"$filecontents"
				));
			if($filecontents != ''){
				$commentresult = $GLOBALS["sclient"]->call('add_ticket_attachment', $params);
			}else{
				$upload_error ='LBL_FILE_HAS_NO_CONTENTS';
				return $upload_error;
			}	
		}
		else
		{
			$upload_error = 'LBL_UPLOAD_VALID_FILE';
		}
	
		return $upload_error;
	}
}
?>