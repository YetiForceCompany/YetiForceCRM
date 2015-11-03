{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if !$NOLOADLIBS}
		{include file="modules/Vtiger/Header.tpl"}
	{/if}
	{if $ISMODAL}
		<div class="modelContainer modal fade" tabindex="-1">
			<div class="modal-dialog modal-blg">
				<div class="modal-content">
				{/if}
				<div class="SendEmailFormStep2 container-fluid" id="emailPreview" name="emailPreview">
					<div class="">
						<div class="blockHeader emailPreviewHeader">
							<h3 class='col-md-4 pushDown'>{vtranslate('emailPreviewHeader',$MODULE)}</h3>
							<div class='pull-right'>
								<div class="btn-toolbar" >
									{if vglobal('isActiveSendingMails')}
										<span class="btn-group">
											{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
											<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD_MODEL->getId()}&type=replyAll{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no'{/if})">
												<img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReplyAll.png">&nbsp;&nbsp;
												<strong>{vtranslate('LBL_REPLYALLL',$MODULE)}</strong>
											</a>
										</span>
										<span class="btn-group">
											<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD_MODEL->getId()}&type=reply{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no'{/if})">
												<img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReply.png" >&nbsp;&nbsp;
												<strong>{vtranslate('LBL_REPLY',$MODULE)}</strong>
											</a>
										</span>
										<span class="btn-group">
											<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD_MODEL->getId()}&type=forward{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no'{/if})">
												<span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp;&nbsp;
												<strong>{vtranslate('LBL_FORWARD',$MODULE)}</strong>
											</a>
										</span>
									{/if}
									{if Users_Privileges_Model::isPermitted($MODULE, 'PrintMail')}
										<span class="btn-group">
											<button id="previewPrint" onclick="printMail();" type="button" name="previewPrint" class="btn btn-default" data-mode="previewPrint">
												<span class="glyphicon glyphicon-print" aria-hidden="true"></span>&nbsp;&nbsp;
												<strong>{vtranslate('LBL_PRINT',$MODULE)}</strong>
											</button>
										</span>
									{/if}
									{if $ISMODAL}
										<span class="btn-group">
											<button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</span>
									{/if}
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
						<hr>
						<form class="form-horizontal emailPreview">
							<div class="row padding-bottom1per">
								<span class="col-md-2">
									<span class="pull-right muted">{vtranslate('From',$MODULENAME)}</span>
								</span>
								<span class="col-md-9">
									<span id="emailPreview_From" class="">{$FROM}</span>
								</span>
							</div>
							<div class="row padding-bottom1per">
								<span class="col-md-2">
									<span class="pull-right muted">{vtranslate('To',$MODULENAME)}</span>
								</span>
								<span class="col-md-9">
									<span id="emailPreview_To" class="">{assign var=TO_EMAILS value=","|implode:$TO}{$TO_EMAILS}</span>
								</span>
							</div>
							{if !empty($CC)}
								<div class="row padding-bottom1per">
									<span class="col-md-2">
										<span class="pull-right muted">{vtranslate('CC',$MODULENAME)}</span>
									</span>
									<span class="col-md-9">
										<span id="emailPreview_Cc" class="">
											{$CC}
										</span>
									</span>
								</div>
							{/if}
							{if !empty($BCC)}
								<div class="row padding-bottom1per">
									<span class="col-md-2">
										<span class="pull-right muted">{vtranslate('BCC',$MODULENAME)}</span>
									</span>
									<span class="col-md-9">
										<span id="emailPreview_Bcc" class="">
											{$BCC}
										</span>
									</span>
								</div>
							{/if}
							<div class="row padding-bottom1per">
								<span class="col-md-2">
									<span class="pull-right muted">{vtranslate('Subject',$MODULENAME)}</span>
								</span>
								<span class="col-md-9">
									<span id="emailPreview_Subject" class="">
										{$SUBJECT}
									</span>
								</span>
							</div>
							{if !empty($ATTACHMENTS)}
								<div class="row padding-bottom1per">
									<span class="col-md-2">
										<span class="pull-right muted">{vtranslate('Attachments_Exist',$MODULENAME)}</span>
									</span>
									<span class="col-md-9">
										<span id="emailPreview_attachment" class="">
											{foreach item=ATTACHMENT from=$ATTACHMENTS}
												<a &nbsp;
													{if array_key_exists('docid',$ATTACHMENT)}
														&nbsp; href="index.php?module=Documents&action=DownloadFile&record={$ATTACHMENT['docid']}
														&fileid={$ATTACHMENT['id']}"
													{else}
														&nbsp; href="index.php?module=Emails&action=DownloadFile&attachment_id={$ATTACHMENT['id']}"
													{/if}
													>{$ATTACHMENT['file']}</a>&nbsp;&nbsp;
											{/foreach}
										</span>
									</span>
								</div>
							{/if}
							<div class="row padding-bottom1per content">
								<span class="col-md-2">
									<span class="pull-right muted">{vtranslate('Content',$MODULENAME)}</span>
								</span>
								<span class="col-md-10 row">
									<iframe id="emailPreview_Content" class="col-md-12" src="{$URL}" frameborder="0"></iframe>
								</span>
							</div>
							<hr/>

							<div class="textAlignCenter">
								<span class="muted">
									<small><em>{vtranslate('Sent',$MODULENAME)}</em></small>
									<span><small><em>&nbsp;{$SENT}</em></small></span>
								</span>
							</div>
							<div class="textAlignCenter">
								<span><strong> {vtranslate('LBL_OWNER','Emails')} : {getOwnerName($OWNER)}</strong></span>
							</div>
						</form>
					</div>
				</div>
				{if $ISMODAL}
				</div>
			</div>
		</div>
	{/if}
	{if !$NOLOADLIBS}
		{include file='JSResources.tpl'|vtemplate_path}
	{/if}
{/strip}
{if !$ISMODAL}
	<script>
				$('#emailPreview_Content').css('height', document.documentElement.clientHeight - 267);	</script>
{/if}
{literal}
	<script>
				var params = {};
				function printMail(){
				var content = window.open();
						$(".emailPreview > div").each(function(index) {
				if ($(this).hasClass('content')){
				var inframe = $("#emailPreview_Content").contents();
						content.document.write(inframe.find('body').html() + "<br>");
				} else{
				content.document.write($.trim($(this).text()) + "<br>");
				}
				});
						content.print();
				}
	</script>
{/literal}
