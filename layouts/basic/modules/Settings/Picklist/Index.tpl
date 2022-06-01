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
	<div class="listViewPageDiv tpl-Settings-Picklist-Index">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="listViewContentDiv js-container p-1 mt-2" id="listViewContents">
			<div class="form-group row">
				<label class="col-form-label col-md-3"><strong>{\App\Language::translate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
				<div class="col-md-4">
					<select class="select2 form-control" id="pickListModules">
						{foreach item=PICKLIST_MODULE from=$PICKLIST_MODULES}
							<option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE.tabname} selected="" {/if} value="{$PICKLIST_MODULE.tabname}">{\App\Language::translate($PICKLIST_MODULE.tablabel, $PICKLIST_MODULE.tabname)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div id="modulePickListContainer" class="js-picklist-container">
				{include file=\App\Layout::getTemplatePath('ModulePickListDetail.tpl', $QUALIFIED_MODULE)}
			</div>
			<div id="modulePickListValuesContainer" class="js-picklist-data-container">
				{if !empty($SELECTED_PICKLIST_FIELDMODEL)}
					{include file=\App\Layout::getTemplatePath('PickListValueDetail.tpl', $QUALIFIED_MODULE)}
				{/if}
			</div>
		</div>
{/strip}
