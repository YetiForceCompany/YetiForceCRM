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
			<span class="col-md-2">{vtranslate('LBL_TITLE',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
			<div class="col-md-8">
				<input data-validation-engine='validate[required]' class="form-control" name="todo" type="text" value="{$TASK_OBJECT->todo}" />
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-8">
				<textarea class="form-control" name="description">{$TASK_OBJECT->description}</textarea>
			</div>
		</div>
		{*<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</span>
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
			<span class="col-md-2">{vtranslate('LBL_PRIORITY',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				{assign var=PRIORITY_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('taskpriority')->getPickListValues()}
				<select name="priority" class="chzn-select form-control">
					{foreach  from=$PRIORITY_PICKLIST_VALUES item=PRIORITY_PICKLIST_VALUE key=PRIORITY_PICKLIST_KEY}
						<option value="{$PRIORITY_PICKLIST_KEY}" {if $PRIORITY_PICKLIST_KEY eq $TASK_OBJECT->priority} selected="" {/if}>{$PRIORITY_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</span>
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
			<span class="col-md-2">{vtranslate('LBL_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<div class="input-group time input-group-sm">
					{if $TASK_OBJECT->time neq ''}
						{assign var=TIME value=$TASK_OBJECT->time}
					{else}
						{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
						{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
						{assign var=TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
					{/if}
					<input  type="text" class="clockPicker form-control" value="{$TIME}" name="time" />
					<span class="input-group-addon cursorPointer">
						<i class="glyphicon glyphicon-time"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-xs-2">{vtranslate('LBL_DAYS_START',$QUALIFIED_MODULE)}</span>
			<div class="col-xs-2">
				<input class="form-control" type="text" name="days_start" value="{$TASK_OBJECT->days_start}">&nbsp;
			</div>
			<span class="control-label pull-left alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			<div class="col-xs-3 marginLeftZero">
				<select class="chzn-select form-control" name="direction_start">
					<option {if $TASK_OBJECT->direction_start eq 'after'}selected=""{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->direction_start eq 'before'}selected=""{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</div>
			<div class="col-xs-4">
				<select class="chzn-select form-control" name="datefield_start">
					<optgroup label='{vtranslate('LBL_VALUE_OF_FIELDS', $QUALIFIED_MODULE)}'>
						{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
							<option {if $TASK_OBJECT->datefield_start eq $DATETIME_FIELD->get('name')}selected{/if} value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
						{/foreach}
					</optgroup>
					<optgroup label='{vtranslate('LBL_VALUE_OF_SERVER', $QUALIFIED_MODULE)}'>
						<option {if $TASK_OBJECT->datefield_start eq 'wfRunTime'}selected{/if} value="wfRunTime">{vtranslate('LBL_WORKFLOWS_RUN_TIME',$QUALIFIED_MODULE)}</option>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-xs-2">{vtranslate('LBL_DAYS_END',$QUALIFIED_MODULE)}</span>
			<span class="col-xs-2">
				<input class="form-control" type="text" name="days_end" value="{$TASK_OBJECT->days_end}">&nbsp;
			</span>
			<span class="control-label pull-left alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			<span class="col-xs-3 marginLeftZero">
				<select class="chzn-select" name="direction_end" style="width: 100px">
					<option {if $TASK_OBJECT->direction_end eq 'after'}selected=""{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->direction_end eq 'before'}selected=""{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="col-xs-4">
				<select class="chzn-select form-control" name="datefield_end">
					<optgroup label='{vtranslate('LBL_VALUE_OF_FIELDS', $QUALIFIED_MODULE)}'>
						{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
							<option {if $TASK_OBJECT->datefield_end eq $DATETIME_FIELD->get('name')}selected{/if} value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
						{/foreach}
					</optgroup>
					<optgroup label='{vtranslate('LBL_VALUE_OF_SERVER', $QUALIFIED_MODULE)}'>
						<option {if $TASK_OBJECT->datefield_end eq 'wfRunTime'}selected{/if} value="wfRunTime">{vtranslate('LBL_WORKFLOWS_RUN_TIME',$QUALIFIED_MODULE)}</option>
					</optgroup>
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_SEND_NOTIFICATION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-6">
				<input type="checkbox" name="sendNotification" value="true" {if $TASK_OBJECT->sendNotification}checked{/if} />
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_DO_NOT_DUPLICATE_RECORDS',$QUALIFIED_MODULE)}</span>
			<div class="col-md-6">
				<input type="checkbox" name="doNotDuplicate" value="true" {if $TASK_OBJECT->doNotDuplicate}checked{/if} />
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_DUPLICATE_STATUS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				<select multiple name="duplicateStatus" class="chzn-select form-control">
					<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
					{foreach from=App\Fields\Picklist::getPickListValues('activitystatus') key=KEY item=ITEM}
						<option value="{$ITEM}" {if in_array($ITEM,vtlib\Functions::getArrayFromValue($TASK_OBJECT->duplicateStatus))} selected="" {/if}>{vtranslate($ITEM,'Calendar')}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2">{vtranslate('LBL_UPDATE_DATES_BASE_DATE_CHANGES',$QUALIFIED_MODULE)}</span>
			<div class="col-md-6">
				<input type="checkbox" name="updateDates" value="true" {if $TASK_OBJECT->updateDates}checked{/if} />
			</div>
		</div>
	</div>
{/strip}
