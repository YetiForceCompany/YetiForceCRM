{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o
********************************************************************************/
-->*}
{strip}
<div class="listViewPageDiv tpl-Settings-Picklist-Index">
	<div class="widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="listViewContentDiv" id="listViewContents" style="padding: 1%;">
		<br/>
		<div class="row">
			<label class="fieldLabel col-md-3"><strong>{\App\Language::translate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
			<div class="col-md-4 fieldValue">
				<select class="select2 form-control" id="pickListModules">
					{foreach item=PICKLIST_MODULE from=$PICKLIST_MODULES}
						<option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE->get('name')} selected="" {/if} value="{$PICKLIST_MODULE->get('name')}">{\App\Language::translate($PICKLIST_MODULE->get('label'),$PICKLIST_MODULE->get('name'))}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<br/>
		<div id="modulePickListContainer">
			{include file=\App\Layout::getTemplatePath('ModulePickListDetail.tpl', $QUALIFIED_MODULE)}
		</div>

		<div id="modulePickListValuesContainer">
			{if empty($NO_PICKLIST_FIELDS)}
				{include file=\App\Layout::getTemplatePath('PickListValueDetail.tpl', $QUALIFIED_MODULE)}
			{/if}
		</div>
	</div>
	{/strip}	
