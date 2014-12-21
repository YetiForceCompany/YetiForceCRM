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
	<br />
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<?php echo $GLOBALS["modulesNames"][$module]; ?>
					<div class="input-group pull-right" >
						<a href="index.php?module=HelpDesk&action=new" class="btn btn-warning btn-sm pull-right"><?php echo Language::translate("LBL_NEW_TICKET"); ?></a>
					</div>
					<div class="clearfix"></div>
				</div>
				<!-- /.panel-heading -->
				<div class="panel-body">
				<?php if(isset($data['tickets']) && count($data['tickets'])>0 && $data['tickets']!=""){ ?>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTablesContainer">
							<thead>
								<tr>
								<?php foreach($data['tableheader'] as $hf) echo "<th>".Language::translate($hf['fielddata'])."</th>"; ?>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach($data['tickets'] as $tkf){
								
										echo "<tr>";
										foreach($tkf as $tkv) echo "<td>".$tkv['fielddata']."</td>";
										echo "</tr>";
																											
								}
									
								?>
							</tbody>
						</table>
					</div>
					<?php } else { ?>    
					<h5>
						<?php 
						$listTrans = "LBL_NO_".strtoupper($module)."_RECORDS_FOUND";
						if( Language::translate($listTrans) != $listTrans){
							echo Language::translate($listTrans);
						}else{
							echo Language::translate("LBL_NO_RECORDS_FOUND").': '.$GLOBALS["modulesNames"][$module];
						}	
						?>
					</h5>
					<?php } ?>        
				</div>
				<!-- /.panel-body -->
				<div class="panel-footer">
					<div class="input-group" style="width:100%; text-align:right;">
						<a href="index.php?module=HelpDesk&action=new" class="btn btn-warning btn-sm pull-right"><?php echo Language::translate("LBL_NEW_TICKET"); ?></a>
					</div>
				</div>
			</div>
			<!-- /.panel -->
		</div>
		<!-- /.col-lg-12 -->
	</div>
	<!-- /.row -->
</div>
<!-- /#page-wrapper -->
<?php Functions::loadDataTable(); ?>