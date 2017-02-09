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
	<div id="addEventRepeatUI">
		<div>
			<span>{App\Language::translate('LBL_REPEATEVENT', $MODULE_NAME)}&nbsp;{$RECURRING_INFORMATION['INTERVAL']} &nbsp;{App\Language::translate($RECURRING_INFORMATION['freqLabel'], $MODULE_NAME)}</span>
		</div>
		<div>
			<span>{$RECURRING_INFORMATION['repeat_str']}</span>
		</div>
		<div>
			{App\Language::translate('LBL_UNTIL', $MODULE)}&nbsp;
			{if isset($RECURRING_INFORMATION['COUNT'])} 
				{if $RECURRING_INFORMATION['COUNT'] eq 0} 
					{App\Language::translate('LBL_NEVER', $MODULE)}
				{else}
					{App\Language::translate('LBL_COUNT', $MODULE)}: &nbsp;{$RECURRING_INFORMATION['COUNT']}
				{/if}
			{else}
				{$RECURRING_INFORMATION['UNTIL']}
			{/if}
		</div>
	</div>
{/strip}
