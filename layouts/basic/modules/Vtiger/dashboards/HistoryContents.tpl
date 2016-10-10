{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
   * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
<div style='padding:5px;'>
{if $HISTORIES neq false}
	{foreach key=$index item=HISTORY from=$HISTORIES}
		{assign var=MODELNAME value=get_class($HISTORY)}
		{if $MODELNAME == 'ModTracker_Record_Model'}
			{assign var=USER value=$HISTORY->getModifiedBy()}
			{assign var=TIME value=$HISTORY->getActivityTime()}
			{assign var=PARENT value=$HISTORY->getParent()}
			{assign var=MOD_NAME value=$HISTORY->getParent()->getModule()->getName()}
			{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MOD_NAME}
			{assign var=TRANSLATED_MODULE_NAME value = vtranslate($SINGLE_MODULE_NAME ,$MOD_NAME)}
			{assign var=PROCEED value= TRUE}
			{if ($HISTORY->isRelationLink()) or ($HISTORY->isRelationUnLink())}
				{assign var=RELATION value=$HISTORY->getRelationInstance()}
				{if !($RELATION->getLinkedRecord())}
					{assign var=PROCEED value= FALSE}
				{/if}
			{/if}
			{if $PROCEED}
				<div class="row">
					<div class='col-md-1'>
						{if vimage_path($MOD_NAME|cat:'.png')}
							<img width='24px' src="{vimage_path($MOD_NAME|cat:'.png')}" alt="{$TRANSLATED_MODULE_NAME}" title="{$TRANSLATED_MODULE_NAME}" />&nbsp;&nbsp;
						{else}
							<span class="glyphicon glyphicon-menu-hamburger icon-in-history-widget" title="{$TRANSLATED_MODULE_NAME}"></span>
						{/if}
					</div>
					<div class="col-md-11">
					<p class="pull-right muted" style="padding-right:5px;"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$TIME")}</small></p>
					{assign var=DETAILVIEW_URL value=$PARENT->getDetailViewUrl()}
					{if $HISTORY->isUpdate()}
						{assign var=FIELDS value=$HISTORY->getFieldInstances()}
						<div class="">
							<div><strong>{$USER->getName()}&nbsp;</strong> {vtranslate('LBL_UPDATED')}&nbsp; <a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
								onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>
								{$PARENT->getName()}</a>
							</div>
							{foreach from=$FIELDS key=INDEX item=FIELD}
							{if $INDEX lt 2}
								{if $FIELD && $FIELD->getFieldInstance() && $FIELD->getFieldInstance()->isViewableInDetailView()}
								<div class='font-x-small'>
									<span>{vtranslate($FIELD->getName(), $FIELD->getModuleName())}</span>
									{if $FIELD->get('prevalue') neq '' && $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELD->get('postvalue') eq '0' || $FIELD->get('prevalue') eq '0'))}
										&nbsp;{vtranslate('LBL_FROM')}&nbsp; <strong>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getDisplayValue(decode_html($FIELD->get('prevalue'))))}</strong>
									{else if $FIELD->get('postvalue') eq '' || ($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
	                                    &nbsp; <strong> {vtranslate('LBL_DELETED')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getDisplayValue(decode_html($FIELD->get('prevalue'))))}</del> )
	                                {else}
										&nbsp;{vtranslate('LBL_CHANGED')}
									{/if}
	                                {if $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
										&nbsp;{vtranslate('LBL_TO')}&nbsp;<strong>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getDisplayValue(decode_html($FIELD->get('postvalue'))))}</strong>
	                                {/if}    
								</div>
								{/if}
							{else}
								<a class="btn btn-info btn-xs moreBtn" href="{$PARENT->getUpdatesUrl()}">{vtranslate('LBL_MORE')}</a>
								{break}
							{/if}
							{/foreach}
						</div>
					{else if $HISTORY->isCreate()}
						<div style='margin-top:5px'>
							<strong>{$USER->getName()}&nbsp;</strong> {vtranslate('LBL_ADDED')} <a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
								onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>
								&nbsp;{$PARENT->getName()}</a>
						</div>
					{else if $HISTORY->isDisplayed()}
						<div style='margin-top:5px'>
							<strong>{$USER->getName()}&nbsp;</strong> {vtranslate('LBL_DISPLAYED')} <a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
								onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>
								&nbsp;{$PARENT->getName()}</a>
						</div>
					{else if ($HISTORY->isRelationLink() || $HISTORY->isRelationUnLink())}
						{assign var=RELATION value=$HISTORY->getRelationInstance()}
						{assign var=LINKED_RECORD_DETAIL_URL value=$RELATION->getLinkedRecord()->getDetailViewUrl()}
						{assign var=PARENT_DETAIL_URL value=$RELATION->getParent()->getParent()->getDetailViewUrl()}
						<div class='' style='margin-top:5px'>
							<strong>{$USER->getName()}&nbsp;</strong>
								{if $HISTORY->isRelationLink()}
									{vtranslate('LBL_ADDED', $MODULE_NAME)}&nbsp;
								{else}
									{vtranslate('LBL_REMOVED', $MODULE_NAME)}
								{/if}
								{if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
									{if isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId()) eq 'yes'}
										<a class="cursorPointer" {if stripos($LINKED_RECORD_DETAIL_URL, 'javascript:')===0} onclick='{$LINKED_RECORD_DETAIL_URL|substr:strlen("javascript:")}'
											{else} onclick='window.location.href="{$LINKED_RECORD_DETAIL_URL}"' {/if}>{$RELATION->getLinkedRecord()->getName()}</a>
									{else}
										{vtranslate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())}
									{/if}
								{else}
								 <a class="cursorPointer" {if stripos($LINKED_RECORD_DETAIL_URL, 'javascript:')===0} onclick='{$LINKED_RECORD_DETAIL_URL|substr:strlen("javascript:")}'
									{else} onclick='window.location.href="{$LINKED_RECORD_DETAIL_URL}"' {/if}>{vtranslate($RELATION->getLinkedRecord()->getName(), $RELATION->getLinkedRecord()->getModuleName() )}</a>
								{/if}{vtranslate('LBL_FOR')} <a class="cursorPointer" {if stripos($PARENT_DETAIL_URL, 'javascript:')===0}
								onclick='{$PARENT_DETAIL_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$PARENT_DETAIL_URL}"' {/if}>
								{$RELATION->getParent()->getParent()->getName()}</a>
						</div>
					{else if $HISTORY->isRestore()}
						<div class=''  style='margin-top:5px'>
							<strong>{$USER->getName()}&nbsp;</strong> {vtranslate('LBL_RESTORED')} <a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
								onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>
								{$PARENT->getName()}</a>
						</div>
					{else if $HISTORY->isDelete()}
						<div class=''  style='margin-top:5px'>
							<strong>{$USER->getName()}&nbsp;</strong> {vtranslate('LBL_DELETED')} <a class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
								onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>
								{$PARENT->getName()}</a>
						</div>
					{/if}
					</div>
				</div>
			{/if}
			{else if $MODELNAME == 'ModComments_Record_Model'}
			{assign var=TRANSLATED_MODULE_NAME value = vtranslate('SINGLE_ModComments' ,'ModComments')}
			<div class="row">
				<div class="col-md-1">
					<img width='24px' src="{vimage_path('ModComments.png')}" alt="{$TRANSLATED_MODULE_NAME}" title="{$TRANSLATED_MODULE_NAME}" />&nbsp;&nbsp;
				</div>
				<div class="col-md-11">
					{assign var=COMMENT_TIME value=$HISTORY->getCommentedTime()}
					<p class="pull-right muted" style="padding-right:5px;"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$COMMENT_TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$COMMENT_TIME")}</small></p>
					<div>
						<strong>{$HISTORY->getCommentedByModel()->getName()}</strong> {vtranslate('LBL_COMMENTED')} {vtranslate('LBL_ON')} <a class="textOverflowEllipsis" href="{$HISTORY->getParentRecordModel()->getDetailViewUrl()}">{$HISTORY->getParentRecordModel()->getName()}</a>
					</div>
					<div class='font-x-small'><span>"{nl2br($HISTORY->get('commentcontent'))}"</span></div>
				</div>
			</div>
		{/if}
	{/foreach}

	{if $NEXTPAGE}
	<div class="row">
		<div class="col-md-12">
			<button class="load-more btn btn-xs btn-info" data-page="{$PAGE}" data-nextpage="{$NEXTPAGE}">{vtranslate('LBL_MORE')}</button>
		</div>
	</div>
	{/if}

{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_UPDATES_OR_COMMENTS', $MODULE_NAME)}
	</span>
{/if}
</div>
{/strip}
