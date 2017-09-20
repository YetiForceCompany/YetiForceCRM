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
	<input type="hidden" name="relatedModule" value="Calendar" />
	{assign var=MODULE_NAME value="Calendar"}
	{if count($ACTIVITIES) neq '0'}
		{if $PAGE_NUMBER eq 1}
			<input type="hidden" class="totaltActivities" value="{$PAGING_MODEL->get('totalCount')}">
			<input type="hidden" class="pageLimit" value="{$PAGING_MODEL->getPageLimit()}">
		{/if}
		<input type="hidden" class="countActivities" value="{count($ACTIVITIES)}">
		<input type="hidden" class="currentPage" value="{$PAGE_NUMBER}">
		{foreach item=RECORD key=KEY from=$ACTIVITIES name=activities}
			{if $PAGE_NUMBER neq 1 && $smarty.foreach.activities.first}
				<hr>
			{/if}
			{assign var=START_DATE value=$RECORD->get('date_start')}
			{assign var=START_TIME value=$RECORD->get('time_start')}
			{assign var=END_DATE value=$RECORD->get('due_date')}
			{assign var=END_TIME value=$RECORD->get('time_end')}
			{assign var=STATUS value=$RECORD->get('status')}
			{assign var=SHAREDOWNER value=Vtiger_SharedOwner_UIType::getSharedOwners($RECORD->get('crmid'), $RECORD->getModuleName())}
			<div class="activityEntries padding5"
				 {if !empty($COLOR_LIST[$RECORD->getId()])}
					 style="background: {$COLOR_LIST[$RECORD->getId()]['background']}; color: {$COLOR_LIST[$RECORD->getId()]['text']}" 
				 {/if}>
				<input type="hidden" class="activityId" value="{$RECORD->get('activityid')}"/>
				<div class="row">
					<span class="col-md-6">
						<strong title='{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME")}'><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;{Vtiger_Util_Helper::formatDateIntoStrings($START_DATE, $START_TIME)}</strong>
					</span>
					<span class="col-md-6 rightText">
						<strong title='{Vtiger_Util_Helper::formatDateTimeIntoDayString("$END_DATE $END_TIME")}'><span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;{Vtiger_Util_Helper::formatDateIntoStrings($END_DATE, $END_TIME)}</strong>
					</span>
				</div>
				<div class="summaryViewEntries">
					{assign var=ACTIVITY_TYPE value=$RECORD->get('activitytype')}
					{assign var=ACTIVITY_UPPERCASE value=$ACTIVITY_TYPE|upper}
					<img src="{Vtiger_Theme::getOrignOrDefaultImgPath($ACTIVITY_TYPE, 'Calendar')}" width="14px" class="textOverflowEllipsis" alt="{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}"/>&nbsp;&nbsp;
					{\App\Language::translate($ACTIVITY_TYPE,$MODULE_NAME)}&nbsp;-&nbsp; 
					{if $RECORD->isViewable()}
						<a href="{$RECORD->getDetailViewUrl()}" >
							{$RECORD->get('subject')}</a>
						{else}
							{$RECORD->get('subject')}
						{/if}&nbsp;
					{if !$IS_READ_ONLY && $RECORD->isEditable()}
						<a href="{$RECORD->getEditViewUrl()}" class="fieldValue">
							<span class="glyphicon glyphicon-pencil summaryViewEdit" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
						</a>
					{/if}
					{if $RECORD->isViewable()}&nbsp;
						<a href="{$RECORD->getDetailViewUrl()}" class="fieldValue">
							<span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}" class="glyphicon glyphicon-th-list summaryViewEdit"></span>
						</a>
					{/if}
				</div>
				<div class="row">
					<div class="activityStatus col-md-12">
						{if $RECORD->get('activitytype') eq 'Task'}
							{assign var=MODULE_NAME value=$RECORD->getModuleName()}
							<input type="hidden" class="activityModule" value="{$RECORD->getModuleName()}"/>
							<input type="hidden" class="activityType" value="{$RECORD->get('activitytype')}"/>
							{if !$IS_READ_ONLY && $RECORD->isEditable()}
								<div>
									<strong>
										<span class="glyphicon glyphicon-tags"></span>&nbsp;&nbsp;<span class="value">{\App\Language::translate($RECORD->get('status'),$MODULE_NAME)}</span>
									</strong>&nbsp;&nbsp;
									{if $DATA_TYPE != 'history'}
										<span class="editDefaultStatus pull-right cursorPointer popoverTooltip delay0" data-url="{$RECORD->getActivityStateModalUrl()}" data-content="{\App\Language::translate('LBL_SET_RECORD_STATUS',$MODULE_NAME)}">
											<span class="glyphicon glyphicon-ok"></span>
										</span>
									{/if}
								</div>
							{/if}
						{else}
							{assign var=MODULE_NAME value="Events"}
							<input type="hidden" class="activityModule" value="Events"/>
							<input type="hidden" class="activityType" value="{$RECORD->get('activitytype')}"/>
							{if !$IS_READ_ONLY && $RECORD->isEditable()}
								<div>
									<strong><span class="glyphicon glyphicon-tags"></span>&nbsp;&nbsp;<span class="value">{\App\Language::translate($RECORD->get('status'),$MODULE_NAME)}</span></strong>&nbsp;&nbsp;
										{if $DATA_TYPE != 'history'}
										<span class="editDefaultStatus pull-right cursorPointer popoverTooltip delay0" data-url="{$RECORD->getActivityStateModalUrl()}" data-content="{\App\Language::translate('LBL_SET_RECORD_STATUS',$MODULE_NAME)}"><span class="glyphicon glyphicon-ok"></span></span>
										{/if}
								</div>
							{/if}
						{/if}
					</div>
				</div>
				<div class="activityDescription">					    
					<div>
						<span class="value"><span class="glyphicon glyphicon-align-justify"></span>&nbsp;&nbsp;
							{if $RECORD->get('description') neq ''}
								{\App\Language::translate($RECORD->get('description'),$MODULE_NAME)|truncate:120:'...'}
							{else}
								<span class="muted">{\App\Language::translate('LBL_NO_DESCRIPTION',$MODULE_NAME)}</span>
							{/if}
						</span>&nbsp;&nbsp;
						{if !$IS_READ_ONLY}
							<span class="editDescription cursorPointer">
								<span class="glyphicon glyphicon-pencil" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
							</span>
						{/if}
						<span class="pull-right popoverTooltip delay0" data-placement="top" data-original-title="{\App\Language::translate($RECORD->get('activitytype'),$MODULE_NAME)}: {$RECORD->get('subject')}" 
							  data-content="{\App\Language::translate('Status',$MODULE_NAME)}: {\App\Language::translate($STATUS,$MODULE_NAME)}<br />{\App\Language::translate('Start Time','Calendar')}: {$START_DATE} {$START_TIME}<br />{\App\Language::translate('End Time','Calendar')}: {$END_DATE} {$END_TIME}<hr />{\App\Language::translate('Created By',$MODULE_NAME)}: {\App\Fields\Owner::getLabel( $RECORD->get('smcreatorid') )}<br />{\App\Language::translate('Assigned To',$MODULE_NAME)}: {\App\Fields\Owner::getLabel( $RECORD->get('smownerid') )}
							  {if $SHAREDOWNER}<div> 
								  {\App\Language::translate('Share with users',$MODULE_NAME)}:&nbsp;
								  {foreach $SHAREDOWNER item=SOWNERID name=sowner}
									  {if $smarty.foreach.sowner.last}
										  ,&nbsp;
									  {/if}
									  {\App\Fields\Owner::getUserLabel($SOWNERID)}
								  {/foreach}
								  </div>
							  {/if}
							  {if $MODULE_NAME eq 'Events'}
								  {if count($RECORD->get('selectedusers')) > 0}
									  <br />{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}: 
									  {foreach item=USER key=KEY from=$RECORD->get('selectedusers')}
									  {if $USER}{\App\Fields\Owner::getLabel( $USER )}{/if}
								  {/foreach}
							  {/if}
						{/if}
						">
						<span class="glyphicon glyphicon-info-sign"></span>
					</span>
					{if !$IS_READ_ONLY && $RECORD->isEditable()}
						<span class="2 edit hide row">
							{assign var=FIELD_MODEL value=$RECORD->getModule()->getField('description')}
							{assign var=FIELD_VALUE value=$FIELD_MODEL->set('fieldvalue', $RECORD->get('description'))}
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
							{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
								<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
							{else}
								<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
							{/if}
						</span>
					{/if}
					{if $RECORD->get('location') neq '' }
						<a target="_blank" rel="noreferrer" href="https://www.google.com/maps/search/{urlencode ($RECORD->get('location'))}" class="pull-right popoverTooltip delay0" data-original-title="{\App\Language::translate('Location', 'Calendar')}" data-content="{$RECORD->get('location')}">
							<span class="icon-map-marker"></span>&nbsp
						</a>
					{/if}
				</div>
			</div>
		</div>
		{if !$smarty.foreach.activities.last}
			<hr>
		{/if}
	{/foreach}
{else}
	<div class="summaryWidgetContainer">
		<p class="textAlignCenter">{\App\Language::translate('LBL_NO_PENDING_ACTIVITIES',$MODULE_NAME)}</p>
	</div>
{/if}
{if $PAGING_MODEL->isNextPageExists()}
	<div class="row">
		<div class="pull-right">
			<button type="button" class="btn btn-primary btn-xs moreRecentActivities marginTop10 marginRight10">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</button>
		</div>
	</div>
{/if}
{/strip}
