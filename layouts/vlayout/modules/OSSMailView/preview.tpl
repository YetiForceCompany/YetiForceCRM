{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

{strip}
{if !$NOLOADLIBS}
	{include file="modules/Vtiger/Header.tpl"}
{/if}
<div class="SendEmailFormStep2" id="emailPreview" name="emailPreview">
			<div class="well zeroPaddingAndMargin">
				<div class="blockHeader emailPreviewHeader">
					<h3 class='col-md-4 pushDown'>{vtranslate('emailPreviewHeader','OSSMailView')}</h3>
					<div class='pull-right'>
						<div class="btn-toolbar" >
							<span class="btn-group">
								{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
								<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD_MODEL->getId()}&type=replyAll{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})">
									<img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReplyAll.png">
									<strong>{vtranslate('LBL_REPLYALLL','OSSMailView')}</strong>
								</a>
							</span>
							<span class="btn-group">
								<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD_MODEL->getId()}&type=reply{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})">
									<img width="14px" src="layouts/vlayout/modules/OSSMailView/previewReply.png" >
									<strong>{vtranslate('LBL_REPLY','OSSMailView')}</strong>
								</a>
							</span>
							<span class="btn-group">
								<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD_MODEL->getId()}&type=forward{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})">
									<i class="icon-share-alt"></i>
									<strong>{vtranslate('LBL_FORWARD','OSSMailView')}</strong>
								</a>
							</span>
							<span class="btn-group">
								 <button id="previewPrint" onclick="printMail();" type="button" name="previewPrint" class="btn btn-default" data-mode="previewPrint">
									<span class="icon-print"></span>
									<strong>{vtranslate('LBL_PRINT','OSSMailView')}</strong>
								</button>
							</span>
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
{if !$NOLOADLIBS}
	{include file='JSResources.tpl'|vtemplate_path}
{/if}
{/strip}
{literal}
<script>
var params = {};
$('#emailPreview_Content').css('height', document.documentElement.clientHeight - 267);
function printMail(){
    var content = window.open();
	$( ".emailPreview > div" ).each(function( index ) {
		if( $( this ).hasClass( 'content' ) ){
			var inframe = $( "#emailPreview_Content" ).contents();
			content.document.write( inframe. find('body'). html() +"<br>");
		}else{
			content.document.write( $.trim( $( this ).text() ) +"<br>");
		}
	});
    content.print();
}
</script>
{/literal}
