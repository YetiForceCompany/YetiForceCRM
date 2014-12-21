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
	<?php } else { ?>    
	<h2>
		<?php 
		$listTrans = "LBL_NO_".strtoupper($module)."_RECORDS_FOUND";
		if( Language::translate($listTrans) != $listTrans){
			echo Language::translate($listTrans);
		}else{
			echo Language::translate("LBL_NO_RECORDS_FOUND").': '.$GLOBALS["modulesNames"][$module];
		}	
		?>
	</h2>
	<?php } ?>  
	</div>
</div>