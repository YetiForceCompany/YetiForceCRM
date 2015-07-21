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
	<br><br>
	<div class="row">
		<div class="col-md-2">&nbsp;</div>
		<div class="col-md-4 well-md" style="overflow: hidden">
			<div id="assignToRolepickListValuesTable" class="fontBold textAlignCenter">
				{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$ALL_PICKLIST_VALUES}
					<div data-value="{$PICKLIST_VALUE}" data-id="{$PICKLIST_KEY}" class="valuesAssignedToRole cursorPointer assignToRolePickListValue {if in_array($PICKLIST_VALUE,$ROLE_PICKLIST_VALUES)}selectedCell{else}unselectedCell{/if}">
						{if in_array($PICKLIST_VALUE,$ROLE_PICKLIST_VALUES)}<i class="glyphicon glyphicon-ok pull-left"></i>{/if}{vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}
					</div>
				{/foreach}
			</div>
		</div>
		<div class="col-md-6">
			<div><span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;<span class="selectedCell padding1per">{vtranslate('LBL_SELECTED_VALUES',$QUALIFIED_MODULE)}</span>&nbsp;<span>{vtranslate('LBL_SELECTED_VALUES_MESSGAE',$QUALIFIED_MODULE)}</span></div><br>
			<div><span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;<span>{vtranslate('LBL_ENABLE/DISABLE_MESSGAE',$QUALIFIED_MODULE)}</span></div><br>
			&nbsp;&nbsp;<button id="saveOrder" disabled="" class="btn btn-success">{vtranslate('LBL_SAVE',$QUALIFIED_MODULE)}</button>
		</div>		
	</div>				
{/strip}	
