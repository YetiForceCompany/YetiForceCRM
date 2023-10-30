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
	<div class="tpl-Settings-Workflow-Tasks-VTCreateTodoTask row">
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_TITLE',$QUALIFIED_MODULE)}<span
					class="redColor">*</span></span>
			<div class="col-md-9">
				<input data-validation-engine='validate[required]' class="form-control" name="todo" type="text"
					value="{if isset($TASK_OBJECT->todo)}{$TASK_OBJECT->todo}{/if}" />
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-9">
				<textarea class="form-control" name="description">{if isset($TASK_OBJECT->description)}{$TASK_OBJECT->description}{/if}</textarea>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_STATUS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-9">
				{assign var=STATUS_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitystatus')->getPickListValues()}
				<select name="status" class="select2 form-control" data-select="allowClear">
					<optgroup class="p-0">
						<option value=""> - {\App\Language::translate('LBL_AUTOMATIC')} - </option>
					</optgroup>
					{foreach  from=$STATUS_PICKLIST_VALUES item=STATUS_PICKLIST_VALUE key=STATUS_PICKLIST_KEY}
						<option value="{$STATUS_PICKLIST_KEY}" {if isset($TASK_OBJECT->status) && $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_PRIORITY',$QUALIFIED_MODULE)}</span>
			<span class="col-md-9">
				{assign var=PRIORITY_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('taskpriority')->getPickListValues()}
				<select name="priority" class="select2 form-control">
					{foreach  from=$PRIORITY_PICKLIST_VALUES item=PRIORITY_PICKLIST_VALUE key=PRIORITY_PICKLIST_KEY}
						<option value="{$PRIORITY_PICKLIST_KEY}" {if isset($TASK_OBJECT->priority) && $PRIORITY_PICKLIST_KEY eq $TASK_OBJECT->priority} selected="" {/if}>{$PRIORITY_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</span>
			<span class="col-md-9">
				{assign var=ASSIGNED_USER_ID value=0}
				{if isset($TASK_OBJECT->assigned_user_id)}
					{assign var=ASSIGNED_USER_ID value=$TASK_OBJECT->assigned_user_id}
				{/if}
				<select name="assigned_user_id" class="select2 form-control" data-select="allowClear">
					<optgroup class="p-0">
						<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
					</optgroup>
					{foreach from=$ASSIGNED_TO key=LABEL item=ASSIGNED_USERS_LIST}
						<optgroup label="{\App\Language::translate($LABEL,$QUALIFIED_MODULE)}">
							{foreach from=$ASSIGNED_USERS_LIST item=ASSIGNED_USER key=ASSIGNED_USER_KEY}
								<option value="{$ASSIGNED_USER_KEY}" {if $ASSIGNED_USER_KEY eq $ASSIGNED_USER_ID} selected="" {/if}>{$ASSIGNED_USER}</option>
							{/foreach}
						</optgroup>
					{/foreach}
					<optgroup label="{\App\Language::translate('LBL_SPECIAL_OPTIONS')}">
						<option value="copyParentOwner" {if $ASSIGNED_USER_ID eq 'copyParentOwner'} selected="" {/if}>{\App\Language::translate('LBL_PARENT_OWNER')}</option>
						<option value="currentUser" {if $ASSIGNED_USER_ID eq 'currentUser'} selected="" {/if}>{\App\Language::translate('LBL_CURRENT_USER',$QUALIFIED_MODULE)}</option>
						{if $WORKFLOW_MODEL->get('execution_condition') === \VTWorkflowManager::$TRIGGER}
							<option value="triggerUser" {if $ASSIGNED_USER_ID eq 'triggerUser'} selected="" {/if}>{\App\Language::translate('LBL_TRIGGER_USER',$QUALIFIED_MODULE)}</option>
						{/if}
					</optgroup>
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-9">
				<div class="input-group time">
					{if !empty($TASK_OBJECT->time)}
						{assign var=TIME value=$TASK_OBJECT->time}
					{else}
						{assign var=TIME value=App\Fields\Time::formatToDisplay('')}
					{/if}
					<input type="text" class="clockPicker form-control" value="{$TIME}" name="time" autocomplete="off" />
					<div class="input-group-append">
						<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
							<span class="far fa-clock"></span>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<div class="col-md-3 mb-1 mb-md-0">{\App\Language::translate('LBL_DAYS_START',$QUALIFIED_MODULE)}</div>
			<div class="col-md-2 mb-1 mb-md-0 pr-md-1">
				<input class="form-control" type="text" name="days_start" value="{if isset($TASK_OBJECT->days_start)}{$TASK_OBJECT->days_start}{/if}">
			</div>
			<div class="col-md-4 row no-gutters mb-1 mb-md-0 pr-md-1">
				<div class="col-2 pt-1">{\App\Language::translate('LBL_DAYS',$QUALIFIED_MODULE)}</div>
				<div class="col-10">
					<select class="select2 form-control" name="direction_start">
						<option {if isset($TASK_OBJECT->direction_start) && $TASK_OBJECT->direction_start eq 'after'}selected="" {/if}
							value="after">{\App\Language::translate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
						<option {if isset($TASK_OBJECT->direction_start) && $TASK_OBJECT->direction_start eq 'before'}selected="" {/if}
							value="before">{\App\Language::translate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="col-md-3">
				{assign var=DATE_FIELD_START value=''}
				{if isset($TASK_OBJECT->datefield_start)}
					{assign var=DATE_FIELD_START value=$TASK_OBJECT->datefield_start}
				{/if}
				<select class="select2 form-control" name="datefield_start">
					<optgroup label='{\App\Language::translate('LBL_VALUE_OF_FIELDS', $QUALIFIED_MODULE)}'>
						{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
							<option {if $DATE_FIELD_START eq $DATETIME_FIELD->get('name')}selected="selected" {/if}
								value="{$DATETIME_FIELD->get('name')}">{\App\Language::translate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
						{/foreach}
					</optgroup>
					<optgroup label='{\App\Language::translate('LBL_VALUE_OF_SERVER', $QUALIFIED_MODULE)}'>
						<option {if $DATE_FIELD_START eq 'wfRunTime'}selected="selected" {/if}
							value="wfRunTime">{\App\Language::translate('LBL_WORKFLOWS_RUN_TIME',$QUALIFIED_MODULE)}</option>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<div class="col-md-3 mb-1 mb-md-0">{\App\Language::translate('LBL_DAYS_END',$QUALIFIED_MODULE)}</div>
			<div class="col-md-2 mb-1 mb-md-0 pr-md-1">
				<input class="form-control" type="text" name="days_end" value="{if isset($TASK_OBJECT->days_end)}{$TASK_OBJECT->days_end}{/if}">
			</div>
			<div class="col-md-4 mb-1 mb-md-0 row no-gutters pr-md-1">
				<div class="col-2 pt-1">{\App\Language::translate('LBL_DAYS',$QUALIFIED_MODULE)}</div>
				<div class="col-10">
					<select class="select2 form-control" name="direction_end">
						<option {if isset($TASK_OBJECT->direction_end) && $TASK_OBJECT->direction_end eq 'after'}selected="" {/if}
							value="after">{\App\Language::translate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
						<option {if isset($TASK_OBJECT->direction_end) && $TASK_OBJECT->direction_end eq 'before'}selected="" {/if}
							value="before">{\App\Language::translate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="col-md-3">
				{assign var=DATE_FIELD_END value=''}
				{if isset($TASK_OBJECT->datefield_end)}
					{assign var=DATE_FIELD_END value=$TASK_OBJECT->datefield_end}
				{/if}
				<select class="select2 form-control" name="datefield_end">
					<optgroup label='{\App\Language::translate('LBL_VALUE_OF_FIELDS', $QUALIFIED_MODULE)}'>
						{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
							<option {if $DATE_FIELD_END eq $DATETIME_FIELD->get('name')}selected="selected" {/if}
								value="{$DATETIME_FIELD->get('name')}">{\App\Language::translate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
						{/foreach}
					</optgroup>
					<optgroup label='{\App\Language::translate('LBL_SPECIAL_OPTIONS', $QUALIFIED_MODULE)}'>
						<option {if $DATE_FIELD_END eq 'fromDateStart'}selected="selected" {/if}
							value="fromDateStart">{\App\Language::translate('LBL_TASK_START_DATES_AND_TIMES', $QUALIFIED_MODULE)}</option>
					</optgroup>
					<optgroup label='{\App\Language::translate('LBL_VALUE_OF_SERVER', $QUALIFIED_MODULE)}'>
						<option {if $DATE_FIELD_END eq 'wfRunTime'}selected="selected" {/if}
							value="wfRunTime">{\App\Language::translate('LBL_WORKFLOWS_RUN_TIME',$QUALIFIED_MODULE)}</option>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-8">{\App\Language::translate('LBL_SEND_NOTIFICATION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<input type="checkbox" name="sendNotification" value="true"
					{if !empty($TASK_OBJECT->sendNotification)}checked{/if} />
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-8">{\App\Language::translate('LBL_DO_NOT_DUPLICATE_RECORDS',$QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<input type="checkbox" name="doNotDuplicate" value="true"
					{if !empty($TASK_OBJECT->doNotDuplicate)}checked{/if} />
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-5">{\App\Language::translate('LBL_DUPLICATE_STATUS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-7">
				<select multiple name="duplicateStatus" class="select2 form-control">
					{foreach from=App\Fields\Picklist::getValuesName('activitystatus') key=KEY item=ITEM}
						<option value="{$ITEM}" {if isset($TASK_OBJECT->duplicateStatus) && in_array($ITEM,vtlib\Functions::getArrayFromValue($TASK_OBJECT->duplicateStatus))} selected="" {/if}>{\App\Language::translate($ITEM,'Calendar')}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-8">{\App\Language::translate('LBL_UPDATE_DATES_BASE_DATE_CHANGES',$QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<input type="checkbox" name="updateDates" value="true" {if !empty($TASK_OBJECT->updateDates)}checked{/if} />
			</div>
		</div>
	</div>
{/strip}
