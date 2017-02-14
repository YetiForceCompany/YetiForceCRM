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
	<div class="row">
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_EVENT_NAME',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
			<div class="col-md-9">
				<input data-validation-engine='validate[required]' class="form-control" name="eventName" type="text" value="{$TASK_OBJECT->eventName}" />
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-9">
				<textarea class="form-control" name="description">{$TASK_OBJECT->description}</textarea>
			</div>
		</div>
		{*<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				{assign var=STATUS_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitystatus')->getPickListValues()}
				<select name="status" class="chzn-select form-control">
					{foreach  from=$STATUS_PICKLIST_VALUES item=STATUS_PICKLIST_VALUE key=STATUS_PICKLIST_KEY}
						<option value="{$STATUS_PICKLIST_KEY}" {if $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>*}
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_TYPE',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				{assign var=EVENTTYPE_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitytype')->getPickListValues()}
				<select name="eventType" class="chzn-select form-control">
					{foreach  from=$EVENTTYPE_PICKLIST_VALUES item=EVENTTYPE_PICKLIST_VALUE key=EVENTTYPE_PICKLIST_KEY}
						<option value="{$EVENTTYPE_PICKLIST_KEY}" {if $EVENTTYPE_PICKLIST_KEY eq $TASK_OBJECT->eventType} selected="" {/if}>{$EVENTTYPE_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				<select name="assigned_user_id" class="chzn-select form-control">
					<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
					{foreach from=$ASSIGNED_TO key=LABEL item=ASSIGNED_USERS_LIST}
						<optgroup label="{vtranslate($LABEL,$QUALIFIED_MODULE)}">
							{foreach from=$ASSIGNED_USERS_LIST item=ASSIGNED_USER key=ASSIGNED_USER_KEY}
								<option value="{$ASSIGNED_USER_KEY}" {if $ASSIGNED_USER_KEY eq $TASK_OBJECT->assigned_user_id} selected="" {/if}>{$ASSIGNED_USER}</option>
							{/foreach}
						</optgroup>
					{/foreach}
                    <optgroup label="{vtranslate('LBL_SPECIAL_OPTIONS')}">
						<option value="copyParentOwner" {if $TASK_OBJECT->assigned_user_id eq 'copyParentOwner'} selected="" {/if}>{vtranslate('LBL_PARENT_OWNER')}</option>
						<option value="currentUser" {if $TASK_OBJECT->assigned_user_id eq 'currentUser'} selected="" {/if}>{vtranslate('LBL_CURRENT_USER',$QUALIFIED_MODULE)}</option>
						<option value="triggerUser" {if $TASK_OBJECT->assigned_user_id eq 'triggerUser'} selected="" {/if}>{vtranslate('LBL_TRIGGER_USER',$QUALIFIED_MODULE)}</option>
                    </optgroup>
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			{if $TASK_OBJECT->startTime neq ''}
				{assign var=START_TIME value=$TASK_OBJECT->startTime}
			{else}
				{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=START_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="col-md-2 control-label">{vtranslate('LBL_START_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<div class="input-group time input-group-sm">
					<input  type="text" class="clockPicker form-control" data-format="{$timeFormat}" value="{$START_TIME}" name="startTime" />
					<span class="input-group-addon cursorPointer">
						<i class="glyphicon glyphicon-time"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_START_DATE',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<input class="form-control" type="text" value="{$TASK_OBJECT->startDays}" name="startDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
			</div>
			<span class="control-label pull-left alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			<span class="span marginLeftZero col-md-3">
				<select class="chzn-select form-control" name="startDirection">
					<option  {if $TASK_OBJECT->startDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->startDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="col-md-4">
				<select class="chzn-select form-control" name="startDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->startDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			{if $TASK_OBJECT->endTime neq ''}
				{assign var=END_TIME value=$TASK_OBJECT->endTime}
			{else}
				{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=END_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="col-md-2 control-label">{vtranslate('LBL_END_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<div class="input-group time input-group-sm">
					<input  type="text" class="clockPicker form-control" value="{$END_TIME}" name="endTime" />
					<span class="input-group-addon cursorPointer">
						<i class="glyphicon glyphicon-time"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_END_DATE',$QUALIFIED_MODULE)}</span>
			<span class="col-md-2">
				<input class="form-control" type="text" value="{$TASK_OBJECT->endDays}" name="endDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
			</span>
			<span class="control-label pull-left alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-3 marginLeftZero">
				<select class="chzn-select form-control" name="endDirection">
					<option  {if $TASK_OBJECT->endDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->endDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="col-md-4">
				<select class="chzn-select form-control" name="endDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->endDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
	</div>
{/strip}
