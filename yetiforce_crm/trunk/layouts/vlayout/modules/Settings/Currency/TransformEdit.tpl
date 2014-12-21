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
    {assign var=CURRENCY_ID value=$RECORD_MODEL->getId()}
    <div class="currencyTransformModalContainer">
        <div class="modal-header contentsBackground">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>{vtranslate('LBL_TRANSFER_CURRENCY', $QUALIFIED_MODULE)}</h3>
        </div>
        <form id="transformCurrency" class="form-horizontal" method="POST">
            <input type="hidden" name="record" value="{$CURRENCY_ID}" />
            <div class="modal-body">
                <div class="row-fluid">
                    <div class="control-group">
                        <label class="muted control-label">{vtranslate('LBL_CURRENT_CURRENCY', $QUALIFIED_MODULE)}</label>
                        <div class="controls">
                            <span>{vtranslate($RECORD_MODEL->get('currency_name'), $QUALIFIED_MODULE)}</span>
                        </div>	
                    </div>
                    <div class="control-group">
                        <label class="muted control-label">{vtranslate('LBL_TRANSFER_CURRENCY', $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_TO', $QUALIFIED_MODULE)}</label>
                        <div class="controls row-fluid">
                            <select class="select2 span6" name="transform_to_id">
                                {foreach key=CURRENCY_ID item=CURRENCY_MODEL from=$CURRENCY_LIST}
                                    <option value="{$CURRENCY_ID}">{vtranslate($CURRENCY_MODEL->get('currency_name'), $QUALIFIED_MODULE)}</option>
                                {/foreach}
                            </select>
                        </div>	
                    </div>
                </div>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
        </form>
    </div>
{/strip}