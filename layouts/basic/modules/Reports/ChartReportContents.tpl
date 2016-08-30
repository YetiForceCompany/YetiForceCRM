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

<input type='hidden' name='charttype' value="{$CHART_TYPE}" />
<input type='hidden' name='data' class="widgetData" value="{Vtiger_Util_Helper::toSafeHTML($DATA)}" />
<input type='hidden' name='clickthrough' value="{$CLICK_THROUGH}" />

<br>
<div style="margin:0px 20px;">
	<div class='border1px' style="padding:30px 100px;">
		<div class='chartcontent' style="min-height:400px;" ></div>
		<br>
		{if $CLICK_THROUGH neq 'true'}
			<div class='row alert alert-info'>
				<span class='col-md-3'></span>
				<span class='span alert-info'>
					<span class="glyphicon glyphicon-info-sign"></span>
					{vtranslate('LBL_CLICK_THROUGH_NOT_AVAILABLE', $MODULE)}
				</span>
			</div>
			<br>
		{/if}
	</div>
</div>
<br>
