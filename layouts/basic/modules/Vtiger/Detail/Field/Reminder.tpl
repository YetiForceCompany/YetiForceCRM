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
	{assign var=REMINDER_VALUES value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
	<div class="tpl-Detail-Field-Reminder">
		{if $REMINDER_VALUES eq ''}
			{\App\Language::translate('LBL_NO', $MODULE)}
		{else}
			{$REMINDER_VALUES}{\App\Language::translate('LBL_BEFORE_EVENT', $MODULE)}
		{/if}
	</div>
{/strip}
