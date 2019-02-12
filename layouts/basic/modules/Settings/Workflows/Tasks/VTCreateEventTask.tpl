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
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_EVENT_NAME',$QUALIFIED_MODULE)}<span
						class="redColor">*</span></span>
			<div class="col-md-9">
				<input data-validation-engine='validate[required]' class="form-control" name="eventName" type="text"
					   value="{$TASK_OBJECT->eventName}"/>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-9">
				<textarea class="form-control" name="description">{$TASK_OBJECT->description}</textarea>
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
						<option value="{$STATUS_PICKLIST_KEY}" {if $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_TYPE',$QUALIFIED_MODULE)}</span>
			<span class="col-md-9">
				{assign var=EVENTTYPE_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitytype')->getPickListValues()}
				<select name="eventType" class="select2 form-control">
					{foreach  from=$EVENTTYPE_PICKLIST_VALUES item=EVENTTYPE_PICKLIST_VALUE key=EVENTTYPE_PICKLIST_KEY}
						<option value="{$EVENTTYPE_PICKLIST_KEY}" {if $EVENTTYPE_PICKLIST_KEY eq $TASK_OBJECT->eventType} selected="" {/if}>{$EVENTTYPE_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<span class="col-md-3">{\App\Language::translate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</span>
			<span class="col-md-9">
				<select name="assigned_user_id" class="select2 form-control" data-select="allowClear">
					<optgroup class="p-0">
						<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
					</optgroup>
					{foreach from=$ASSIGNED_TO key=LABEL item=ASSIGNED_USERS_LIST}
						<optgroup label="{\App\Language::translate($LABEL,$QUALIFIED_MODULE)}">
							{foreach from=$ASSIGNED_USERS_LIST item=ASSIGNED_USER key=ASSIGNED_USER_KEY}
								<option value="{$ASSIGNED_USER_KEY}" {if $ASSIGNED_USER_KEY eq $TASK_OBJECT->assigned_user_id} selected="" {/if}>{$ASSIGNED_USER}</option>
							{/foreach}
						</optgroup>
					{/foreach}
					<optgroup label="{\App\Language::translate('LBL_SPECIAL_OPTIONS')}">
						<option value="copyParentOwner" {if $TASK_OBJECT->assigned_user_id eq 'copyParentOwner'} selected="" {/if}>{\App\Language::translate('LBL_PARENT_OWNER')}</option>
						<option value="currentUser" {if $TASK_OBJECT->assigned_user_id eq 'currentUser'} selected="" {/if}>{\App\Language::translate('LBL_CURRENT_USER',$QUALIFIED_MODULE)}</option>
						<option value="triggerUser" {if $TASK_OBJECT->assigned_user_id eq 'triggerUser'} selected="" {/if}>{\App\Language::translate('LBL_TRIGGER_USER',$QUALIFIED_MODULE)}</option>
                    </optgroup>
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			{if $TASK_OBJECT->startTime neq ''}
				{assign var=START_TIME value=$TASK_OBJECT->startTime}
			{else}
				{assign var=DATE_TIME_VALUE value=App\Fields\DateTime::formatToDisplay('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=START_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="col-md-3">{\App\Language::translate('LBL_START_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-9">
				<div class="input-group time">
					<input type="text" class="clockPicker form-control" data-format="{$timeFormat}" autocomplete="off"
						   value="{$START_TIME}" name="startTime"/>
					<div class="input-group-append">
						<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
							<span class="far fa-clock"></span>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<div class="col-md-3 mb-1 mb-md-0">{\App\Language::translate('LBL_START_DATE',$QUALIFIED_MODULE)}</div>
			<div class="col-md-2 mb-1 mb-md-0 pr-md-1">
				<input class="form-control" type="text" value="{$TASK_OBJECT->startDays}" name="startDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
			</div>
			<div class="col-md-4 row no-gutters mb-1 mb-md-0 pr-md-1">
				<div class="col-2 pt-1">{\App\Language::translate('LBL_DAYS',$QUALIFIED_MODULE)}</div>
				<div class="col-10">
					<select class="select2 form-control" name="startDirection">
						<option {if $TASK_OBJECT->startDirection eq 'after'}selected{/if}
								value="after">{\App\Language::translate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
						<option {if $TASK_OBJECT->startDirection eq 'before'}selected{/if}
								value="before">{\App\Language::translate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<span class="col-md-3">
				<select class="select2 form-control" name="startDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->startDatefield eq $DATETIME_FIELD->get('name')}selected{/if}
								value="{$DATETIME_FIELD->get('name')}">{\App\Language::translate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			{if $TASK_OBJECT->endTime neq ''}
				{assign var=END_TIME value=$TASK_OBJECT->endTime}
			{else}
				{assign var=DATE_TIME_VALUE value=App\Fields\DateTime::formatToDisplay('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=END_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<div class="col-md-3">{\App\Language::translate('LBL_END_TIME',$QUALIFIED_MODULE)}</div>
			<div class="col-md-9">
				<div class="input-group time">
					<input type="text" class="clockPicker form-control" value="{$END_TIME}" name="endTime"
						   autocomplete="off"/>
					<div class="input-group-append">
						<div class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
							<span class="far fa-clock"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row no-gutters col-12 col-xl-6 padding-bottom1per">
			<div class="col-md-3 mb-1 mb-md-0">{\App\Language::translate('LBL_END_DATE',$QUALIFIED_MODULE)}</div>
			<div class="col-md-2 mb-1 mb-md-0 pr-md-1">
				<input class="form-control" type="text" value="{$TASK_OBJECT->endDays}" name="endDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
			</div>
			<div class="col-md-4 row no-gutters mb-1 mb-md-0 pr-md-1">

				<div class="col-2 pt-1">{\App\Language::translate('LBL_DAYS',$QUALIFIED_MODULE)}</div>
				<div class="col-10">
					<select class="select2 form-control" name="endDirection">
						<option {if $TASK_OBJECT->endDirection eq 'after'}selected{/if}
								value="after">{\App\Language::translate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
						<option {if $TASK_OBJECT->endDirection eq 'before'}selected{/if}
								value="before">{\App\Language::translate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<span class="col-md-3">
				<select class="select2 form-control" name="endDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->endDatefield eq $DATETIME_FIELD->get('name')}selected{/if}
								value="{$DATETIME_FIELD->get('name')}">{\App\Language::translate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
	</div>
{/strip}
