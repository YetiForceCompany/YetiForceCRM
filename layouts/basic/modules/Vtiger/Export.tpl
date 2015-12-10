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
    <div style="padding-left: 15px;">
        <form id="exportForm" class="form-horizontal row" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
            <input type="hidden" name="action" value="ExportData" />
            <input type="hidden" name="viewname" value="{$VIEWID}" />
            <input type="hidden" name="selected_ids" value="{ZEND_JSON::encode($SELECTED_IDS)}">
            <input type="hidden" name="excluded_ids" value="{ZEND_JSON::encode($EXCLUDED_IDS)}">
            <input type="hidden" id="page" name="page" value="{$PAGE}" />
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
            <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />

            <div>
                <div>&nbsp;</div>
                <div class="col-md-8">
                    <h4>{vtranslate('LBL_EXPORT_RECORDS',$MODULE)}</h4>
                    <div class="well exportContents marginLeftZero">
                        <fieldset>
                            <legend class="hide">{vtranslate('LBL_EXPORT_RECORDS',$MODULE)}</legend>
                            <div class="row">
                                <div>
					<div class="col-md-8 row pushDown">
						<div class="col-xs-4 ">{vtranslate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}&nbsp;</div>
						<div class="col-xs-1 ">
							<input type="radio" name="mode" title="{vtranslate('LBL_EXPORT_SELECTED_RECORDS')}" value="ExportSelectedRecords" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if}/>
						</div>
						{if empty($SELECTED_IDS)}&nbsp; <div class="col-xs-5 col-md-5 redColor">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</div>{/if}
					</div>
								
				</div>
				<div>
					<div class="col-md-8 row pushDown">
						<div class="col-xs-4">{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}&nbsp;</div>
						<div class="col-xs-1"><input type="radio" name="mode" title="{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}" value="ExportCurrentPage" /></div>
					</div>
				</div>
				<div >
					<div class="col-md-8 row pushDown">
						<div class="col-xs-4 ">{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}&nbsp;</div>
						<div class="col-xs-1"><input type="radio"  name="mode" value="ExportAllData" title="{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}" {if empty($SELECTED_IDS)} checked="checked" {/if} /></div>
					</div>
				</div>
			    </div>
                        </fieldset>
                    </div>
                    <br>
                    <div class="textAlignCenter">
                        <button class="btn btn-success" type="submit"><strong>{vtranslate($MODULE, $MODULE)}&nbsp;{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}</strong></button>
						&nbsp;&nbsp;
                        <button class="btn btn-warning" type="reset" onclick='window.history.back()'>{vtranslate('LBL_CANCEL', $MODULE)}</button>
                    </div>
                </div>
            </div>
	
	</form>
    </div>
{/strip}
