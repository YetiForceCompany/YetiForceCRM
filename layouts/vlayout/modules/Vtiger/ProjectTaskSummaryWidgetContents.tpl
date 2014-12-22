{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{foreach item=HEADER from=$RELATED_HEADERS}
		{if $HEADER->get('label') eq "Project Task Name"}
			{assign var=TASK_NAME_HEADER value={vtranslate($HEADER->get('label'),$MODULE_NAME)}}
		{elseif $HEADER->get('label') eq "Progress"}
			{assign var=TASK_PROGRESS_HEADER value={vtranslate($HEADER->get('label'),$MODULE_NAME)}}
		{/if}
	{/foreach}
	<div class="row-fluid">		
		<span class="span7">
			<strong>{$TASK_NAME_HEADER}</strong>
		</span>
		<span class="span4">
			<span class="pull-right">
				<strong>{$TASK_PROGRESS_HEADER}</strong>
			</span>
		</span>
	</div>
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="recentActivitiesContainer">
			<ul class="unstyled">
				<li>
					<div class="row-fluid">
						<span class="span7 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('projecttaskname')}">
								{$RELATED_RECORD->getDisplayValue('projecttaskname')}
							</a>
						</span>
						<span class="span4 horizontalLeftSpacingForSummaryWidgetContents">
							<span class="pull-right">{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}</span>
						</span>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentTasks cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}