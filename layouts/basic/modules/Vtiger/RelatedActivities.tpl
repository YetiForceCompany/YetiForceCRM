{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="small">
		<input type="hidden" name="relatedModule" value="Calendar" />
		{assign var=MODULE_NAME value="Calendar"}
		{if count($ACTIVITIES) neq '0'}
			{if $PAGE_NUMBER eq 1}
				<input type="hidden" class="totaltActivities" value="{$PAGING_MODEL->get('totalCount')}" />
				<input type="hidden" class="pageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
			{/if}
			<input type="hidden" class="countActivities" value="{count($ACTIVITIES)}" />
			<input type="hidden" class="currentPage" value="{$PAGE_NUMBER}" />
			{assign var=SHOW_LINK_TO_CALENDAR value=App\Config::module($MODULE_NAME, 'SHOW_EDIT_FORM')}
			{assign var=CURRENT_STATUSES value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
			{foreach item=RECORD key=KEY from=$ACTIVITIES name=activities}
				{if $PAGE_NUMBER neq 1 && $smarty.foreach.activities.first}
					<hr>
				{/if}
				{assign var=START_DATE value=$RECORD->get('date_start')}
				{assign var=START_TIME value=$RECORD->get('time_start')}
				{assign var=END_DATE value=$RECORD->get('due_date')}
				{assign var=END_TIME value=$RECORD->get('time_end')}
				{assign var=SHAREDOWNER value=\App\Fields\SharedOwner::getById($RECORD->getId())}
				<div class="activityEntries p-1">
					<input type="hidden" class="activityModule" value="{$MODULE_NAME}" />
					<input type="hidden" class="activityId" value="{$RECORD->getId()}" />
					<div class="row">
						<span class="col-md-6">
							<strong title='{\App\Fields\DateTime::formatToDay("$START_DATE $START_TIME")}'><span
									class="far fa-clock fa-fw mr-1"></span>{Vtiger_Util_Helper::formatDateIntoStrings($START_DATE, $START_TIME)}</strong>
						</span>
						<span class="col-md-6 rightText">
							<strong title='{\App\Fields\DateTime::formatToDay("$END_DATE $END_TIME")}'><span
									class="far fa-clock fa-fw mr-1"></span>{Vtiger_Util_Helper::formatDateIntoStrings($END_DATE, $END_TIME)}</strong>
						</span>
					</div>
					<div class="summaryViewEntries">
						<span class="mr-1">
							{assign var=ACTIVITY_TYPE value=$RECORD->get('activitytype')}
							{if $ACTIVITY_TYPE eq 'Task'}
								<span class="far fa-check-square fa-fw"></span>
							{elseif $ACTIVITY_TYPE eq 'Call'}
								<span class="fas fa-phone fa-flip-horizontal fa-fw"></span>
							{else}
								<span class="fas fa-user fa-fw"></span>
							{/if}
						</span>
						{$RECORD->getDisplayValue('activitytype')}&nbsp;-&nbsp;
						{if $RECORD->isViewable()}
							<a href="{$RECORD->getDetailViewUrl()}">
								{$RECORD->getDisplayValue('subject')}</a>
						{else}
							{$RECORD->getDisplayValue('subject')}
						{/if}&nbsp;
						{if !$IS_READ_ONLY && $RECORD->isEditable()}
							<a href="{$RECORD->getEditViewUrl()}" class="fieldValue">
								<span class="yfi yfi-full-editing-view fa-fw js-detail-quick-edit"
									title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
							</a>
						{/if}
						{if $RECORD->isViewable()}&nbsp;
							<a href="{$RECORD->getDetailViewUrl()}" class="fieldValue">
								<span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}"
									class="fas fa-th-list fa-fw js-detail-quick-edit"></span>
							</a>
						{/if}
					</div>
					<div class="row">
						<div class="activityStatus col-md-12">
							<input value="{\App\Purifier::encodeHtml($RECORD->get('activitytype'))}" type="hidden" class="activityType" />
							{if !$IS_READ_ONLY && $RECORD->isEditable()}
								<div>
									<strong>
										<span class="fas fa-tags fa-fw mr-1"></span>
										<span class="value">{$RECORD->getDisplayValue('activitystatus')}</span>
									</strong>&nbsp;&nbsp;
									{if in_array($RECORD->get('activitystatus'), $CURRENT_STATUSES)}
										{if $SHOW_LINK_TO_CALENDAR}
											{assign var=ACTIVITY_URL value="index.php?module=Calendar&view=QuickEditAjax&record={$RECORD->getId()}"}
											<span class="editDefaultStatus u-cursor-pointer float-right js-popover-tooltip showEdit" data-url="{$ACTIVITY_URL}"
												data-content="{\App\Language::translate('LBL_EDIT_FROM_CALENDAR',$MODULE_NAME)}" data-js="popover">
												<span class="far fa-calendar fa-fw"></span>
											</span>
										{else}
											<span class="editDefaultStatus float-right u-cursor-pointer js-popover-tooltip delay0"
												data-js="popover" data-url="{$RECORD->getActivityStateModalUrl()}"
												data-content="{\App\Language::translate('LBL_SET_RECORD_STATUS',$MODULE_NAME)}">
												<span class="fas fa-check fa-fw"></span>
											</span>
										{/if}
									{/if}
								</div>
							{/if}
						</div>
					</div>
					<div class="activityDescription">
						<div>
							<span class="value mr-1"><span class="fas fa-align-justify fa-fw mr-1"></span>
								{assign var=IS_DESCRIPTION value=$RECORD->get('description') neq ''}
								<span class="js-description-text" data-js="html">
									{if $IS_DESCRIPTION}
										{$RECORD->getDisplayValue('description', false, false, 120)}
									{/if}
								</span>
								<span class="js-no-description text-muted{if $IS_DESCRIPTION} d-none{/if}" data-js="class: d-none">
									{\App\Language::translate('LBL_NO_DESCRIPTION',$MODULE_NAME)}
								</span>
							</span>
							{if !$IS_READ_ONLY}
								<span class="js-activity-buttons__container d-none" data-js="class: d-none">
									<button class="btn btn-sm btn-success js-save-description u-fs-95per my-1 mr-1 py-0 px-1" type="button" data-js="click">
										<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SAVE',$MODULE_NAME)}
									</button>
									<button class="btn btn-sm btn-danger js-close-description u-fs-95per my-1 py-0 px-1" type="button" data-js="click">
										<span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CLOSE',$MODULE_NAME)}
									</button>
								</span>
								<span class="editDescription u-cursor-pointer">
									<span class="yfi yfi-full-editing-view fa-fw"
										title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
								</span>
							{/if}
							{if $RECORD->get('location')}
								<a target="_blank" rel="noreferrer noopener"
									href="https://www.google.com/maps/search/{urlencode ($RECORD->getDisplayValue('location'))}"
									class="float-right js-popover-tooltip delay0" data-js="popover"
									data-original-title="{\App\Language::translate('Location', 'Calendar')}"
									data-content="{$RECORD->getDisplayValue('location')}">
									<span class="fas fa-map-marker-alt fa-fw"></span>&nbsp
								</a>
							{/if}
							<span class="float-right js-popover-tooltip delay0" data-js="popover" data-placement="left"
								data-class="activities"
								data-original-title="{\App\Purifier::encodeHtml($RECORD->getDisplayValue('activitytype',false, true,true))}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('subject',false,false,40))}"
								data-content="{\App\Language::translate('Status',$MODULE_NAME)}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('activitystatus',false, true,40))}<br />{\App\Language::translate('Start Time','Calendar')}: {$START_DATE} {$START_TIME}<br />{\App\Language::translate('End Time','Calendar')}: {$END_DATE} {$END_TIME}
								  {if $RECORD->get('linkextend')}<hr />{App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('linkextend')))}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('linkextend',false,false,40))}{/if}
								  {if $RECORD->get('link')}<br />{App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('link')))}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('link',false,false,40))}{/if}
								  {if $RECORD->get('process')}<br />{App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('process')))}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('process',false,false,40))}{/if}
								  {if $RECORD->get('subprocess')}<br />{App\Language::translateSingularModuleName(\App\Record::getType($RECORD->get('subprocess')))}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('subprocess',false,false,40))}{/if}
								  <hr />{\App\Language::translate('Created By',$MODULE_NAME)}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('created_user_id',false,false,40))}
								  <br />{\App\Language::translate('Assigned To',$MODULE_NAME)}: {\App\Purifier::encodeHtml($RECORD->getDisplayValue('assigned_user_id',false,false,40))}
								  {if $SHAREDOWNER}<div>
									  {\App\Language::translate('Share with users',$MODULE_NAME)}:&nbsp;
									  {foreach $SHAREDOWNER item=SOWNERID name=sowner}
										  {if $smarty.foreach.sowner.last}
											  ,&nbsp;
										  {/if}
										  {\App\Purifier::encodeHtml(\App\Fields\Owner::getUserLabel($SOWNERID))}
									  {/foreach}
									  </div>
								  {/if}
								  {if count($RECORD->get('selectedusers')) > 0}
									  <br />{\App\Language::translate('LBL_INVITE_RECORDS',$MODULE_NAME)}:
									  {foreach item=USER key=KEY from=$RECORD->get('selectedusers')}
									 	 {if $USER} {\App\Purifier::encodeHtml(\App\Fields\Owner::getLabel($USER))}{/if}
									  {/foreach}
								  {/if}
							">
								<span class="fas fa-info-circle fa-fw"></span>
							</span>
							{if !$IS_READ_ONLY && $RECORD->isEditable()}
								<span class="edit d-none">
									{assign var=FIELD_MODEL value=$RECORD->getField('description')->set('fieldvalue', $RECORD->get('description'))}
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
									{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
									<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}' data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}' />
								</span>
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
			<div class="d-flex py-1">
				<div class="ml-auto">
					<button type="button"
						class="btn btn-primary btn-sm moreRecentActivities mt-2">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}
						..
					</button>
				</div>
			</div>
		{/if}
	</div>
{/strip}
