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
			<h1 class="page-header"><?php echo $GLOBALS["modulesNames"][$module]; ?></h1>
		</div>
	</div>
  <div class="row">
	<?php if(isset($data['recordinfo']) && count($data['recordinfo'])>0 && $data['recordinfo']!=""){ foreach($data['recordinfo'] as $blockname => $tblocks): ?>
	<div class="col-lg-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php echo $blockname; ?>
			</div>
			<table class="table">
				<?php
					foreach($tblocks as $field){
						echo "<tr><td><b>".$field['label'].": </b></td><td>".$field['value']."</td></tr>";
					}
				?>
			</table>
		</div>
	</div>
	<?php endforeach;  ?>
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php echo Language::translate("LBL_PROJECT_RELATED_TICKETS"); ?>
				<div class="input-group pull-right">
					<a href="index.php?module=HelpDesk&action=new&projectid=<?php echo $data['recordid']; ?>" class="btn btn-warning btn-sm pull-right"><?php echo Language::translate("LBL_NEW_TICKET"); ?></a>
				</div>
				<div class="clearfix"></div>
			</div>   
			<?php if(isset($data['relatedticketlist']) && count($data['relatedticketlist'])>0 && $data['relatedticketlist']!=""): ?>  
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-hover dataTablesContainer" >
						<thead>
							<tr>
							<?php foreach($data['relatedtickettableheader'] as $hf) echo "<th>".$hf['fielddata']."</th>"; ?>
							</tr>
						</thead>
						<tbody>
							<?php 
							foreach($data['relatedticketlist'] as $record){
								echo "<tr>";
								foreach($record as $record_fields) echo "<td>".$record_fields['fielddata']."</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
		  <?php endif; ?>
		</div>
	</div>
	<?php if(isset($data['relatedtaskslist']) && count($data['relatedtaskslist'])>0 && $data['relatedtaskslist']!=""): ?>  
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php echo Language::translate("LBL_PROJECT_RELATED_PROJECTTASK"); ?>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover dataTablesContainer" >
					<thead>
						<tr>
						<?php foreach($data['relatedtaskstableheader'] as $hf) echo "<th>".$hf['fielddata']."</th>"; ?>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach($data['relatedtaskslist'] as $record){
							echo "<tr>";
							foreach($record as $record_fields) echo "<td>".$record_fields['fielddata']."</td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<?php if(isset($data['relatedmilestoneslist']) && count($data['relatedmilestoneslist'])>0 && $data['relatedmilestoneslist']!=""): ?>  
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php echo Language::translate("LBL_PROJECT_RELATED_PROJECTMILESTONE"); ?>
			</div>
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover dataTablesContainer" >
					<thead>
						<tr>
						<?php foreach($data['relatedmilestonestableheader'] as $hf) echo "<th>".Language::translate($hf['fielddata'])."</th>"; ?>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach($data['relatedmilestoneslist'] as $record){
							echo "<tr>";
							foreach($record as $record_fields) echo "<td>".Language::translate($record_fields['fielddata'])."</td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>
		<?php } else echo "<div class='col-lg-12'><h2>".Language::translate("The record could not be found!")."</h2></div>"; ?>
	</div>
</div>
<?php Functions::loadDataTable(); ?>