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
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div>
		<form id="exportForm" class="form-horizontal row" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="action" value="ExportData" />
			<input type="hidden" name="viewname" value="{$VIEWID}" />
			<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
			<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
			<input type="hidden" id="page" name="page" value="{$PAGE}" />
			<input type="hidden" name="search_key" value="{$SEARCH_KEY}" />
			<input type="hidden" name="operator" value="{$OPERATOR}" />
			<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
			<input type="hidden" name="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}" />
			<input type="hidden" name="entityState" value="{$ENTITY_STATE}" />
			<input type="hidden" name="advancedConditions" value="{\App\Purifier::encodeHtml(\App\Json::encode($ADVANCED_CONDITIONS))}" />
			<div class="col-md-8">
				<div class="p-3 card bg-light exportContents ml-0 my-2">
					<div class="radio">
						<label title="{\App\Language::translate('LBL_EXPORT_SELECTED_RECORDS')}">
							<input class="mr-1" type="radio" name="mode" id="optionsRadios1" value="ExportSelectedRecords" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled" {/if}>
							{\App\Language::translate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}
							{if empty($SELECTED_IDS)}&nbsp; - <span class="redColor">{\App\Language::translate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
						</label>
					</div>
					<div class="radio">
						<label title="{\App\Language::translate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}">
							<input class="mr-1" type="radio" name="mode" id="optionsRadios2" value="ExportCurrentPage">
							{\App\Language::translate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}
						</label>
					</div>
					<div class="radio">
						<label title="{\App\Language::translate('LBL_EXPORT_ALL_DATA',$MODULE)}">
							<input class="mr-1" type="radio" name="mode" id="optionsRadios3" value="ExportAllData" {if empty($SELECTED_IDS)} checked="checked" {/if}>
							{\App\Language::translate('LBL_EXPORT_ALL_DATA',$MODULE)}
						</label>
					</div>
					<br />
					<hr>
					<div class="row">
						<div class="col-md-6">
							<label class="">{\App\Language::translate('LBL_EXPORT_TYPE',$MODULE)}</label>
							<div class="">
								<select class="select2" id="exportType" name="export_type">
									{foreach from=$EXPORT_TYPE item=TYPE key=LABEL}
										<option value="{$TYPE}">{\App\Language::translate({$LABEL},$MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
						{if $XML_TPL_LIST}
							<div class="col-md-6 d-none xml-tpl">
								<label class="">{\App\Language::translate('LBL_XML_EXPORT_TPL',$MODULE)}</label>
								<div class="">
									<select class="select2" id="xmlExportType" name="xmlExportType">
										{foreach from=$XML_TPL_LIST item=item key=key}
											<option value="{$item}">{\App\Language::translate({$item}, $MODULE)}</option>
										{/foreach}
									</select>
								</div>
							</div>
						{/if}
					</div>
				</div>
				<div class="float-left">
					<button class="btn btn-success saveButton mr-3" type="submit">
						<strong>
							<span class="fas fa-upload mr-2"></span>
							{\App\Language::translate($MODULE, $MODULE)}
						</strong>
					</button>
					<button class="btn btn-danger" type="reset" onclick='window.history.back()'>
						<span class="fas fa-times mr-2"></span>
						{\App\Language::translate('LBL_CANCEL', $MODULE)}
					</button>
				</div>
			</div>
		</form>
	</div>
{/strip}
