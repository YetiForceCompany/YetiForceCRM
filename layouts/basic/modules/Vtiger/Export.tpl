{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class='widget_header row '>
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
    <div>
        <form id="exportForm" class="form-horizontal row" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
            <input type="hidden" name="action" value="ExportData" />
            <input type="hidden" name="viewname" value="{$VIEWID}" />
            <input type="hidden" name="selected_ids" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($SELECTED_IDS))}">
            <input type="hidden" name="excluded_ids" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($EXCLUDED_IDS))}">
            <input type="hidden" id="page" name="page" value="{$PAGE}" />
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
            <input type="hidden" name="search_params" value='{\App\Json::encode($SEARCH_PARAMS)}' />

            <div>
                <div class="col-md-8">
                    <div class="well exportContents marginLeftZero">
						<div class="radio">
							<label title="{vtranslate('LBL_EXPORT_SELECTED_RECORDS')}">
								<input type="radio" name="mode" id="optionsRadios1" value="ExportSelectedRecords" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if}>
								{vtranslate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}
								{if empty($SELECTED_IDS)}&nbsp; - <span class="redColor">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
							</label>
						</div>
						<div class="radio">
							<label title="{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}">
								<input type="radio" name="mode" id="optionsRadios2" value="ExportCurrentPage">
								{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}
							</label>
						</div>
						<div class="radio">
							<label title="{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}">
								<input type="radio" name="mode" id="optionsRadios3" value="ExportAllData" {if empty($SELECTED_IDS)} checked="checked" {/if}>
								{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}
							</label>
						</div>
						<br>
						<hr>
						<div class="row">
							<div class="col-md-6">
								<label class="">{vtranslate('LBL_EXPORT_TYPE',$MODULE)}</label>
								<div class="">
									<select class="select2" id="exportType" name="export_type">
										{foreach from=$EXPORT_TYPE item=TYPE key=LABEL}
											<option value="{$TYPE}">{vtranslate({$LABEL},$MODULE)}</option>
										{/foreach}
									</select>
								</div>
							</div>
							{if $XML_TPL_LIST}
								<div class="col-md-6 hide xml-tpl">
									<label class="">{vtranslate('LBL_XML_EXPORT_TPL',$MODULE)}</label>
									<div class="">
										<select class="select2" id="xmlExportType" name="xmlExportType">
											{foreach from=$XML_TPL_LIST item=item key=key}
												<option value="{$item}">{vtranslate({$item}, $MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
							{/if}				
						</div>
                    </div>
                    <div class="pull-left">
                        <button class="btn btn-success saveButton" type="submit"><strong>{vtranslate($MODULE, $MODULE)}</strong></button>
                        <button class="btn btn-warning" type="reset" onclick='window.history.back()'>{vtranslate('LBL_CANCEL', $MODULE)}</button>
                    </div>
                </div>
            </div>

		</form>
    </div>
{/strip}
