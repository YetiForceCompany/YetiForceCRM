{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
<div class="">
    <form id="EditView" method="POST">
        <div class="">
            <span class="widget_header">
                <div class="">
					<h3>{vtranslate('LBL_CUSTOMIZE_RECORD_NUMBERING', $QUALIFIED_MODULE)}</h3>
					<span style="font-size:12px;color: black;">{vtranslate('LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', $QUALIFIED_MODULE)}</span>
				</div>
            </span>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table id="customRecordNumbering" class="table table-bordered">
                {assign var=DEFAULT_MODULE_DATA value=$DEFAULT_MODULE_MODEL->getModuleCustomNumberingData()}
                {assign var=DEFAULT_MODULE_NAME value=$DEFAULT_MODULE_MODEL->getName()}
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                    <thead>
                        <tr>
                            <th width="30%" class="{$WIDTHTYPE}">
                                <strong>{vtranslate('LBL_CUSTOMIZE_RECORD_NUMBERING', $QUALIFIED_MODULE)}</strong>
                            </th>
                            <th width="70%" class="{$WIDTHTYPE}" style="border-left: none">
                            <span class="pull-right">
                                <button type="button" class="btn btn-info" name="updateRecordWithSequenceNumber"><b>{vtranslate('LBL_UPDATE_MISSING_RECORD_SEQUENCE', $QUALIFIED_MODULE)}</b></button>
                            </span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                    <tr>
                        <td class="{$WIDTHTYPE}">
                            <label class="pull-right marginRight10px"><b>{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</b></label>
                        </td>
                        <td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
                            <select class="chzn-select form-control" name="sourceModule">
                                {foreach key=index item=MODULE_MODEL from=$SUPPORTED_MODULES}
                                    {assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
                                    <option value={$MODULE_NAME} {if $MODULE_NAME eq $DEFAULT_MODULE_NAME} selected {/if}>
                                        {vtranslate($MODULE_NAME, $MODULE_NAME)}
                                    </option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="{$WIDTHTYPE}">
                            <label class="pull-right marginRight10px"><b>{vtranslate('LBL_USE_PREFIX', $QUALIFIED_MODULE)}</b></label>
                        </td>
                        <td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
                            <input type="text" class="form-control" value="{$DEFAULT_MODULE_DATA['prefix']}" data-old-prefix="{$DEFAULT_MODULE_DATA['prefix']}" name="prefix" data-validation-engine="validate[funcCall[Vtiger_AlphaNumericWithSlashes_Validator_Js.invokeValidation]]"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="{$WIDTHTYPE}">
                            <label class="pull-right marginRight10px">
                                <b>{vtranslate('LBL_START_SEQUENCE', $QUALIFIED_MODULE)}</b><span class="redColor">*</span>
                            </label>
                        </td>
                        <td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
                            <input type="text" class="form-control" value="{$DEFAULT_MODULE_DATA['sequenceNumber']}"
                                   data-old-sequence-number="{$DEFAULT_MODULE_DATA['sequenceNumber']}" name="sequenceNumber"
                                   data-validation-engine="validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]"/>
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12 pull-right">
                <div class="pull-right">
                    <button class="btn btn-success saveButton" type="submit" disabled="disabled"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
                    <button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
                </div>
            </div>
        </div>
    </form>
</div>
{/strip}
