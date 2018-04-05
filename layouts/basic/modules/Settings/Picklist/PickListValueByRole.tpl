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
	<br /><br />
	<div class="row">
		<div class="col-md-2">&nbsp;</div>
		<div class="col-md-4 well-md" style="overflow: hidden">
			<div id="assignToRolepickListValuesTable" class="fontBold textAlignCenter">
				{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$ALL_PICKLIST_VALUES}
					<div data-value="{$PICKLIST_VALUE}" data-id="{$PICKLIST_KEY}" class="valuesAssignedToRole u-cursor-pointer assignToRolePickListValue {if in_array($PICKLIST_VALUE,$ROLE_PICKLIST_VALUES)}selectedCell{else}unselectedCell{/if}">
						{if in_array($PICKLIST_VALUE,$ROLE_PICKLIST_VALUES)}<i class="fas fa-check float-left"></i>{/if}{\App\Language::translate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}
					</div>
				{/foreach}
			</div>
		</div>
		<div class="col-md-6">
			<div><span class="fas fa-info-circle"></span>&nbsp;&nbsp;<span class="selectedCell padding1per">{\App\Language::translate('LBL_SELECTED_VALUES',$QUALIFIED_MODULE)}</span>&nbsp;<span>{\App\Language::translate('LBL_SELECTED_VALUES_MESSGAE',$QUALIFIED_MODULE)}</span></div><br />
			<div><span class="fas fa-info-circle"></span>&nbsp;&nbsp;<span>{\App\Language::translate('LBL_ENABLE/DISABLE_MESSGAE',$QUALIFIED_MODULE)}</span></div><br />
			&nbsp;&nbsp;<button id="saveOrder" disabled="" class="btn btn-success">{\App\Language::translate('LBL_SAVE',$QUALIFIED_MODULE)}</button>
		</div>		
	</div>				
{/strip}	
