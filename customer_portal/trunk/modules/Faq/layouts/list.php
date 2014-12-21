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
		<h3><?php echo Language::translate("LBL_FAQ_CATEGORIES"); ?></h3>
		</div>
		<div class="col-lg-3">  
		<ul class="list-group" role="tablist">
			<?php $ct=0; foreach($data['faqcategories'] as $fq): ?>
				<li class="list-group-item <?php if($ct==0) echo "active"; ?>"><a href="#p<?php echo $ct; ?>" role="tab" data-toggle="tab"><?php echo $fq[1]; ?></a></li>
			<?php $ct++; endforeach; ?> 
		</ul>
		</div>
		<div class="col-lg-9">
			<div class="tab-content">      
			  <?php $ct=0; foreach($data['faqcategories'] as $fq): ?>
			  <div class="tab-pane <?php if($ct==0) echo "active"; ?>" id="p<?php echo $ct; ?>">
					<div id="accordion">
				<?php if(isset($data['faqs'][$fq[0]]) && count($data['faqs'][$fq[0]])>0 && $data['faqs'][$fq[0]]!=""): foreach($data['faqs'][$fq[0]] as $faq): ?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $faq['id']; ?>"><?php echo $faq['faqno']." - ".$faq['question']; ?></a>
							</h4>
						</div>
						<div id="<?php echo $faq['id']; ?>" class="panel-collapse collapse in">
							<div class="panel-body">
								<h4><?php echo Language::translate("LBL_FAQ_ANSWER"); ?>: </h4>
								<p><?php echo $faq['answer']; ?></p>
								<?php if(isset($data['faqproducts'][$faq['product_id']])): ?>
								<h4><?php echo Language::translate("LBL_FAQ_RELATED_PRODUCT"); ?>: <?php echo $data['faqproducts'][$faq['product_id']]['productname']; ?></h4>
								<?php endif; ?>
								<div class="row">
									<?php if(isset($faq['attachments'])): 
											echo "<div class='col-md-12'><h4>".Language::translate("LBL_FAQ_ATTACHMENTS").":</h4></div>";
											foreach($faq['attachments'] as $doc) 
											echo "<div class='col-md-3 dwbtn'>".$doc[1]['fielddata']."</div>";
										
										  endif; ?>
								</div>
							</div>
						</div>
					</div>
				  <?php endforeach; else: ?>  
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
					<?php endif; ?>
			   </div>
			  </div>
			  <?php $ct++; endforeach; ?>
			</div>	
		</div>
	</div>
</div>
<!-- /#page-wrapper -->
 <script>
$(document).ready(function() {
	$('.dwbtn > a').attr("class","btn btn-lg btn-info");
	$('.dwbtn > a').prepend('<i class="fa fa-eye"></i>&nbsp;|&nbsp;');
});
</script>