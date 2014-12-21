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
		   <?php if(isset($data) && count($data)>0){ ?>
		   <?php foreach($data as $table): ?> 
		   <?php if(isset($table['recordlist']) && count($table['recordlist'])>0 && $table['recordlist']!=""){ ?>
			   <div class="panel panel-default">
				<div class="panel-heading">
					<?php echo Language::translate($table['tablename']); ?>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTables dataTablesContainer" >
							<thead>
								<tr>
								<?php foreach($table['tableheader'] as $hf) echo "<th>".$hf['fielddata']."</th>"; ?>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach($table['recordlist'] as $record){
									echo "<tr>";
									foreach($record as $record_fields) echo "<td>".$record_fields['fielddata']."</td>";
									echo "</tr>";
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- /.panel-body -->
			</div>
			<!-- /.panel -->
				 <?php } ?> 
				 <?php endforeach; ?> 
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
	</div>
</div>
<?php Functions::loadDataTable(); ?>