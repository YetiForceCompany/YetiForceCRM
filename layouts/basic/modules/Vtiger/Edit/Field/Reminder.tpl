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
	{assign var=REMINDER_VALUES value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
	{assign var=TABINDEX value=$FIELD_MODEL->getTabIndex()}
	{if $REMINDER_VALUES eq ''}
		{assign var=DAYS value=0}
		{assign var=DAY value=0}
		{assign var=HOURS value=0}
		{assign var=HOUR value=0}
		{assign var=MINUTES value=1}
		{assign var=MINUTE value=1}
	{else}
		{assign var=DAY value=$REMINDER_VALUES[0]}
		{assign var=HOUR value=$REMINDER_VALUES[1]}
		{assign var=MINUTE value=$REMINDER_VALUES[2]}
	{/if}
	<div class="tpl-Edit-Field-Reminder d-flex flex-nowrap js-reminder-field-element"
		data-js="container">
		<div class="checkbox">
			<input name="{$FIELD_MODEL->getName()}" value="0" type="hidden">
			<label class="d-flex align-items-baseline">
				<input name="{$FIELD_MODEL->getName()}" value="1" type="checkbox" class="js-reminder-field-checkbox" {if $REMINDER_VALUES neq ''}checked="checked" {/if} tabindex="{$TABINDEX}"
					title="{\App\Language::translate('Send Reminder', $MODULE)}" data-js="checked" />&nbsp;&nbsp;
			</label>
		</div>
		<div class="{if $REMINDER_VALUES neq ''}show{else}d-none{/if} row w-100 js-reminder-field-row"
			data-js="class:d-none">
			<div class="col-4">
				<div>
					<select class="select2" name="remdays" tabindex="{$TABINDEX}" title="{\App\Language::translate('LBL_REMAIND_DAYS', $MODULE)}">
						{for $DAYS = 0 to 31}
							<option value="{$DAYS}" {if $DAYS eq $DAY}selected{/if}>{$DAYS}</option>
						{/for}
					</select>
				</div>
				<div class="float-left mt-1 px-1">
					{\App\Language::translate('LBL_DAYS', $MODULE)}
				</div>
			</div>
			<div class="col-4">
				<div>
					<select class="select2" name="remhrs" tabindex="{$TABINDEX}" title="{\App\Language::translate('LBL_REMAIND_HOURS', $MODULE)}">
						{for $HOURS = 0 to 23}
							<option value="{$HOURS}" {if $HOURS eq $HOUR}selected{/if}>{$HOURS}</option>
						{/for}
					</select>
				</div>
				<div class="float-left mt-1 px-1">
					{\App\Language::translate('LBL_HOURS', $MODULE)}
				</div>
			</div>
			<div class="col-4">
				<div>
					<select class="select2" name="remmin" tabindex="{$TABINDEX}" title="{\App\Language::translate('LBL_REMAIND_MINS', $MODULE)}">
						{for $MINUTES = 1 to 59}
							<option value="{$MINUTES}" {if $MINUTES eq $MINUTE}selected{/if}>{$MINUTES}</option>
						{/for}
					</select>
				</div>
				<div class="float-left mt-1 px-1">
					{\App\Language::translate('LBL_MINUTES', $MODULE)}
				</div>
			</div>
		</div>
	</div>
{/strip}
