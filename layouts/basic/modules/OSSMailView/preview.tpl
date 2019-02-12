{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OSSMailView-preview -->
	{if !$NOLOADLIBS}
		{include file="modules/Vtiger/Header.tpl"}
	{/if}
{if $ISMODAL}
	<div class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-blg">
			{/if}
			<div class="{if $ISMODAL}modal-content{else}container-fluid{/if}" id="emailPreview" name="emailPreview">
				<div class="{if $ISMODAL}modal-header{else}blockHeader emailPreviewHeader{/if} flex-wrap flex-md-nowrap">
					<h5 {if $ISMODAL}class="modal-title"{/if}>{\App\Language::translate('emailPreviewHeader',$MODULENAME)}</h5>
					<div class="btn-toolbar order-3 order-md-2 ml-md-auto mt-2 mt-md-0">
						{if AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')}
							{if $USER_MODEL->get('internal_mailer') == 1}
								{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
								{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
								{assign var=POPUP value=$CONFIG['popup']}
								<button type="button" class="btn btn-sm btn-light sendMailBtn mr-1"
										data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=reply"
										data-popup="{$POPUP}">
									<span class="fas fa-reply mr-1"></span>
									<strong>{\App\Language::translate('LBL_REPLY','OSSMailView')}</strong>
								</button>
								<button type="button" class="btn btn-sm btn-light sendMailBtn mr-1"
										data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=replyAll"
										data-popup="{$POPUP}">
									<span class="fas fa-reply-all mr-1"></span>
									<strong>{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}</strong>
								</button>
								<button type="button" class="btn btn-sm btn-light sendMailBtn mr-1"
										data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=forward"
										data-popup="{$POPUP}">
									<span class="fas fa-share mr-1"></span>
									<strong>{\App\Language::translate('LBL_FORWARD','OSSMailView')}</strong>
								</button>
							{else}
								<a class="btn btn-sm btn-light" role="button"
								   href="{OSSMail_Module_Model::getExternalUrlForWidget($RECORD_MODEL, 'reply')}">
									<span class="fas fa-reply mr-1"></span>
									<strong>{\App\Language::translate('LBL_REPLY','OSSMailView')}</strong>
								</a>
								<a class="btn btn-sm btn-light" role="button"
								   href="{OSSMail_Module_Model::getExternalUrlForWidget($RECORD_MODEL, 'replyAll')}">
									<span class="fas fa-reply-all mr-1"></span>
									<strong>{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}</strong>
								</a>
								<a class="btn btn-sm btn-light" role="button"
								   href="{OSSMail_Module_Model::getExternalUrlForWidget($RECORD_MODEL, 'forward')}">
									<span class="fas fa-share mr-1"></span>
									<strong>{\App\Language::translate('LBL_FORWARD','OSSMailView')}</strong>
								</a>
							{/if}
						{/if}
						{if \App\Privilege::isPermitted($MODULENAME, 'PrintMail')}
							<div class="btn-group">
								<button id="previewPrint" onclick="OSSMailView_Preview_Js.printMail();"
										type="button" name="previewPrint" class="btn btn-sm btn-light"
										data-mode="previewPrint">
									<span class="fas fa-print mr-1"></span>
									<strong>{\App\Language::translate('LBL_PRINT',$MODULENAME)}</strong>
								</button>
							</div>
						{/if}
					</div>
					{if $ISMODAL}
						<button type="button" class="close order-2 order-md-3 ml-1" data-dismiss="modal"
								aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					{/if}
				</div>
				<hr>
				<form class="form-horizontal emailPreview">
					<div class="row no-gutters padding-bottom1per">
								<span class="col-2">
									<span class="float-right muted">{\App\Language::translate('From',$MODULENAME)}</span>
								</span>
						<span class="col-10 pl-2 pl-md-4">
									<span id="emailPreview_From" class="">{$FROM}</span>
								</span>
					</div>
					<div class="row no-gutters padding-bottom1per">
								<span class="col-2">
									<span class="float-right muted">{\App\Language::translate('To',$MODULENAME)}</span>
								</span>
						<span class="col-10 pl-2 pl-md-4">
									<span id="emailPreview_To"
										  class="">{assign var=TO_EMAILS value=","|implode:$TO}{$TO_EMAILS}</span>
								</span>
					</div>
					{if !empty($CC)}
						<div class="row no-gutters padding-bottom1per">
									<span class="col-2">
										<span class="float-right muted">{\App\Language::translate('CC',$MODULENAME)}</span>
									</span>
							<span class="col-10 pl-2 pl-md-4">
										<span id="emailPreview_Cc" class="">
											{$CC}
										</span>
									</span>
						</div>
					{/if}
					{if !empty($BCC)}
						<div class="row no-gutters padding-bottom1per">
									<span class="col-2">
										<span class="float-right muted">{\App\Language::translate('BCC',$MODULENAME)}</span>
									</span>
							<span class="col-10 pl-2 pl-md-4">
										<span id="emailPreview_Bcc" class="">
											{$BCC}
										</span>
									</span>
						</div>
					{/if}
					<div class="row no-gutters padding-bottom1per">
								<span class="col-2">
									<span class="float-right muted">{\App\Language::translate('Subject',$MODULENAME)}</span>
								</span>
						<span class="col-10 pl-2 pl-md-4">
									<span id="emailPreview_Subject" class="">
										{$SUBJECT}
									</span>
								</span>
					</div>
					{if !empty($ATTACHMENTS)}
						<div class="row no-gutters padding-bottom1per">
									<span class="col-2">
										<span class="float-right muted">{\App\Language::translate('Attachments_Exist',$MODULENAME)}</span>
									</span>
							<span class="col-10 pl-2 pl-md-4">
										<span id="emailPreview_attachment" class="">
											{foreach item=ATTACHMENT from=$ATTACHMENTS}
												<a class="btn btn-sm btn-primary"
												   href="file.php?module=OSSMailView&action=DownloadFile&record={$RECORD}&attachment={$ATTACHMENT['id']}">
													<span class="fas fa-paperclip mr-1"></span>
													{$ATTACHMENT['file']}
												</a>
											{/foreach}
										</span>
									</span>
						</div>
					{/if}
					<div class="row no-gutters padding-bottom1per content">
								<span class="col-2">
									<span class="float-right muted">{\App\Language::translate('Content',$MODULENAME)}</span>
								</span>
						<span class="col-md-10 row no-gutters">
									<iframe id="emailPreview_Content" class="col-12 {if $ISMODAL}u-h-70vh{/if}"
											src="{$URL}"
											frameborder="0"></iframe>
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
					<span><strong> {\App\Language::translate('LBL_OWNER')}
							: {\App\Fields\Owner::getLabel($OWNER)}</strong></span>
					</div>
				</form>
			</div>
			{if $ISMODAL}
		</div>
	</div>
{/if}
	{if !$NOLOADLIBS}
		{include file=\App\Layout::getTemplatePath('JSResources.tpl')}
	{/if}
	{if !$ISMODAL}
		<script>
			$('#emailPreview_Content').css('height', document.documentElement.clientHeight - 267);
		</script>
	{else}
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
	{/if}
	<!-- /tpl-OSSMailView-preview -->
{/strip}