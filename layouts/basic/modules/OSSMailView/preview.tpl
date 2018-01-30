{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
							<h3 class='col-md-4 pushDown'>{\App\Language::translate('emailPreviewHeader',$MODULENAME)}</h3>
							<div class='float-right'>
								<div class="btn-toolbar" >
									{if AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
										{if $USER_MODEL->get('internal_mailer') == 1}
											{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}	
											{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
											{assign var=POPUP value=$CONFIG['popup']}
											<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=reply" data-popup="{$POPUP}" title="{\App\Language::translate('LBL_REPLY','OSSMailView')}">
												<img width="14px" src="{\App\Layout::getLayoutFile('modules/OSSMailView/previewReply.png')}" alt="{\App\Language::translate('LBL_REPLY','OSSMailView')}">
												&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_REPLY','OSSMailView')}</strong>
											</button>
											<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=replyAll" data-popup="{$POPUP}" title="{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}">
												<img width="14px" src="{\App\Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png')}" alt="{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}">
												&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}</strong>
											</button>
											<button type="button" class="btn btn-sm btn-light sendMailBtn" data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=forward" data-popup="{$POPUP}" title="{\App\Language::translate('LBL_FORWARD','OSSMailView')}">
												<span class="fa fa-share"></span>
												&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_FORWARD','OSSMailView')}</strong>
											</button>
										{else}
											<a class="btn btn-sm btn-light" href="{OSSMail_Module_Model::getExternalUrlForWidget($RECORD_MODEL, 'reply')}" title="{\App\Language::translate('LBL_CREATEMAIL', 'OSSMailView')}">
												<img width="14px" src="{\App\Layout::getLayoutFile('modules/OSSMailView/previewReply.png')}" alt="{\App\Language::translate('LBL_REPLY','OSSMailView')}">
												&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_REPLY','OSSMailView')}</strong>
											</a>
											<a class="btn btn-sm btn-light" href="{OSSMail_Module_Model::getExternalUrlForWidget($RECORD_MODEL, 'replyAll')}" title="{\App\Language::translate('LBL_REPLYALLL', 'OSSMailView')}">
												<img width="14px" src="{\App\Layout::getLayoutFile('modules/OSSMailView/previewReplyAll.png')}" alt="{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}">
												&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}</strong>
											</a>
											<a class="btn btn-sm btn-light" href="{OSSMail_Module_Model::getExternalUrlForWidget($RECORD_MODEL, 'forward')}" title="{\App\Language::translate('LBL_FORWARD', 'OSSMailView')}">
												<span class="fa fa-share"></span>
												&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_FORWARD','OSSMailView')}</strong>
											</a>
										{/if}
									{/if}
									{if \App\Privilege::isPermitted($MODULENAME, 'PrintMail')}
										<span class="btn-group">
											<button id="previewPrint" onclick="OSSMailView_preview_Js.printMail();" type="button" name="previewPrint" class="btn btn-sm btn-light" data-mode="previewPrint">
												<span class="glyphicon glyphicon-print" aria-hidden="true"></span>&nbsp;&nbsp;
												<strong>{\App\Language::translate('LBL_PRINT',$MODULENAME)}</strong>
											</button>
										</span>
									{/if}
									{if $ISMODAL}
										<span class="btn-group">
											<button type="button" class="btn btn-sm btn-danger" data-dismiss="modal" aria-label="Close">
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
									<span class="float-right muted">{\App\Language::translate('From',$MODULENAME)}</span>
								</span>
								<span class="col-md-9">
									<span id="emailPreview_From" class="">{$FROM}</span>
								</span>
							</div>
							<div class="row padding-bottom1per">
								<span class="col-md-2">
									<span class="float-right muted">{\App\Language::translate('To',$MODULENAME)}</span>
								</span>
								<span class="col-md-9">
									<span id="emailPreview_To" class="">{assign var=TO_EMAILS value=","|implode:$TO}{$TO_EMAILS}</span>
								</span>
							</div>
							{if !empty($CC)}
								<div class="row padding-bottom1per">
									<span class="col-md-2">
										<span class="float-right muted">{\App\Language::translate('CC',$MODULENAME)}</span>
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
										<span class="float-right muted">{\App\Language::translate('BCC',$MODULENAME)}</span>
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
									<span class="float-right muted">{\App\Language::translate('Subject',$MODULENAME)}</span>
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
										<span class="float-right muted">{\App\Language::translate('Attachments_Exist',$MODULENAME)}</span>
									</span>
									<span class="col-md-9">
										<span id="emailPreview_attachment" class="">
											{foreach item=ATTACHMENT from=$ATTACHMENTS}
												<a class="btn btn-xs btn-primary" title="{$ATTACHMENT['name']}" 
												   href="file.php?module=Documents&action=DownloadFile&record={$ATTACHMENT['id']}">
													<span class="glyphicon glyphicon-paperclip"></span>&nbsp;&nbsp;{$ATTACHMENT['file']}</a>&nbsp;&nbsp;
											{/foreach}
										</span>
									</span>
								</div>
							{/if}
							<div class="row padding-bottom1per content">
								<span class="col-md-2">
									<span class="float-right muted">{\App\Language::translate('Content',$MODULENAME)}</span>
								</span>
								<span class="col-md-10 row">
									<iframe id="emailPreview_Content" class="col-md-12" src="{$URL}" frameborder="0"></iframe>
								</span>
							</div>
							<hr/>

							<div class="textAlignCenter">
								<span class="muted">
									<small><em>{\App\Language::translate('Sent',$MODULENAME)}</em></small>
									<span><small><em>&nbsp;{$SENT}</em></small></span>
								</span>
							</div>
							<div class="textAlignCenter">
								<span><strong> {\App\Language::translate('LBL_OWNER')} : {\App\Fields\Owner::getLabel($OWNER)}</strong></span>
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
		{include file=\App\Layout::getTemplatePath('JSResources.tpl')}
	{/if}
{/strip}
{if !$ISMODAL}
	<script>
		$('#emailPreview_Content').css('height', document.documentElement.clientHeight - 267);
	</script>
{else}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
{/if}
