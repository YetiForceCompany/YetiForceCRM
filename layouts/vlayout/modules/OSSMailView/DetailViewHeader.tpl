{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
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
    content.document.write("<b>"+app.vtranslate('Subject')+": "+subject+"</b><br>");
    content.document.write("<br>"+app.vtranslate('From')+" :" +from +"<br>");
    content.document.write(""+app.vtranslate('To')+" :" +to+"<br>");
    cc == null ? '' : content.document.write(""+app.vtranslate('CC')+" :" +cc+"<br>");
    content.document.write(""+app.vtranslate('Date')+" :" + date+"<br>");
    content.document.write("<hr/>"+body +"<br>");
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
		<div class="row-fluid detailViewTitle">
			<div class="span12">
				<div class="row-fluid">
					<div class="span7">
						<div class="row-fluid">
							{include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE}
						</div>
					</div>
					<div class="span5">
						<div class="pull-right detailViewButtoncontainer">
							<div class="btn-toolbar">
							<span class="btn-group">
								<a style="padding: 4px 7px 1px 7px;" class="btn" href="index.php?module=OSSMail&view=compose&id={$RECORD->getId()}&type=replyAll">
									<img src="layouts/vlayout/modules/OSSMailView/previewReplyAll.png" alt="{vtranslate('LBL_REPLYALLL','OSSMailView')}" title="{vtranslate('LBL_REPLYALLL','OSSMailView')}">
								</a>
							</span>
							<span class="btn-group">
								<a style="padding: 4px 7px 1px 7px;" class="btn" href="index.php?module=OSSMail&view=compose&id={$RECORD->getId()}&type=reply">
									<img src="layouts/vlayout/modules/OSSMailView/previewReply.png" alt="{vtranslate('LBL_REPLY','OSSMailView')}" title="{vtranslate('LBL_REPLY','OSSMailView')}">
								</a>
							</span>
							<span class="btn-group">
								<a style="padding: 4px 7px 1px 7px;" class="btn" href="index.php?module=OSSMail&view=compose&id={$RECORD->getId()}&type=reply">
									<span class="icon-share-alt" alt="{vtranslate('LBL_FORWARD','OSSMailView')}" title="{vtranslate('LBL_FORWARD','OSSMailView')}"></span>
								</a>
							</span>
							<span class="btn-group">
								<button style="padding: 4px 7px 1px 7px;" id="previewPrint" onclick="printMail();" title="{vtranslate('LBL_PRINT','OSSMailView')}" type="button" name="previewPrint" class="btn " data-mode="previewPrint">
									<img src="layouts/vlayout/modules/OSSMailView/previewPrint.png" alt="{vtranslate('LBL_PRINT','OSSMailView')}" title="{vtranslate('LBL_PRINT','OSSMailView')}">
								</button>
							</span>
							{foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
							<span class="btn-group">
								<button class="btn" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
									{if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
										onclick="window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
									{else}
										onclick={$DETAIL_VIEW_BASIC_LINK->getUrl()}
									{/if}>
									<strong>{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}</strong>
								</button>
							</span>
							{/foreach}
							{if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
							<span class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown" >
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
									<button class="btn dropdown-toggle" href="#" data-toggle="dropdown"><span class="icon-wrench" alt="{vtranslate('LBL_SETTINGS', $MODULE_NAME)}" title="{vtranslate('LBL_SETTINGS', $MODULE_NAME)}"></span>&nbsp;&nbsp;<span class="caret"></span></button>
									<ul class="listViewSetting dropdown-menu">
										{foreach item=DETAILVIEW_SETTING from=$DETAILVIEW_LINKS['DETAILVIEWSETTING']}
											<li><a href={$DETAILVIEW_SETTING->getUrl()}>{vtranslate($DETAILVIEW_SETTING->getLabel(), $MODULE_NAME)}</a></li>
										{/foreach}
									</ul>
								</span>
							{/if}
							<span class="btn-group">
								<button class="btn" id="detailViewPreviousRecordButton" {if empty($PREVIOUS_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href='{$PREVIOUS_RECORD_URL}'" {/if}><span class="icon-chevron-left"></span></button>
								<button class="btn" id="detailViewNextRecordButton" {if empty($NEXT_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href='{$NEXT_RECORD_URL}'" {/if}><span class="icon-chevron-right"></span></button>
							</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="detailViewInfo row-fluid">
			<div class="{if $NO_PAGINATION} span12 {else} span10 {/if} details">
				<form id="detailView" data-name-fields='{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}'>
					<div class="contents">
{/strip}
