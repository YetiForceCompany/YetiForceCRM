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
			<h1 class="page-header"><?php echo Language::translate("LBL_TICKET_DETAIL"); ?></h1>
		</div>
		<!-- /.col-lg-12 -->
	</div>
	<?php if(isset($data['errors']) && $data['errors']!=''){ ?>
		<div class="row">
			<div class="col-lg-12">
				<div class="alert alert-danger"><?php echo Language::translate($data['errors']); ?></div>
			</div>
		</div>
	<?php } 
		$countBlock = 0;
	?>
	<div class="row">
	<?php foreach($data['ticket_infos'] as $blockname => $tblocks): 
		$countBlock++;
	?>
		<?php if($countBlock == 2){ ?>
		<div class="col-lg-6">
			<?php 
				switch ($data['ticket_status']) {
					case "Open":
						$panelcolor="yellow";
						break;
					case "In Progress":
						$panelcolor="primary";
						break;
					case "Closed":
						$panelcolor="green";
						break;
					case "Wait For Response":
						$panelcolor="red";
						break;
				}
			?>
			<div class="panel panel-<?php echo $panelcolor; ?>">
				<div class="panel-heading">
					<div class="text-center">
					<?php echo Language::translate("LBL_HELPDESK_STATUS"); ?>
					<div class="huge"><?php echo $data['ticket_status_translated']; ?></div>
					</div>
				</div>
			   <?php if($data['ticket_status']!="Closed"): ?> 
				<a href="index.php?module=HelpDesk&action=index&fun=close_ticket&id=<?php echo $data['ticketid']; ?>">
					<div class="panel-footer text-center">
						<b><?php echo Language::translate("LBL_HELPDESK_CLOSE_TICKET"); ?></b>
						<div class="clearfix"></div>
					</div>
				</a>
				<?php endif; ?>
			</div>
			<?php if( $data['attachments'] == '' || ( is_array($data['attachments']) && !array_key_exists('error', $data['attachments']) ) ): ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<span class="glyphicon glyphicon-file" aria-hidden="true"></span> <?php echo Language::translate("LBL_HELPDESK_ATTACHMENTS"); ?>
					</div>
					<table class="table">
						<?php 
						if(isset($data['attachments']) && count($data['attachments'])>0 && $data['attachments']!="") foreach($data['attachments'] as $cat){
							echo '<tr><td><h5>'.ltrim($cat['filename'],$_REQUEST['id'].'_').'</h5></td><td><a class="btn btn-success btn-sm"  href="index.php?downloadfile=true&fileid='.$cat['fileid'].'&filename='.$cat['filename'].'&filetype='.$cat['filetype'].'&filesize='.$cat['filesize'].'&id='.$_REQUEST['id'].'">'.Language::translate("LBL_HELPDESK_DOWNLOAD_ATTACHMENTS").'</a></td></tr>';
						}
						?>
					</table>
					<?php if($data['ticket_status']!="Closed"): ?>
						<div class="panel-footer">
						<form name="fileattachment" method="post" enctype="multipart/form-data" action="index.php">
						<input type="hidden" name="module" value="HelpDesk">
						<input type="hidden" name="action" value="index">
						<input type="hidden" name="fun" value="uploadfile">
						<input type="hidden" name="id" value="<?php echo (int) $data['ticketid'];?>">
						<input type="hidden" name="customerfile_hidden"/>
						<div class="input-group">
							<input id="btn-input" type="file" name="customerfile" class="form-control input-sm" placeholder="<?php echo Language::translate("LBL_HELPDESK_UPLOAD"); ?>..." onchange="validateFilename(this)" >
							<span class="input-group-btn">
								<button class="btn btn-warning btn-sm" id="btn-chat" type="submit">
									<?php echo Language::translate("LBL_HELPDESK_UPLOAD"); ?>
								</button>
							</span>
						</div>
						</form>
					</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php } ?>
		<div class="col-lg-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<?php echo $blockname; ?>
				</div>
				<table class="table">
					<?php
						foreach($tblocks as $field){
							echo "<tr><td class='col-lg-5'><b>".$field['label'].": </b></td><td>".$field['value']."</td></tr>";
						}
					?>
				</table>
			</div>
			<!-- /.panel -->
		</div>
		<!-- /.col-lg-6 -->
	<?php endforeach; ?>
	</div>
		<!-- /.row -->
	<div class="row">   
		<div class="col-lg-12">
		<div class="chat-panel panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-comments fa-fw"></i>
					<?php echo Language::translate("LBL_HELPDESK_COMMENTS"); ?>
				</div>
				<!-- /.panel-heading -->
				<div class="panel-body" style="height: auto; max-height: 350px;">
					<ul class="chat">
					 <?php if(isset($data['commentresult']) && count($data['commentresult'])>0  && $data['commentresult']!="")  foreach($data['commentresult'] as $comment): ?>
						<li class="left clearfix">
							<div class="">
								<div class="header">
									<strong class="primary-font"><?php echo $comment['owner']; ?></strong> 
									<small class="pull-right text-muted">
										<i class="fa fa-clock-o fa-fw"></i> <?php echo $comment['createdtime']; ?>
									</small>
								</div>
								<p>
									<?php echo $comment['comments']; ?>
								</p>
							</div>
						</li>
						 <?php endforeach; ?>
					 </ul>
				</div>
				<!-- /.panel-body -->
				<?php if($data['ticket_status']!="Closed"): ?>
				<div class="panel-footer">
				<form name="comments" action="index.php" method="post">
					<input type="hidden" name="module" value="HelpDesk">
					<input type="hidden" name="action" value="index">
					<input type="hidden" name="fun" value="updatecomment">
					<input type="hidden" name="id" value='<?php echo $data['ticketid']; ?>'>
					<textarea name="comments" rows="5" class="form-control input-sm" ></textarea>
					<br>
					<input class="btn btn-warning btn-sm" title="Send Comment" accesskey="S"  name="submit" value="<?php echo Language::translate("LBL_HELPDESK_SEND_COMMENT"); ?>" type="submit" onclick="if(trim(this.form.comments.value) != '') return true; else return false;" />
				</form>
				</div>
				<!-- /.panel-footer -->
				<?php endif; ?>
			</div>
		</div>
		<!-- /.col-lg-6 -->
	</div>
	<!-- /.row -->
</div>
<!-- /#page-wrapper -->