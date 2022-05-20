{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OSSMailView-preview -->
	{if !$NOLOADLIBS}
		{include file=\App\Layout::getTemplatePath('PageHeader.tpl')}
	{/if}
	{if $ISMODAL}
		<div class="modelContainer modal fade" tabindex="-1">
			<div class="modal-dialog modal-blg">
			{/if}
			<div class="{if $ISMODAL}modal-content{else}container-fluid{/if}" id="emailPreview" name="emailPreview">
				<div class="{if $ISMODAL}modal-header{else}blockHeader emailPreviewHeader{/if} flex-wrap flex-md-nowrap">
					<h5 {if $ISMODAL}class="modal-title" {/if}>{\App\Language::translate('emailPreviewHeader',$MODULENAME)}</h5>
					<div class="btn-toolbar order-3 order-md-2 ml-md-auto mt-2 mt-md-0">
						{if \App\Mail::checkMailClient()}
							{if \App\Mail::checkInternalMailClient()}
								{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
								{assign var=COMPOSE_URL value=OSSMail_Module_Model::getComposeUrl($SMODULENAME, $SRECORD, 'Detail')}
								{assign var=POPUP value=$CONFIG['popup']}
								<button type="button" class="btn btn-sm btn-outline-success sendMailBtn mr-1"
									data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=reply"
									data-popup="{$POPUP}">
									<span class="fas fa-reply mr-1"></span>
									<strong>{\App\Language::translate('LBL_REPLY','OSSMailView')}</strong>
								</button>
								<button type="button" class="btn btn-sm btn-outline-secondary sendMailBtn mr-1"
									data-url="{$COMPOSE_URL}&mid={$RECORD_MODEL->getId()}&type=replyAll"
									data-popup="{$POPUP}">
									<span class="fas fa-reply-all mr-1"></span>
									<strong>{\App\Language::translate('LBL_REPLYALLL','OSSMailView')}</strong>
								</button>
								<button type="button" class="btn btn-sm btn-outline-primary sendMailBtn mr-1"
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
				<hr class="my-0">
				<form class="form-horizontal emailPreview px-3">
					<div class="d-flex col-md-12 px-0 align-items-center mb-1">
						<div class="firstLetter {$FIRSTLETTERBG} d-sm-block d-none mr-2">
							{$FIRSTLETTER}
						</div>
						<div class="col-md-6 px-0">
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								<span class="muted">{\App\Language::translate('From',$MODULENAME)}</span>: <span id="emailPreview_From" class="">{$RECORD_MODEL->getDisplayValue('from_email')}</span>
							</p>
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								<span class="muted">{\App\Language::translate('To',$MODULENAME)}</span>: <span id="emailPreview_To" class="">{assign var=TO_EMAILS value=","|implode:$TO}{$TO_EMAILS}</span>
							</p>
							{if !empty($CC)}
								<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
									<span class="muted">{\App\Language::translate('CC',$MODULENAME)}</span>: <span id="emailPreview_Cc" class="">{$CC}</span>
								</p>
							{/if}
							{if !empty($BCC)}
								<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
									<span class="muted">{\App\Language::translate('BCC',$MODULENAME)}</span>: <span id="emailPreview_Bcc" class="">{$BCC}</span>
								</p>
							{/if}
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								<span class="muted">{\App\Language::translate('Subject',$MODULENAME)}</span>: <span id="emailPreview_Subject" class="">{$RECORD_MODEL->getDisplayValue('subject')}</span>
							</p>
						</div>
						<div class="col-md-6 px-0">
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								<span class="muted">{\App\Language::translate('LBL_SENT_DATE',$MODULENAME)}</span>: {$SENT}
							</p>
							<p class="mb-0 u-fs-15px u-lh-12 u-text-ellipsis">
								<span class="muted">{\App\Language::translate('LBL_OWNER')}</span>: {\App\Fields\Owner::getLabel($OWNER)}
							</p>
						</div>
					</div>
					{if !empty($ATTACHMENTS)}
						<div class="w-100">
							<span class="muted mr-2">{\App\Language::translate('Attachments_Exist',$MODULENAME)}:</span>
							<span id="emailPreview_attachment" class="">
								{foreach item=ATTACHMENT from=$ATTACHMENTS}
									<a class="btn btn-sm btn-primary mr-1 mb-1" href="file.php?module=OSSMailView&action=DownloadFile&record={$RECORD}&attachment={$ATTACHMENT['id']}">
										<span class="fas fa-paperclip mr-1"></span>
										{$ATTACHMENT['file']}
									</a>
								{/foreach}
							</span>
						</div>
					{/if}
					<hr />
					<div class="no-gutters pb-1 content">
						{$CONTENT}
					</div>
				</form>
			</div>
			{if $ISMODAL}
			</div>
		</div>
	{/if}
	{if !$NOLOADLIBS}
		{include file=\App\Layout::getTemplatePath('PageFooter.tpl')}
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
