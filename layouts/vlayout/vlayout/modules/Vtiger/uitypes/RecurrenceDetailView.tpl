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
<div id="addEventRepeatUI">
	<div><span>{$RECURRING_INFORMATION['recurringcheck']}</span></div>
	{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}
	<div>
		<span>{vtranslate('LBL_REPEATEVENT', $MODULE_NAME)}&nbsp;{$RECURRING_INFORMATION['repeat_frequency']}&nbsp;{vtranslate($RECURRING_INFORMATION['recurringtype'], $MODULE_NAME)}</span>
	</div>
	<div>
		<span>{$RECURRING_INFORMATION['repeat_str']}</span>
	</div>
	<div>{vtranslate('LBL_UNTIL', $MODULE)}&nbsp;&nbsp;{$RECURRING_INFORMATION['recurringenddate']}</div>
	{/if}
</div>