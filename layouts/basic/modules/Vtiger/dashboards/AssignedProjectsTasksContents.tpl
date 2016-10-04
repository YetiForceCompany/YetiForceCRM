{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************************************************************/
-->*}
{strip}
<style type="text/css">
small.small-a{
font-size: 75%;
}
</style>
<div>
	{foreach from=$PROJECTSTASKS key=INDEX item=TASKS}
	<div>
		<div class='pull-left'>
			<image style="margin-left: 4px;" alt="{vtranslate('ProjectTask')}" src="{vimage_path('ProjectTask.png')}" width="24px" />&nbsp;&nbsp;
		</div>
		<div>
			<div class='pull-left'>
				{assign var=PROJECT_ID value=$TASKS->get('projectid')}
				{assign var=ACCOUNT value=$TASKS->get('account')}
				<a href="{$TASKS->getDetailViewUrl()}">{$TASKS->get('projecttaskname')|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'|truncate:$NAMELENGHT:'...'}</a>
				{if $PROJECT_ID}
				   <br/><small class='small-a'><strong>{$TASKS->getDisplayValue('projectid')}</strong></small>
				{/if}
				{if $ACCOUNT}
				   - <small class='small-a'><strong>{$ACCOUNT}</strong></small>
				{/if}
			</div>
			{assign var=TARGETENDDATE value=$TASKS->get('targetenddate')}
			<p class='pull-right muted' style='margin-top:5px;padding-right:5px;'><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($TARGETENDDATE)}">{$TARGETENDDATE}</small></p>
			<div class='clearfix'></div>
		</div>
		<div class='clearfix'></div>
	</div>
	{foreachelse}
		<span class="noDataMsg">
			{vtranslate($NODATAMSGLABLE, $MODULE_NAME)}
		</span>
	{/foreach}

{if $PAGING_MODEL->get('nextPageExists') eq 'true'}
	<div class='pull-right' style='margin-top:5px;padding-right:5px;'>
        <a href="javascript:;" name="history_more" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">{vtranslate('LBL_MORE')}...</a>
        <br />
        <br />
        <br />
        <br />
	</div>
{else}
    <br />
    <br />
    <br />
    <br />
{/if}
</div>
{/strip}
