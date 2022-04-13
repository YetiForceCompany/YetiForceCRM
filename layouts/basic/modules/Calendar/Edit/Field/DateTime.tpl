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
	<!-- tpl-Calendar-Edit-Field-DateTime -->
	{if $FIELD_MODEL->getName() == 'date_start'}
		{assign var=DATE_FIELD value=$FIELD_MODEL}
		{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
		{assign var=TIME_FIELD value=$MODULE_MODEL->getField('time_start')}
		{assign var=TIME_NAME value='time_start'}
	{elseif $FIELD_MODEL->getName() == 'due_date'}
		{assign var=DATE_FIELD value=$FIELD_MODEL}
		{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
		{assign var=TIME_FIELD value=$MODULE_MODEL->getField('time_end')}
		{assign var=TIME_NAME value='time_end'}
	{/if}
	{if empty($BLOCK_FIELDS)}
		{assign var=BLOCK_FIELDS value=false}
	{/if}
	{assign var=DATE_TIME_VALUE value=((string) $FIELD_MODEL->get('fieldvalue'))}
	{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
	{if count($DATE_TIME_COMPONENTS) eq 2}
		{assign var=TIME_FIELD value=$TIME_FIELD->set('fieldvalue',$DATE_TIME_COMPONENTS[1])}
	{elseif !empty($RECORD)}
		{assign var=TIME_FIELD value=$TIME_FIELD->set('fieldvalue',$RECORD->get($TIME_NAME))}
	{/if}
	{* Set the date after converting with repsect to timezone *}
	{assign var=DATE_TIME_CONVERTED_VALUE value=DateTimeField::convertToUserTimeZone($DATE_TIME_VALUE)->format('Y-m-d H:i:s')}
	{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_CONVERTED_VALUE)}
	{assign var=DATE_FIELD value=$DATE_FIELD->set('fieldvalue',$DATE_TIME_COMPONENTS[0])}
	<div class="tpl-Edit-Field-DateTime form-row">
		<div class="col-12 col-sm-6 col-md-5 mb-3 mb-sm-0 px-1">
			{include file=\App\Layout::getTemplatePath('Edit/Field/Date.tpl', $MODULE) BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$DATE_FIELD}
		</div>
		<div class="col-12 col-sm-6 col-md-7 px-1">
			{include file=\App\Layout::getTemplatePath('Edit/Field/Time.tpl', $MODULE) BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$TIME_FIELD}
		</div>
	</div>
	<!-- /tpl-Calendar-Edit-Field-DateTime -->
{/strip}
