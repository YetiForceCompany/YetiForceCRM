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
{assign var=REMINDER_VALUES value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{if $REMINDER_VALUES eq ''}
    {assign var=DAYS value=0}
	{assign var=HOURS value=0}
	{assign var=MINUTES value=1}
{else}
    {assign var=DAY value=$REMINDER_VALUES[0]}
	{assign var=HOUR value=$REMINDER_VALUES[1]}
	{assign var=MINUTE value=$REMINDER_VALUES[2]}
{/if}

<div>
	<div style="float:left">
		<input type=hidden name=set_reminder value=0 />
		<input type=checkbox name=set_reminder {if $REMINDER_VALUES neq ''}checked{/if} value=1 />&nbsp;&nbsp;
	</div>
	<div style="float:left" class="{if $REMINDER_VALUES neq ''}show{else}hide{/if}">
		<div style="float:left">
			<div style="float:left">
				<select class="chzn-select input-mini" name="remdays">
					{for $DAYS = 0 to 31}
						<option value="{$DAYS}" {if $DAYS eq $DAY}selected{/if}>{$DAYS}</option>
					{/for}
				</select>
			</div>
			<div style="float:left;margin-top:5px">
				&nbsp;{vtranslate('LBL_DAYS', $MODULE)}&nbsp;&nbsp;
			</div>
			<div class="clearfix"></div>
		</div>
		<div style="float:left">
			<div style="float:left">
				<select class="chzn-select input-mini" name="remhrs">
					{for $HOURS = 0 to 23}
						<option value="{$HOURS}" {if $HOURS eq $HOUR}selected{/if}>{$HOURS}</option>
					{/for}
				</select>
			</div>
			<div style="float:left;margin-top:5px">
				&nbsp;{vtranslate('LBL_HOURS', $MODULE)}&nbsp;&nbsp;
			</div>
			<div class="clearfix"></div>
		</div>
		<div style="float:left">
			<div style="float:left">
				<select class="chzn-select  input-mini" name="remmin">
				{for $MINUTES = 1 to 59}
					<option value="{$MINUTES}" {if $MINUTES eq $MINUTE}selected{/if}>{$MINUTES}</option>
				{/for}
				</select>
			</div>
			<div style="float:left;margin-top:5px">
				&nbsp;{vtranslate('LBL_MINUTES', $MODULE)}&nbsp;&nbsp;
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
{/strip}