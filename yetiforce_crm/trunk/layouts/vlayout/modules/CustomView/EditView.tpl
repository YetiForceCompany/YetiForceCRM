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
    {assign var=SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
    <div class="container-fluid">
        <form class="form-inline" id="CustomView" name="CustomView" method="post" action="index.php">
            <input type=hidden name="record" id="record" value="{$RECORD_ID}" />
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="action" value="Save" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
            <input type="hidden" id="stdfilterlist" name="stdfilterlist" value=""/>
            <input type="hidden" id="advfilterlist" name="advfilterlist" value=""/>
            <input type="hidden" id="status" name="status" value="{$CV_PRIVATE_VALUE}"/>
			<div class="CustomFilterViewTitle">
				<h3>{vtranslate('LBL_CREATE_VIEW',$MODULE)}</h3>
			</div>	
			<hr>
            <input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}">
            <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
            <div class="filterBlocksAlignment">
				<br>
                <div class="row-fluid">
                    <h4 class="filterHeaders">{vtranslate('LBL_BASIC_DETAILS',$MODULE)} :</h4>
                </div>
				<br>
                <div class="row-fluid">
                    <label><span class="redColor">*</span> {vtranslate('LBL_VIEW_NAME',$MODULE)}&nbsp;</label>
                    <input type="text" id="viewname" data-validation-engine='validate[required]' name="viewname" value="{$CUSTOMVIEW_MODEL->get('viewname')}">&nbsp;&nbsp;&nbsp;
                    <label class="checkbox"><input id="setdefault" type="checkbox" name="setdefault" value="1" {if $CUSTOMVIEW_MODEL->isDefault()} checked="checked"{/if}>{vtranslate('LBL_SET_AS_DEFAULT',$MODULE)}</label>&nbsp;&nbsp;&nbsp;
                    <label class="checkbox"><input id="setmetrics" name="setmetrics" type="checkbox" value="1" {if $CUSTOMVIEW_MODEL->get('setmetrics') eq '1'} checked="checked"{/if}>{vtranslate('LBL_LIST_IN_METRICS',$MODULE)}</label>&nbsp;&nbsp;&nbsp;
                    <label class="checkbox"><input id="status" name="status" type="checkbox" {if $CUSTOMVIEW_MODEL->isSetPublic()} value="{$CUSTOMVIEW_MODEL->get('status')}" checked="checked" {else} value="{$CV_PENDING_VALUE}" {/if}>{vtranslate('LBL_SET_AS_PUBLIC',$MODULE)}</label>
                </div>
                <br>
                <h4 class="filterHeaders">{vtranslate('LBL_CHOOSE_COLUMNS',$MODULE)} ({vtranslate('LBL_MAX_NUMBER_FILTER_COLUMNS')}) :</h4>
                <br>
				<div class="columnsSelectDiv">
                    {assign var=MANDATORY_FIELDS value=array()}
                    <select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="select2-container columnsSelect" id="viewColumnsSelect">
                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
                            <optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
                                {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
                                    {if $FIELD_MODEL->isMandatory()}
                                        {array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
                                    {/if}
                                    <option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
                                            {if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
                                                selected
                                            {/if}
                                            >{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
                            {if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
                            </option>
                        {/foreach}
                        </optgroup>
                    {/foreach}
				{*Required to include event fields for columns in calendar module advanced filter*}
                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$EVENT_RECORD_STRUCTURE}
					<optgroup label='{vtranslate($BLOCK_LABEL, 'Events')}'>
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
						{if $FIELD_MODEL->isMandatory()}
							{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
						{/if}
						<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
						{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
							selected
						{/if}
						>{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
						{if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
						</option>
					{/foreach}
					</optgroup>
				{/foreach}
                </select>
                <input type="hidden" name="columnslist" value='{ZEND_JSON::encode($SELECTED_FIELDS)}' />
                <input id="mandatoryFieldsList" type="hidden" value='{ZEND_JSON::encode($MANDATORY_FIELDS)}' />
            </div>
            <br>
            <h4 class="filterHeaders">{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS', $MODULE)} :</h4>
            <br>
			<div class="filterConditionsDiv">
                <div class="row-fluid">
                    <span class="span12">
                        {include file='AdvanceFilter.tpl'|@vtemplate_path}
                    </span>
                </div>
            </div>
        </div>
        <div class="filterActions">
            <a class="cancelLink pull-right" type="reset" onClick="window.location.reload()">{vtranslate('LBL_CANCEL', $MODULE)}</a>
            <button class="btn btn-success pull-right" id="customViewSubmit" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
        </div>
    </form>
</div>
{/strip}
