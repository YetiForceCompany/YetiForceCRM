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
	<div class="listViewPageDiv">
		<div class="widget_header row">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents" style="padding: 1%;">
			<br>
			<div class="row">
				<label class="fieldLabel col-md-3"><strong>{vtranslate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
				<div class="col-md-4 fieldValue">
					<select class="chzn-select form-control" id="pickListModules">
						<optgroup>
							<option value="">{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
							{foreach item=PICKLIST_MODULE from=$PICKLIST_MODULES}
								<option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE->get('name')} selected="" {/if} value="{$PICKLIST_MODULE->get('name')}">{vtranslate($PICKLIST_MODULE->get('label'),$PICKLIST_MODULE->get('name'))}</option>
							{/foreach}	
						</optgroup>
					</select>
				</div>
			</div><br>
			<div id="modulePickListContainer">
				{include file="ModulePickListDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
			</div>

			<div id="modulePickListValuesContainer">
                {if empty($NO_PICKLIST_FIELDS)}
                {include file="PickListValueDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
                {/if}
            </div>
		</div>
	{/strip}	
