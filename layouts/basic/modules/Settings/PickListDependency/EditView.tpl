{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
*
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Settings-PickListDependency-EditView">
		<div class="o-breadcrumb widget_header row mb-3">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents js-picklist-dependent-container" data-js="container">
			<form id="pickListDependencyForm" method="POST">
				{if !empty($MAPPED_VALUES)}
					<input type="hidden" class="editDependency" value="true" />
					<input type="hidden" name="sourceModule" value="{$SELECTED_MODULE}" />
					<input type="hidden" name="sourceField" value="{$RECORD_MODEL->get('source_field')}" />
					<input type="hidden" name="secondField" value="{$RECORD_MODEL->get('second_field')}" />
					<input type="hidden" name="thirdField" value="{$RECORD_MODEL->get('third_field')}" />
				{/if}
				<div class="js-dependent-fields row" data-js="container">
					{include file=\App\Layout::getTemplatePath('DependentFields.tpl', $QUALIFIED_MODULE)}
				</div>
				<div class="d-none errorMessage my-3">
					<div class="alert alert-warning">
						<strong>{\App\Language::translate('LBL_ERR_CYCLIC_DEPENDENCY', $QUALIFIED_MODULE)}</strong>
					</div>
				</div>
				<div id="dependencyGraph" class="my-3 w-100 js-dependency-tables-container" data-js="container">
					{if $DEPENDENCY_GRAPH}
						{if $RECORD_MODEL->get('third_field')}
							{include file=\App\Layout::getTemplatePath('DependentFieldSettings.tpl', $QUALIFIED_MODULE)}
						{else}
							{include file=\App\Layout::getTemplatePath('DependencyGraph.tpl', $QUALIFIED_MODULE)}
						{/if}
					{/if}
				</div>
			</form>
		</div>
	</div>
{/strip}
