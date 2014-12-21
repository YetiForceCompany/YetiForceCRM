<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
?>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header"><?php echo Language::translate("LBL_NEW_TICKET_TITLE"); ?></h1>
		</div>
		<!-- /.col-lg-12 -->
	</div>
	<div class="row">   
		<div class="col-lg-12">
		<div class="panel panel-default">
				<div class="panel-heading">
					<?php echo Language::translate("LBL_TICKET_DETAIL"); ?>
				</div>
				<form method="POST" action="index.php" role="form">
				<!-- /.panel-heading -->
				<div class="panel-body" >
			   <input type="hidden" name="module" value="HelpDesk">
			   <input type="hidden" name="action" value="new">
			   <input type="hidden" name="projectid" value="<?php echo $_REQUEST['projectid'] ?>" />
			   <div class="row">
			   
			   <div class="col-md-12">
			   <div class="form-group">
				<label for="ttitle"><?php echo Language::translate("LBL_HELPDESK_TITLE"); ?></label>
				<input type="text" class="form-control" id="ttitle" name="title" placeholder="<?php echo Language::translate("LBL_HELPDESK_TITLE_HOLDER"); ?>" name="title"></div>
			   </div>
			   <div class="col-md-6">
				   <div class="form-group">
					   <label for="severity"><?php echo Language::translate("LBL_HELPDESK_SEVERITY"); ?></label>
					   <select class="form-control chosen-select" name="severity" id="severity">
					   <?php 
						   foreach($data['picklists']['ticketseverities'] as $count => $pid){
							  echo  '<option value="'.$pid.'">'.Language::translate($pid).'</option>';
						   }
					   ?>
					   </select>
				   </div>
				   <div class="form-group">
				   <label for="category"><?php echo Language::translate("LBL_HELPDESK_CATEGORY"); ?></label>
				   <select class="form-control chosen-select" name="category" id="category">
				   <?php 
					   foreach($data['picklists']['ticketcategories'] as $count => $pid){
						  echo  '<option value="'.$pid.'">'.Language::translate($pid).'</option>';
					   }
				   ?>
				   </select>
				   </div>
			   </div>
			   <div class="col-md-6">
				   <div class="form-group">
				   <label for="serviceid"><?php echo Language::translate("LBL_HELPDESK_CONTRACT_SERVICE"); ?></label>
				   <select class="form-control chosen-select" name="serviceid" id="serviceid">
				   <option value="" selected="">- - - - -</option>
				   <?php 
					   if(isset($data['picklists']['serviceid']) && isset($data['picklists']['serviceid'])) foreach($data['picklists']['serviceid'] as $count => $pid){
						  echo  '<option value="'.$pid.'">'.Language::translate($data['picklists']['servicename'][$count]).'</option>';
					   }
				   ?>
				   </select>
				   </div>
				   
				   <div class="form-group">
				   <label for="priority"><?php echo Language::translate("LBL_HELPDESK_PRIORITY"); ?></label>
				   <select class="form-control chosen-select" name="priority" id="priority">
				   <?php 
					   foreach($data['picklists']['ticketpriorities'] as $count => $pid){
						  echo  '<option value="'.$pid.'">'.Language::translate($pid).'</option>';
					   }
				   ?>
				   </select>
				   </div>
			   </div>
			   <div class="col-md-12 text-center">
			   <div class="form-group">
			   <label for="description"><?php echo Language::translate("LBL_HELPDESK_DESCRIPTION"); ?></label>
			   <textarea name="description" id="description" class="form-control" rows="10"></textarea>
			   </div>
			   </div>
				</div>
				</div>
				<!-- /.panel-body -->
				<div class="panel-footer">
					<input type="submit" value="<?php echo Language::translate("LBL_NEW_TICKET"); ?>" class="btn btn-success btn-lg">
				</div>
				</form>
				<!-- /.panel-footer -->
			</div>
		</div>
		<!-- /.col-lg-6 -->
	</div>
	<!-- /.row -->
</div>
<!-- /#page-wrapper -->