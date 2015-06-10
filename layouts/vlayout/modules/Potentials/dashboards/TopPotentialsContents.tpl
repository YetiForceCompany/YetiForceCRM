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
<div style='padding:5px'>
{if count($MODELS) > 0}
	<div class="table-responsive">
		<table class="table table-hover">
			<thead>
				<tr>
					{foreach item=HEADER from=$MODULE_HEADER}
						<td>
							<strong>{vtranslate({$HEADER}, $MODULE_NAME)}</strong>
						</td>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach item=MODEL from=$MODELS}
					<tr>
					{foreach item=HEADER key=KEY_VALUE from=$MODULE_HEADER}
						<td>
							{if $KEY_VALUE eq 'potentialname'}
								<a href="{$MODEL->getDetailViewUrl()}">{$MODEL->getName()}</a>
							{else}  
								{$MODEL->getDisplayValue({$KEY_VALUE})}
							{/if}
						</td>
					{/foreach}
					<tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
</div>
