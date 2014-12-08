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
    <div id="advanceSearchContainer">
        <div class="row-fluid padding10 boxSizingBorderBox">
            <div class="span5">
                <strong class="pull-right pushDown">{vtranslate('LBL_SEARCH_IN',$MODULE)}</strong>
            </div>
            <div class="span7 ">
                <select class="chzn-select pushDown" id="searchModuleList" data-placeholder="{vtranslate('LBL_SELECT_MODULE')}">
                    <option></option>
                    {foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
                        <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SOURCE_MODULE}selected="selected"{/if}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row-fluid">
            <div class="filterElements" id="searchContainer" style="height: auto;">
                <form name="advanceFilterForm">
                    {if $SOURCE_MODULE eq 'Home'}
                        <div class="textAlignCenter well contentsBackground">{vtranslate('LBL_PLEASE_SELECT_MODULE',$MODULE)}</div>
                    {else}
                        <input type="hidden" name="labelFields" data-value='{ZEND_JSON::encode($SOURCE_MODULE_MODEL->getNameFields())}' />
                        {include file='AdvanceFilter.tpl'|@vtemplate_path}
                    {/if}	
                </form>
            </div>
        </div>

        <div class="actions modal-footer">
            <a class="cancelLink pull-right" type="reset" id="advanceSearchCancel" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
            <button class="btn" id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if}  type="submit"><strong>{vtranslate('LBL_SEARCH', $MODULE)}</strong></button>
            <div class="pull-right">
                {if $SAVE_FILTER_PERMITTED}
                    <button class="btn hide pull-right" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceSave"><strong>{vtranslate('LBL_SAVE_FILTER', $MODULE)}</strong></button>
                    <button class="btn pull-right" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceIntiateSave"><strong>{vtranslate('LBL_SAVE_AS_FILTER', $MODULE)}</strong></button>
                    <input class="zeroOpacity pull-right" type="text" value="" name="viewname"/>&nbsp;
                {/if}
            </div>
        </div>
    </div>
</div>
</div>