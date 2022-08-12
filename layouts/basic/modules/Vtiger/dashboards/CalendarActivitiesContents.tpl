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
	<style type="text/css">
		small.small-a {
			font-size: 75%;
		}
	</style>
	<div>
		{foreach from=$ACTIVITIES key=INDEX item=ACTIVITY}
			<div class="changeActivity u-cursor-pointer" data-url="{$ACTIVITY->getActivityStateModalUrl()}" accesskey="">
				<div class="rowActivities">
					<div class="d-flex mb-1">
						<div class="">
							{assign var=ACTIVITY_TYPE value=$ACTIVITY->get('activitytype')}
							{assign var=ACTIVITY_UPPERCASE value=$ACTIVITY_TYPE|upper}
							<i class="
							   {if $ACTIVITY_TYPE eq 'Task'}
								   far fa-check-square
							   {elseif $ACTIVITY_TYPE eq 'Call'}
								   fas fa-phone
							   {else}
								   fas fa-user
							   {/if}
							   {' '}fa-lg fa-fw"></i>
						</div>
						{if $DATE_TYPE === 'DUE'}
							{assign var=ACTIVITY_DATE value=$ACTIVITY->get('due_date')}
							{assign var=ACTIVITY_TIME value=$ACTIVITY->get('time_end')}
						{else}
							{assign var=ACTIVITY_DATE value=$ACTIVITY->get('date_start')}
							{assign var=ACTIVITY_TIME value=$ACTIVITY->get('time_start')}
						{/if}
						{assign var=LINK value=$ACTIVITY->get('link')}
						{assign var=PROCESS value=$ACTIVITY->get('process')}
						{assign var=SUBPROCESS value=$ACTIVITY->get('subprocess')}
						{assign var=CONTRACTOR value=$ACTIVITY->get('contractor')}
						<div class="w-100 mx-1">
							{\App\TextUtils::textTruncate($ACTIVITY->getDisplayName(), $NAMELENGTH)}
							{if $CONTRACTOR}
								<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('contractor')}</strong></small>, <strong><small class='small-a'><a href="{$CONTRACTOR->getDetailViewUrl()}">{\App\TextUtils::textTruncate($CONTRACTOR->getDisplayName(), $HREFNAMELENGTH)}</a></small></strong>
							{/if}
							{if $LINK}
								<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('link')}</strong></small>
							{/if}
							{if $PROCESS}
								<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('process')}</strong></small>
							{/if}
							{if $SUBPROCESS}
								<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('subprocess')}</strong></small>
							{/if}
						</div>
						<div>
							{$ACTIVITY->getDisplayValue('taskpriority')}
						</div>
						{if $ACTIVITY->get('location') neq '' }
							<div class="ml-1">
								<a target="_blank" rel="noreferrer noopener" href="https://www.google.com/maps/search/{urlencode ($ACTIVITY->getDisplayValue('location'))}" class="float-right" title="{\App\Language::translate('Location', 'Calendar')}: {$ACTIVITY->getDisplayValue('location')}">
									<span class="fas fa-globe"></span>
								</a>
							</div>
						{/if}
						<div class="ml-1">
							<small>
								{\App\Fields\DateTime::formatToViewDate("$ACTIVITY_DATE $ACTIVITY_TIME")}
							</small>
						</div>
					</div>
				</div>
			</div>
		{foreachelse}
			<span class="noDataMsg">
				{\App\Language::translate($NODATAMSGLABLE, $MODULE_NAME)}
			</span>
		{/foreach}
		{if $PAGING_MODEL->get('nextPageExists') eq 'true'}
			<div class="float-right padding5">
				<button type="button" class="btn btn-sm btn-primary showMoreHistory" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">
					{\App\Language::translate('LBL_MORE')}
				</button>
			</div>
		{/if}
	</div>
{/strip}
