{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
<div class='verticalScroll'>
	<div class='editViewContainer'>
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
				<input type="hidden" name="module" value="{$MODULE}"/>
				<input type="hidden" name="parent" value="{$PARENT_MODULE}"/>
				<input type="hidden" name="conditions" value="">
				<input type="hidden" name="action" value="Save"/>
			{if !empty($RECORD_ID)}
				<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}"/>
			{/if}
			<div class="widget_header row mb-3">
				<div class="col-md-8">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="card">
				<div class="card-header">
					{if !empty($RECORD->getId())}
					<span class="fas fa-edit mr-2"></span>
						{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)} - {$RECORD->getName()}
					{else}
						<span class="fas fa-plus mr-2"></span>
						{\App\Language::translate('LBL_CREATE',$QUALIFIED_MODULE)}
					{/if}
				</div>
				<div class="card-body">
					<div class="form-group row">
						<div class="col-12 col-md-4">
							<label>{\App\Language::translate('LBL_NAME',$QUALIFIED_MODULE)}</label>
							<input type="text" name="name" class="form-control"  value="{$RECORD->getName()}" data-validation-engine="validate[required,maxSize[255]]">
						</div>
						<div class="col-12 col-md-4">
							<label>{\App\Language::translate('LBL_OPERATIONAL_HOURS',$QUALIFIED_MODULE)}</label>
							<select name="operational_hours" class="select2"  data-validation-engine="validate[required]">
								<option value="0"{if $RECORD->get('operational_hours')===0}selected="selected"{/if}>{\App\Language::translate('LBL_CALENDAR_HOURS',$QUALIFIED_MODULE)}</option>
								<option value="1"{if $RECORD->get('operational_hours')===1}selected="selected"{/if}>{\App\Language::translate('LBL_BUSINESS_HOURS',$QUALIFIED_MODULE)}</option>
							</select>
						</div>
						<div class="col-12 col-md-4">
							<label>{\App\Language::translate('LBL_SOURCE_MODULE',$QUALIFIED_MODULE)}</label>
							<select name="source_module" class="select2"  data-validation-engine="validate[required]">
								{foreach item=MODULE_NAME from=$MODULES}
									<option value="{$MODULE_NAME}"{if \App\Module::getModuleName($RECORD->get('tabid')) === $MODULE_NAME}selected="selected"{/if}>{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group row">
						<div class="col js-condition-builder-view" data-js="container"></div>
					</div>
				</div>
			</div>
{/strip}
