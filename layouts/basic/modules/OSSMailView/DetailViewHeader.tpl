{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

{literal}
	<script>
		function printMail(){
		var subject = $('#subject').val();
				var from = $('#from_email').val();
				var to = $('#to_email').val();
				var cc = $('#cc_email').val();
				var date = jQuery('#createdtime').val();
				var body = $('#content').html();
				var content = window.open();
				content.document.write("<b>" + app.vtranslate('Subject') + ": " + subject + "</b><br>");
				content.document.write("<br>" + app.vtranslate('From') + " :" + from + "<br>");
				content.document.write("" + app.vtranslate('To') + " :" + to + "<br>");
				cc == null ? '' : content.document.write("" + app.vtranslate('CC') + " :" + cc + "<br>");
				content.document.write("" + app.vtranslate('Date') + " :" + date + "<br>");
				content.document.write("<hr/>" + body + "<br>");
				content.print();
		}
	</script>
{/literal}
{strip}
	{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
	<input id="recordId" type="hidden" value="{$RECORD->getId()}" />
	<input id="from_email" type="hidden" value="{$RECORD->get('from_email')}" />
	<input id="to_email" type="hidden" value="{$RECORD->get('to_email')}" />
	<input id="cc_email" type="hidden" value="{$RECORD->get('cc_email')}" />
	<input id="subject" type="hidden" value="{$RECORD->get('subject')}" />
	<input id="createdtime" type="hidden" value="{$RECORD->get('createdtime')}" />
	<div id="content" style="display: none;">{$RECORD->get('content')}</div>
	<div class="detailViewContainer">
		<div class="row detailViewTitle">
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-6">
						<div class="row">
							{include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE}
						</div>
					</div>
					<div class="col-md-6">
						<div class="pull-right detailViewButtoncontainer">
							<div class="btn-toolbar">
								{if vglobal('isActiveSendingMails')}
									<span class="btn-group">
										{assign var=CONFIG value=OSSMail_Module_Model::getComposeParameters()}
										<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD->getId()}&type=replyAll{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})">
											<img src="layouts/vlayout/modules/OSSMailView/previewReplyAll.png" alt="{vtranslate('LBL_REPLYALLL',$MODULE)}" title="{vtranslate('LBL_REPLYALLL',$MODULE)}">
										</a>
									</span>
									<span class="btn-group">
										<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD->getId()}&type=reply{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})">
											<img src="layouts/vlayout/modules/OSSMailView/previewReply.png" alt="{vtranslate('LBL_REPLY',$MODULE)}" title="{vtranslate('LBL_REPLY',$MODULE)}">
										</a>
									</span>
									<span class="btn-group">
										<a class="btn btn-default" onclick="window.open('index.php?module=OSSMail&view=compose&id={$RECORD->getId()}&type=forward{if $CONFIG['popup']}&popup=1{/if}',{if !$CONFIG['popup']}'_self'{else}'_blank', 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})">
											<span class="glyphicon glyphicon-share-alt" alt="{vtranslate('LBL_FORWARD',$MODULE)}" title="{vtranslate('LBL_FORWARD',$MODULE)}"></span>
										</a>
									</span>
								{/if}
								{if Users_Privileges_Model::isPermitted($MODULE, 'PrintMail')}
									<span class="btn-group">
										<button id="previewPrint" onclick="printMail();" title="{vtranslate('LBL_PRINT',$MODULE)}" type="button" name="previewPrint" class="btn btn-default" data-mode="previewPrint">
											<img src="layouts/vlayout/modules/OSSMailView/previewPrint.png" alt="{vtranslate('LBL_PRINT',$MODULE)}" title="{vtranslate('LBL_PRINT',$MODULE)}">
										</button>
									</span>
								{/if}
								{foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
									<span class="btn-group">
										<button class="btn btn-default" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
												{if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
													onclick="window.location.href = '{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
												{else}
													onclick={$DETAIL_VIEW_BASIC_LINK->getUrl()}
												{/if}>
											<strong>{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}</strong>
										</button>
									</span>
								{/foreach}
								{if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
									<span class="btn-group">
										<button class="btn dropdown-toggle btn-default" data-toggle="dropdown" >
											<strong>{vtranslate('LBL_MORE', $MODULE_NAME)}</strong>&nbsp;&nbsp;<span class="caret"></span>
										</button>
										<ul class="dropdown-menu pull-right">
											{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
												<li id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
													<a href={$DETAIL_VIEW_LINK->getUrl()} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
												</li>
											{/foreach}
										</ul>
									</span>
								{/if}
								{if $DETAILVIEW_LINKS['DETAILVIEWSETTING']|@count gt 0}
									<span class="btn-group">
										<button class="btn dropdown-toggle btn-default" href="#" data-toggle="dropdown"><span class="glyphicon glyphicon-wrench" alt="{vtranslate('LBL_SETTINGS', $MODULE_NAME)}" title="{vtranslate('LBL_SETTINGS', $MODULE_NAME)}"></span>&nbsp;&nbsp;<span class="caret"></span></button>
										<ul class="listViewSetting dropdown-menu dropdown-menu-right">
											{foreach item=DETAILVIEW_SETTING from=$DETAILVIEW_LINKS['DETAILVIEWSETTING']}
												<li><a href={$DETAILVIEW_SETTING->getUrl()}>{vtranslate($DETAILVIEW_SETTING->getLabel(), $MODULE_NAME)}</a></li>
												{/foreach}
										</ul>
									</span>
								{/if}
								<span class="btn-group">
									<button class="btn btn-default" id="detailViewPreviousRecordButton" {if empty($PREVIOUS_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href = '{$PREVIOUS_RECORD_URL}'" {/if}><span class="glyphicon glyphicon-chevron-left"></span></button>
									<button class="btn btn-default" id="detailViewNextRecordButton" {if empty($NEXT_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href = '{$NEXT_RECORD_URL}'" {/if}><span class="glyphicon glyphicon-chevron-right"></span></button>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="detailViewInfo row">
			<div class="{if $NO_PAGINATION} col-md-12 {else} col-md-10 {/if} details">
				<form id="detailView" data-name-fields='{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}'>
					<div class="contents">
					{/strip}
