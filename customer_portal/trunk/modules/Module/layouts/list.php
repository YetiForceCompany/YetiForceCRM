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
				</div>
				<div class="panel-body text-center">
				<?php if(isset($data['recordlist']) && count($data['recordlist'])>0){ ?>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTablesContainer">
							<thead>
								<tr>
								<?php foreach($data['tableheader'] as $hf) echo "<th>".Language::translate($hf['fielddata'])."</th>"; ?>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach($data['recordlist'] as $record){
									echo "<tr>";
									foreach($record as $record_fields) echo "<td>".Language::translate($record_fields['fielddata'])."</td>";
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
			</div>
		</div>
	</div>
</div>
<?php Functions::loadDataTable(); ?>