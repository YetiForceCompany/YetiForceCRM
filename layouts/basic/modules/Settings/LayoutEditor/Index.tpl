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
    <div id="layoutEditorContainer">
        <input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
        <div class="widget_header row">
			<div class="col-md-6">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				{if isset($SELECTED_PAGE)}
					{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
			<div class="pull-right col-md-6 form-inline">
				<div class="form-group pull-right col-md-6">
					<select class="select2 form-control" name="layoutEditorModules">
						{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
							<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group pull-right">
					<input id="inventorySwitch" title="{vtranslate('LBL_CHANGE_BLOCK_ADVANCED', $QUALIFIED_MODULE)}" class="switchBtn" type="checkbox" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_BASIC_MODULE',$QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_ADVANCED_MODULE',$QUALIFIED_MODULE)}" {if !$IS_INVENTORY}checked{/if} >
				</div>
			</div>
        </div>
        <hr>
        <div class="contents tabbable">
            <ul class="nav nav-tabs layoutTabs massEditTabs">
                <li class="active"><a data-toggle="tab" href="#detailViewLayout"><strong>{vtranslate('LBL_DETAILVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong></a></li>
							{if $IS_INVENTORY}
					<li class="inventoryNav"><a data-toggle="tab" href="#inventoryViewLayout"><strong>{vtranslate('LBL_MANAGING_AN_ADVANCED_BLOCK', $QUALIFIED_MODULE)}</strong></a></li>
							{/if}
            </ul>
            <div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
                <div class="tab-pane active" id="detailViewLayout">
                    {assign var=FIELD_TYPE_INFO value=$SELECTED_MODULE_MODEL->getAddFieldTypeInfo()}
                    {assign var=IS_SORTABLE value=$SELECTED_MODULE_MODEL->isSortableAllowed()}
                    {assign var=IS_BLOCK_SORTABLE value=$SELECTED_MODULE_MODEL->isBlockSortableAllowed()}
                    {assign var=ALL_BLOCK_LABELS value=[]}
                    {if $IS_SORTABLE}
                        <div class="btn-toolbar" id="layoutEditorButtons">
                            <button class="btn btn-success addButton addCustomBlock" type="button">
                                <span class="glyphicon glyphicon-plus"></span>&nbsp;
                                <strong>{vtranslate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</strong>
                            </button>
                            <span class="pull-right">
                                <button class="btn btn-success saveFieldSequence hide" type="button">
                                    <strong>{vtranslate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
                                </button>
                            </span>
                        </div>
                    {/if}
                    <div class="moduleBlocks">
                        {foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
                            {assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
                            {assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
                            {$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_LABEL_KEY}
                            <div id="block_{$BLOCK_ID}" class="editFieldsTable block_{$BLOCK_ID} marginBottom10px border1px {if $IS_BLOCK_SORTABLE} blockSortable{/if}" data-block-id="{$BLOCK_ID}" data-sequence="{$BLOCK_MODEL->get('sequence')}" style="border-radius: 4px;background: white;">
                                <div class="row layoutBlockHeader no-margin">
                                    <div class="blockLabel col-md-6 col-sm-6 padding10 marginLeftZero">
                                        {if $IS_BLOCK_SORTABLE}<img class="alignMiddle" src="{vimage_path('drag.png')}" alt=""/>&nbsp;&nbsp;{/if}
                                        <strong>{vtranslate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}</strong>
                                    </div>
                                    <div class="col-md-6 col-sm-6 marginLeftZero " ><div class="pull-right btn-toolbar blockActions" style="margin: 4px;">
                                            {if $BLOCK_MODEL->isAddCustomFieldEnabled()}
                                                <div class="btn-group">
                                                    <button class="btn btn-success addCustomField" type="button">
                                                        <strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
                                                    </button>
                                                </div>
                                            {/if}
                                            {if $BLOCK_MODEL->isActionsAllowed()}
                                                <div class="btn-group"><button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                        <strong>{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</strong>&nbsp;&nbsp;
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu pull-right">
                                                        <li class="blockVisibility" data-visible="{if !$BLOCK_MODEL->isHidden()}1{else}0{/if}" data-block-id="{$BLOCK_MODEL->get('id')}">
                                                            <a href="javascript:void(0)">
                                                                <span class="glyphicon glyphicon-ok {if $BLOCK_MODEL->isHidden()} hide {/if}"></span>&nbsp;
                                                                {vtranslate('LBL_ALWAYS_SHOW', $QUALIFIED_MODULE)}
                                                            </a>
                                                        </li>
                                                        <li class="inActiveFields">
                                                            <a href="javascript:void(0)">{vtranslate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</a>
                                                        </li>
                                                        {if $BLOCK_MODEL->isCustomized()}
                                                            <li class="deleteCustomBlock">
                                                                <a href="javascript:void(0)">{vtranslate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</a>
                                                            </li>
                                                        {/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="blockFieldsList blockFieldsSortable row no-margin" style="padding:5px;min-height: 27px">
                                    <ul name="{if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}sortable1{/if}" class="sortTableUl connectedSortable col-md-6">
                                        {foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist}
                                            {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                                            {if $smarty.foreach.fieldlist.index % 2 eq 0}
                                                <li>
                                                    <div class="opacity editFields marginLeftZero border1px" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
                                                        <div class="row padding1per">
                                                            {assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
                                                            <div class="col-xs-2 col-sm-2">&nbsp;
                                                                {if $FIELD_MODEL->isEditable()}
                                                                    <a>
                                                                        <img src="{vimage_path('drag.png')}" border="0" alt="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
                                                                    </a>
                                                                {/if}
                                                            </div>
                                                            <div class="col-xs-10 col-sm-10 marginLeftZero fieldContainer" style="word-wrap: break-word;">
                                                                <span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}&nbsp;[{$FIELD_MODEL->get('name')}]
																	{if $IS_MANDATORY}<span class="redColor">*</span>{/if}</span>
																<span class="btn-group pull-right actions">
																	<input type="hidden" value="{$FIELD_MODEL->get('name')}" id="relatedFieldValue{$FIELD_MODEL->get('id')}" />
																	<a href="javascript:void(0)" class="copyFieldLabel pull-right" data-target="relatedFieldValue{$FIELD_MODEL->get('id')}">
																		<span class="glyphicon glyphicon-copy alignMiddle" title="{vtranslate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
																	</a>
																	{if $FIELD_MODEL->isEditable()}
																		<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
																			<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
																		</a>
																		<div class="basicFieldOperations pull-right hide" style="width : 250px;">
																			<form class="form-horizontal fieldDetailsForm" method="POST">
																				<div class="modal-header contentsBackground">
																					<strong>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</strong>
																					<div class="pull-right"><a href="javascript:void(0)" class='cancel'>X</a></div>
																				</div>
																				<div style="padding-bottom: 5px;">
																					<span>
																						<input type="hidden" name="mandatory" value="O" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="mandatory" {if $IS_MANDATORY} checked {/if}
																								   {if $FIELD_MODEL->isMandatoryOptionDisabled()} readonly="readonly" {/if} value="M" />&nbsp;
																							{vtranslate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="presence" value="1" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="presence" {if $FIELD_MODEL->isViewable()} checked {/if}
																								   {if $FIELD_MODEL->isActiveOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if} value="{$FIELD_MODEL->get('presence')}" />&nbsp;
																							{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="quickcreate" value="1" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="quickcreate" {if $FIELD_MODEL->isQuickCreateEnabled()} checked {/if}
																								   {if $FIELD_MODEL->isQuickCreateOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if} value="2" />&nbsp;
																							{vtranslate('LBL_QUICK_CREATE', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="summaryfield" value="0"/>
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="summaryfield" {if $FIELD_MODEL->isSummaryField()} checked {/if}
																								   {if $FIELD_MODEL->isSummaryFieldOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} value="1" />&nbsp;
																							{vtranslate('LBL_SUMMARY_FIELD', $QUALIFIED_MODULE)}
																						</label>
																					</span>	
																					<span>
																						<input type="hidden" name="header_field" value="0"/>
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="header_field" {if $FIELD_MODEL->isHeaderField()} checked {/if}
																								   value="btn-default" />&nbsp;
																							{vtranslate('LBL_HEADER_FIELD', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="masseditable" value="2" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="masseditable" {if $FIELD_MODEL->isMassEditable()} checked {/if}
																								   {if $FIELD_MODEL->isMassEditOptionDisabled()} readonly="readonly" {/if} value="1" />&nbsp;
																							{vtranslate('LBL_MASS_EDIT', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="defaultvalue" value="" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="defaultvalue" {if $FIELD_MODEL->hasDefaultValue()} checked {/if}
																								   {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} value="" />&nbsp;
																							{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}
																						</label>
																						<div class="padding1per defaultValueUi {if !$FIELD_MODEL->hasDefaultValue()} zeroOpacity {/if}" style="padding : 0px 10px 0px 25px;">
																							{if $FIELD_MODEL->isDefaultValueOptionDisabled() neq "true"}
																								{if $FIELD_MODEL->getFieldDataType() eq "picklist"}
																									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
																									<select class="col-md-2" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}'>
																										{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
																											<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('defaultvalue')) eq $PICKLIST_NAME} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
																										{/foreach}
																									</select>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "multipicklist"}
																									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
																									{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('defaultvalue'))}
																									<select multiple class="col-md-2" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}'>
																										{foreach item=PICKLIST_VALUE from=$PICKLIST_VALUES}
																											<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
																										{/foreach}
																									</select>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "boolean"}
																									<input type="hidden" name="fieldDefaultValue" value="" />
																									<input type="checkbox" name="fieldDefaultValue" value="1"
																										   {if $FIELD_MODEL->get('defaultvalue') eq 1} checked {/if} data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' />
																								{elseif $FIELD_MODEL->getFieldDataType() eq "time"}
																									<div class="input-group time">
																										<input type="text" class="input-sm form-control" data-format="{$USER_MODEL->get('hour_format')}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-toregister="time" value="{$FIELD_MODEL->get('defaultvalue')}" name="fieldDefaultValue" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'/>
																										<span class="input-group-addon cursorPointer">
																											<span class="glyphicon glyphicon-time"></span>
																										</span>
																									</div>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "date"}
																									<div class="input-group date">
																										{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
																										<input type="text" class="form-control" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} name="fieldDefaultValue" data-toregister="date" data-date-format="{$USER_MODEL->get('date_format')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'
																											   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}" />
																										<span class="input-group-addon">
																											<span class="glyphicon glyphicon-calendar"></span>
																										</span>
																									</div>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "percentage"}
																									<div class="input-group">
																										<input type="number" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  class="form-control" name="fieldDefaultValue"
																											   value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' step="any" />
																										<span class="input-group-addon">%</span>
																									</div>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "currency"}
																									<div class="input-group">
																										<span class="input-group-addon">{$USER_MODEL->get('currency_symbol')}</span>
																										<input type="text" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  class="form-control" name="fieldDefaultValue"
																											   data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}"
																											   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
																									</div>
																								{else if $FIELD_MODEL->getFieldName() eq "terms_conditions" && $FIELD_MODEL->get('uitype') == 19}
																									{assign var=INVENTORY_TERMS_AND_CONDITIONS_MODEL value= Settings_Vtiger_MenuItem_Model::getInstance("LBL_TERMS_AND_CONDITIONS")}
																									<a href="{$INVENTORY_TERMS_AND_CONDITIONS_MODEL->getUrl()}" target="_blank">{vtranslate('LBL_CLICK_HERE_TO_EDIT', $QUALIFIED_MODULE)}</a>
																								{else if $FIELD_MODEL->get('uitype') eq 19}
																									<textarea class="input-medium" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'></textarea>
																								{else}
																									<input type="text" class="input-medium" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'/>
																								{/if}
																							{/if}
																						</div>
																					</span>
																					{if in_array($FIELD_MODEL->getFieldDataType(),['string','phone','currency','url','integer','double'])}
																						<div class="padding1per" style="padding : 0px 10px 0px 25px;">
																							{vtranslate('LBL_FIELD_MASK', $QUALIFIED_MODULE)}&nbsp;
																							<div class="input-group">
																								<input type="text" class="form-control" name="fieldMask" value="{$FIELD_MODEL->get('fieldparams')}" />
																								<span class="input-group-addon"><span class="glyphicon glyphicon-info-sign popoverTooltip" data-placement="top" data-content="{vtranslate('LBL_FIELD_MASK_INFO', $QUALIFIED_MODULE)}"></span></span>
																							</div>
																						</div>
																					{/if}
																					{if AppConfig::developer('CHANGE_VISIBILITY')}
																						<hr />
																						<span>
																							<label class="checkbox" style="padding-left: 5px;">
																								{vtranslate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}
																								{assign var=DISPLAY_TYPE value=Vtiger_Field_Model::showDisplayTypeList()}
																							</label>
																							<div class="padding1per defaultValueUi" style="padding : 0px 10px 0px 25px;">
																								<select name="displaytype" class="form-control">
																									{foreach key=DISPLAY_TYPE_KEY item=DISPLAY_TYPE_VALUE from=$DISPLAY_TYPE}
																										<option value="{$DISPLAY_TYPE_KEY}" {if $DISPLAY_TYPE_KEY == $FIELD_MODEL->get('displaytype')} selected {/if} >{vtranslate($DISPLAY_TYPE_VALUE, $QUALIFIED_MODULE)}</option>
																									{/foreach}
																								</select>
																							</div>
																						</span>
																					{/if}
																					{if AppConfig::developer('CHANGE_GENERATEDTYPE')}
																						<span>
																							<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																								&nbsp;{vtranslate('LBL_GENERATED_TYPE', $QUALIFIED_MODULE)}
																								<input style="margin-left: -89px;" type="checkbox" name="generatedtype" value="1"
																									   {if $FIELD_MODEL->get('generatedtype') eq 1} checked {/if} />
																							</label>
																						</span>
																					{/if}
																					<div class="padding1per" style="padding : 0px 10px 0px 25px;">
																						{vtranslate('LBL_MAX_LENGTH_TEXT', $QUALIFIED_MODULE)}
																						<div class="input-group">
																							<input type="text" class="form-control" name="maxlengthtext" value="{$FIELD_MODEL->get('maxlengthtext')}" />&nbsp;
																						</div>
																					</div>
																					<div class="padding1per" style="padding : 0px 10px 0px 25px;">
																						{vtranslate('LBL_MAX_WIDTH_COLUMN', $QUALIFIED_MODULE)}
																						<div class="form-inline">
																							<input type="text" class="form-control" name="maxwidthcolumn" value="{$FIELD_MODEL->get('maxwidthcolumn')}" />&nbsp;
																						</div>
																					</div>
																				</div>
																				<div class="modal-footer" style="padding: 0px;">
																					<span class="pull-right">
																						<div class="pull-right"><a href="javascript:void(0)" style="margin: 5px;" class='cancel btn btn-warning'>{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a></div>
																						<button class="btn btn-success saveFieldDetails" data-field-id="{$FIELD_MODEL->get('id')}" type="submit" style="margin: 5px;">
																							<strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
																						</button>
																					</span>
																				</div>
																			</form>
																		</div>
																	{/if}
																	{if $FIELD_MODEL->isCustomField() eq 'true'}
																		<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$FIELD_MODEL->get('id')}">
																			<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
																		</a>
																	{/if}
																</span>
															</div>
														</div>
													</div>
												</li>
											{/if}
										{/foreach}
									</ul>
									<ul {if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}name="sortable2"{/if} class="connectedSortable sortTableUl col-md-6">
										{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist1}
											{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
											{if $smarty.foreach.fieldlist1.index % 2 neq 0}
												<li>
													<div class="opacity editFields marginLeftZero border1px" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
														<div class="row padding1per">
															{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
															<div class="col-xs-2 col-sm-2">&nbsp;
																{if $FIELD_MODEL->isEditable()}
																	<a>
																		<img src="{vimage_path('drag.png')}" border="0" alt="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																	</a>
																{/if}
															</div>
															<div class="col-xs-10 col-sm-10 marginLeftZero fieldContainer" style="word-wrap: break-word;">
																<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}&nbsp;[{$FIELD_MODEL->get('name')}]
																	{if $IS_MANDATORY}<span class="redColor">*</span>{/if}
																</span>
																<span class="btn-group pull-right actions">
																	<a href="javascript:void(0)" class="copyFieldLabel pull-right" data-target="relatedFieldValue{$FIELD_MODEL->get('id')}">
																		<span class="glyphicon glyphicon-copy alignMiddle" title="{vtranslate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
																	</a>
																	<input type="hidden" value="{$FIELD_MODEL->get('name')}" id="relatedFieldValue{$FIELD_MODEL->get('id')}" />
																	{if $FIELD_MODEL->isEditable()}
																		<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
																			<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
																		</a>
																		<div class="basicFieldOperations pull-right hide" style="width : 250px;">
																			<form class="form-horizontal fieldDetailsForm" method="POST">
																				<div class="modal-header contentsBackground">
																					<strong>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</strong>
																					<div class="pull-right"><a href="javascript:void(0)" class="cancel">X</a></div>
																				</div>
																				<div style="padding-bottom: 5px;">
																					<span>
																						<input type="hidden" name="mandatory" value="O" /><label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="mandatory" {if $IS_MANDATORY} checked {/if}
																								   {if $FIELD_MODEL->isMandatoryOptionDisabled()} readonly="readonly" {/if} value="M" />&nbsp;
																							{vtranslate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="presence" value="1" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="presence" {if $FIELD_MODEL->isViewable()} checked {/if}
																								   {if $FIELD_MODEL->isActiveOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if} value="{$FIELD_MODEL->get('presence')}" />&nbsp;
																							{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="quickcreate" value="1" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="quickcreate" {if $FIELD_MODEL->isQuickCreateEnabled()} checked {/if}
																								   {if $FIELD_MODEL->isQuickCreateOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if} value="2" />&nbsp;
																							{vtranslate('LBL_QUICK_CREATE', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="summaryfield" value="0"/>
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="summaryfield" {if $FIELD_MODEL->isSummaryField()} checked {/if}
																								   {if $FIELD_MODEL->isSummaryFieldOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} value="1" />&nbsp;
																							{vtranslate('LBL_SUMMARY_FIELD', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="header_field" value="0"/>
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="header_field" {if $FIELD_MODEL->isHeaderField()} checked {/if}
																								   value="btn-default" />&nbsp;
																							{vtranslate('LBL_HEADER_FIELD', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="masseditable" value="2" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="masseditable" {if $FIELD_MODEL->isMassEditable()} checked {/if}
																								   {if $FIELD_MODEL->isMassEditOptionDisabled()} readonly="readonly" {/if} value="1" />&nbsp;
																							{vtranslate('LBL_MASS_EDIT', $QUALIFIED_MODULE)}
																						</label>
																					</span>
																					<span>
																						<input type="hidden" name="defaultvalue" value="" />
																						<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																							<input type="checkbox" name="defaultvalue" {if $FIELD_MODEL->hasDefaultValue()} checked {/if}
																								   {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} value="" />&nbsp;
																							{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}
																						</label>
																						<div class="padding1per defaultValueUi {if !$FIELD_MODEL->hasDefaultValue()} zeroOpacity {/if}" style="padding : 0px 10px 0px 25px;">
																							{if $FIELD_MODEL->isDefaultValueOptionDisabled() neq "true"}
																								{if $FIELD_MODEL->getFieldDataType() eq "picklist"}
																									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
																									<select class="col-md-2" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}'>
																										{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
																											<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('defaultvalue')) eq $PICKLIST_NAME} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
																										{/foreach}
																									</select>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "multipicklist"}
																									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
																									{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('defaultvalue'))}
																									<select multiple class="col-md-2" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}'>
																										{foreach item=PICKLIST_VALUE from=$PICKLIST_VALUES}
																											<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
																										{/foreach}
																									</select>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "boolean"}
																									<input type="hidden" name="fieldDefaultValue" value="" />
																									<input type="checkbox" name="fieldDefaultValue" value="1"
																										   {if $FIELD_MODEL->get('defaultvalue') eq 1} checked {/if} data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' />
																								{elseif $FIELD_MODEL->getFieldDataType() eq "time"}
																									<div class="input-group time">
																										<input type="text" class="input-sm form-control" data-format="{$USER_MODEL->get('hour_format')}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-toregister="time" value="{$FIELD_MODEL->get('defaultvalue')}" name="fieldDefaultValue" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'/>
																										<span class="input-group-addon cursorPointer">
																											<span class="glyphicon glyphicon-time"></span>
																										</span>
																									</div>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "date"}
																									<div class="input-group date">
																										{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
																										<input type="text" class="form-control" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} name="fieldDefaultValue" data-toregister="date" data-date-format="{$USER_MODEL->get('date_format')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'
																											   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}" />
																										<span class="input-group-addon">
																											<span class="glyphicon glyphicon-calendar"></span>
																										</span>
																									</div>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "percentage"}
																									<div class="input-group">
																										<input type="number" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  class="form-control" name="fieldDefaultValue"
																											   value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' step="any" />
																										<span class="input-group-addon">%</span>
																									</div>
																								{elseif $FIELD_MODEL->getFieldDataType() eq "currency"}
																									<div class="input-group">
																										<span class="input-group-addon">{$USER_MODEL->get('currency_symbol')}</span>
																										<input type="text" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  class="form-control" name="fieldDefaultValue"
																											   data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}"
																											   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
																									</div>
																								{else}
																									<input type="text" class="input-medium" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'/>
																								{/if}
																							{/if}
																						</div>
																					</span>
																					{if in_array($FIELD_MODEL->getFieldDataType(),['string','phone','currency','url','integer','double'])}
																						<div class="padding1per defaultValueUi" style="padding : 0px 10px 0px 25px;">
																							{vtranslate('LBL_FIELD_MASK', $QUALIFIED_MODULE)}&nbsp;
																							<div class="input-group">
																								<input type="text" class="form-control" name="fieldMask" value="{$FIELD_MODEL->get('fieldparams')}" />
																								<span class="input-group-addon"><span class="glyphicon glyphicon-info-sign popoverTooltip" data-placement="top" data-content="{vtranslate('LBL_FIELD_MASK_INFO', $QUALIFIED_MODULE)}"></span></span>
																							</div>
																						</div>
																					{/if}
																					{if AppConfig::developer('CHANGE_VISIBILITY')}
																						<hr />
																						<span>
																							<label class="checkbox" style="padding-left: 5px;">
																								{vtranslate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}
																								{assign var=DISPLAY_TYPE value=Vtiger_Field_Model::showDisplayTypeList()}
																							</label>
																							<div class="padding1per defaultValueUi" style="padding : 0px 10px 0px 25px;">
																								<select name="displaytype" class="form-control">
																									{foreach key=DISPLAY_TYPE_KEY item=DISPLAY_TYPE_VALUE from=$DISPLAY_TYPE}
																										<option value="{$DISPLAY_TYPE_KEY}" {if $DISPLAY_TYPE_KEY == $FIELD_MODEL->get('displaytype')} selected {/if} >{vtranslate($DISPLAY_TYPE_VALUE, $QUALIFIED_MODULE)}</option>
																									{/foreach}
																								</select>
																							</div>
																						</span>
																					{/if}
																					{if AppConfig::developer('CHANGE_GENERATEDTYPE')}
																						<span>
																							<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																								&nbsp;{vtranslate('LBL_GENERATED_TYPE', $QUALIFIED_MODULE)}
																								<input style="margin-left: -89px;" type="checkbox" name="generatedtype" value="1"
																									   {if $FIELD_MODEL->get('generatedtype') eq 1} checked {/if} />
																							</label>
																						</span>
																					{/if}
																				</div>
																				<div class="padding1per" style="padding : 0px 10px 0px 25px;">
																					{vtranslate('LBL_MAX_LENGTH_TEXT', $QUALIFIED_MODULE)}
																					<div class="input-group">
																						<input type="text" class="form-control" name="maxlengthtext" value="{$FIELD_MODEL->get('maxlengthtext')}" />&nbsp;
																					</div>
																				</div>
																				<div class="padding1per" style="padding : 0px 10px 0px 25px;">
																					{vtranslate('LBL_MAX_WIDTH_COLUMN', $QUALIFIED_MODULE)}
																					<div class="form-inline">
																						<input type="text" class="form-control" name="maxwidthcolumn" value="{$FIELD_MODEL->get('maxwidthcolumn')}" />&nbsp;
																					</div>
																				</div>
																				<div class="modal-footer" style="padding: 0px;">
																					<span class="pull-right">
																						<div class="pull-right"><a href="javascript:void(0)" class="cancel btn btn-warning" style="margin: 5px;">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a></div>
																						<button class="btn btn-success saveFieldDetails" data-field-id="{$FIELD_MODEL->get('id')}" type="submit" style="margin: 5px;">
																							<strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
																						</button>
																					</span>
																				</div>
																			</form>
																		</div>
																	{/if}
																	{if $FIELD_MODEL->isCustomField() eq 'true'}
																		<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$FIELD_MODEL->get('id')}">
																			<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
																		</a>
																	{/if}
																</span>
															</div>
														</div>
													</div>
												</li>
											{/if}
										{/foreach}
									</ul>
								</div>
							</div>
						{/foreach}
					</div>
					<input type="hidden" class="inActiveFieldsArray" value='{\App\Json::encode($IN_ACTIVE_FIELDS)}' />

					<div class="newCustomBlockCopy hide marginBottom10px border1px {if $IS_BLOCK_SORTABLE}blockSortable {/if}" data-block-id="" data-sequence="" style="border-radius: 4px; background: white;">
						<div class="row layoutBlockHeader no-margin">
							<div class="col-md-6 blockLabel padding10">
								<img class="alignMiddle" src="{vimage_path('drag.png')}" alt="" />&nbsp;&nbsp;
							</div>
							<div class="col-md-6 marginLeftZero">
								<div class="pull-right btn-toolbar blockActions" style="margin: 4px;">
									<div class="btn-group">
										<button class="btn btn-success addCustomField hide" type="button">
											<strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
									<div class="btn-group">
										<button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
											<strong>{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</strong>&nbsp;&nbsp;
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu pull-right">
											<li class="blockVisibility" data-visible="1" data-block-id="">
												<a href="javascript:void(0)">
													<span class="glyphicon glyphicon-ok"></span>&nbsp;{vtranslate('LBL_ALWAYS_SHOW', $QUALIFIED_MODULE)}
												</a>
											</li>
											<li class="inActiveFields">
												<a href="javascript:void(0)">{vtranslate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</a>
											</li>
											<li class="deleteCustomBlock">
												<a href="javascript:void(0)">{vtranslate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</a>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="blockFieldsList row blockFieldsSortable no-margin" style="padding:5px;min-height: 27px">
							<ul class="connectedSortable col-md-6 ui-sortable" style="list-style-type: none; float: left;min-height:1px;padding:2px;" name="sortable1"></ul>
							<ul class="connectedSortable col-md-6 ui-sortable" style="list-style-type: none; margin: 0;float: left;min-height:1px;padding:2px;" name="sortable2"></ul>
						</div>
					</div>

					<li class="newCustomFieldCopy hide">
						<div class="marginLeftZero border1px" data-field-id="" data-sequence="">
							<div class="row padding1per">
								<span class="col-md-2">&nbsp;
									{if $IS_SORTABLE}
										<a>
											<img src="{vimage_path('drag.png')}" border="0" alt="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
										</a>
									{/if}
								</span>
								<div class="col-md-10 marginLeftZero fieldContainer" style="word-wrap: break-word;">
									<span class="fieldLabel"></span>
									<input type="hidden" value="" id="relatedFieldValue" />
									<span class="btn-group pull-right actions">
										<a href="javascript:void(0)" class="copyFieldLabel pull-right" data-target="relatedFieldValue">
											<span class="glyphicon glyphicon-copy alignMiddle" title="{vtranslate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
										</a>
										{if $IS_SORTABLE}
											<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
												<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
											</a>
											<div class="basicFieldOperations hide pull-right" style="width: 250px;">
												<form class="form-horizontal fieldDetailsForm" method="POST">
													<div class="modal-header contentsBackground">
													</div>
													<div style="padding-bottom: 5px;">
														<span>
															<input type="hidden" name="mandatory" value="O" />
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="mandatory" value="M" />&nbsp;{vtranslate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
															</label>
														</span>
														<span>
															<input type="hidden" name="presence" value="1" />
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="presence" value="2" />&nbsp;{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE)}
															</label>
														</span>
														<span>
															<input type="hidden" name="quickcreate" value="1" />
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="quickcreate" value="2" />&nbsp;{vtranslate('LBL_QUICK_CREATE', $QUALIFIED_MODULE)}
															</label>
														</span>
														<span>
															<input type="hidden" name="summaryfield" value="0"/>
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="summaryfield" value="1" />&nbsp;{vtranslate('LBL_SUMMARY_FIELD', $QUALIFIED_MODULE)}
															</label>
														</span>
														<span>
															<input type="hidden" name="header_field" value="0" />
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="header_field" value="btn-default" />&nbsp;{vtranslate('LBL_HEADER_FIELD', $QUALIFIED_MODULE)}
															</label>
														</span>
														<span>
															<input type="hidden" name="masseditable" value="2" />
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="masseditable" value="1" />&nbsp;{vtranslate('LBL_MASS_EDIT', $QUALIFIED_MODULE)}
															</label>
														</span>
														<span>
															<input type="hidden" name="defaultvalue" value="" />
															<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																<input type="checkbox" name="defaultvalue" value="" />&nbsp;
																{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}</label>
															<div class="padding1per defaultValueUi" style="padding : 0px 10px 0px 25px;"></div>
														</span>
														<div class="padding1per maskField" style="padding : 0px 10px 0px 25px;">
															{vtranslate('LBL_FIELD_MASK', $QUALIFIED_MODULE)}&nbsp;
															<div class="input-group">
																<input type="text" class="form-control" name="fieldMask" value="" />
																<span class="input-group-addon"><span class="glyphicon glyphicon-info-sign popoverTooltip" data-placement="top" data-content="{vtranslate('LBL_FIELD_MASK_INFO', $QUALIFIED_MODULE)}"></span></span>
															</div>
														</div>
														{if AppConfig::developer('CHANGE_VISIBILITY')}
															<hr />
															<span>
																<label class="checkbox" style="padding-left: 5px;">
																	{vtranslate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}
																	<select name="displaytype" class="form-control">
																		{foreach key=DISPLAY_TYPE_KEY item=DISPLAY_TYPE_VALUE from=$DISPLAY_TYPE_LIST}
																			<option value="{$DISPLAY_TYPE_KEY}" {if $DISPLAY_TYPE_KEY == '1'} selected {/if}>{vtranslate($DISPLAY_TYPE_VALUE, $QUALIFIED_MODULE)}</option>
																		{/foreach}
																	</select>
																</label>
															</span>
														{/if}
														{if AppConfig::developer('CHANGE_GENERATEDTYPE')}
															<span>
																<label class="checkbox" style="padding-left: 25px; padding-top: 5px;">
																	&nbsp;{vtranslate('LBL_GENERATED_TYPE', $QUALIFIED_MODULE)}
																	<input style="margin-left: -89px;" type="checkbox" name="generatedtype" value="1" />
																</label>
															</span>
														{/if}
														<div class="padding1per" style="padding : 0px 10px 0px 25px;">
															{vtranslate('LBL_MAX_LENGTH_TEXT', $QUALIFIED_MODULE)}
															<div class="input-group">
																<input type="text" class="form-control" name="maxlengthtext" value="{$FIELD_MODEL->get('maxlengthtext')}" />&nbsp;
															</div>
														</div>
														<div class="padding1per" style="padding : 0px 10px 0px 25px;">
															{vtranslate('LBL_MAX_WIDTH_COLUMN', $QUALIFIED_MODULE)}
															<div class="form-inline">
																<input type="text" class="form-control" name="maxwidthcolumn" value="{$FIELD_MODEL->get('maxwidthcolumn')}" />&nbsp;
															</div>
														</div>
													</div>
													<div class="modal-footer" style="padding: 0px;">
														<span class="pull-right">
															<div class="pull-right"><a href="javascript:void(0)" style="margin: 5px;" class='cancel btn btn-warning'>{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a></div>
															<button class="btn btn-success saveFieldDetails" style="margin: 5px;" data-field-id="" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
														</span>
													</div>
												</form>
											</div>
										{/if}
										<a href="javascript:void(0)" class="deleteCustomField" data-field-id=""><span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span></a>
									</span>
								</div>
							</div>
						</div>
					</li>

					<div class="modal addBlockModal fade">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header contentsBackground">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h3>{vtranslate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</h3>
								</div>
								<form class="form-horizontal addCustomBlockForm">
									<div class="modal-body">
										<div class="form-group">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>
												<span>{vtranslate('LBL_BLOCK_NAME', $QUALIFIED_MODULE)}</span>
											</div>
											<div class="col-md-8 controls">
												<input type="text" name="label" class="form-control" data-validation-engine="validate[required]" />
											</div>
										</div>
										<div class="form-group">
											<div class="col-md-3 control-label">
												{vtranslate('LBL_ADD_AFTER', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="form-control" name="beforeBlockId">
													{foreach key=BLOCK_ID item=BLOCK_LABEL from=$ALL_BLOCK_LABELS}
														<option value="{$BLOCK_ID}" data-label="{$BLOCK_LABEL}">{vtranslate($BLOCK_LABEL, $SELECTED_MODULE_NAME)}</option>
													{/foreach}
												</select>
											</div>
										</div>
									</div>
									{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
								</form>
							</div>
						</div>
					</div>

					<div class="modal createFieldModal fade" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h3 class="modal-title">{vtranslate('LBL_CREATE_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h3>
								</div>
								<form class="form-horizontal createCustomFieldForm"  method="POST">
									<div class="modal-body">
										<div class="form-group">
											<div class="col-md-3 control-label">
												{vtranslate('LBL_SELECT_FIELD_TYPE', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="fieldTypesList form-control" name="fieldType">
													{foreach item=FIELD_TYPE from=$ADD_SUPPORTED_FIELD_TYPES}
														<option value="{$FIELD_TYPE}"
																{foreach key=TYPE_INFO item=TYPE_INFO_VALUE from=$FIELD_TYPE_INFO[$FIELD_TYPE]}
																	data-{$TYPE_INFO}="{$TYPE_INFO_VALUE}"
																{/foreach}>
															{vtranslate($FIELD_TYPE, $QUALIFIED_MODULE)}
														</option>
													{/foreach}
												</select>
											</div>
										</div>
										<div class="form-group">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<input type="text" maxlength="50" name="fieldLabel" value="" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" class="form-control"
													   data-validator={\App\Json::encode([['name'=>'FieldLabel']])} />
											</div>
										</div>
										<div class="form-group">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_FIELD_NAME', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<input type="text" maxlength="30" name="fieldName" value="" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" class="form-control"
													   data-validator={\App\Json::encode([['name'=>'fieldName']])} />
											</div>
										</div>
										<div class="form-group">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="marginLeftZero form-control" name="fieldTypeList">
													<option value="0">{vtranslate('LBL_FIELD_TYPE0', $QUALIFIED_MODULE)}</option>
													<option value="1">{vtranslate('LBL_FIELD_TYPE1', $QUALIFIED_MODULE)}</option>
												</select>
											</div>
										</div>
										<div class="form-group supportedType lengthsupported">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_LENGTH', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<input type="text" name="fieldLength" value="" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" class="form-control"/>
											</div>
										</div>
										<div class="form-group supportedType decimalsupported hide">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_DECIMALS', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<input type="text" name="decimal" value="" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" class="form-control"/>
											</div>
										</div>
										<div class="form-group supportedType preDefinedValueExists hide">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_PICKLIST_VALUES', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select id="picklistUi" class="form-control" name="pickListValues" multiple="" tabindex="-1" aria-hidden="true" placeholder="{vtranslate('LBL_ENTER_PICKLIST_VALUES', $QUALIFIED_MODULE)}" 
														data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator={\App\Json::encode([['name'=>'PicklistFieldValues']])}>
												</select>
											</div>
										</div>
										<div class="form-group supportedType preDefinedModuleList hide">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_RELATION_VALUES', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select {if $FIELD_TYPE_INFO['Related1M']['ModuleListMultiple'] eq true}multiple{/if} class="referenceModule form-control" name="referenceModule">
													{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
														<option value="{$MODULE_NAME}">{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
													{/foreach}
												</select>
											</div>
										</div>
										<div class="form-group supportedType preMultiReferenceValue hide">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_MULTI_REFERENCE_VALUE_MODULES', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="MRVModule form-control" name="MRVModule">
													{foreach item=RELATION from=$SELECTED_MODULE_MODEL->getRelations()}
														<option value="{$RELATION->get('modulename')}">{vtranslate($RELATION->get('label'), $RELATION->get('modulename'))}</option>
													{/foreach}
												</select>
											</div>
										</div>
										<div class="form-group supportedType preMultiReferenceValue hide">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_MULTI_REFERENCE_VALUE_FIELDS', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="MRVField form-control" name="MRVField">
													{foreach item=RELATION from=$SELECTED_MODULE_MODEL->getRelations()}
														{assign var=COUNT_FIELDS value=count($RELATION->getFields())}
														{foreach item=FIELD key=KEY from=$RELATION->getFields()}
															{if !isset($LAST_BLOCK) || $LAST_BLOCK->id != $FIELD->get('block')->id}
																<optgroup label="{vtranslate($FIELD->get('block')->label, $RELATION->get('modulename'))}" data-module="{$RELATION->get('modulename')}">
																{/if} 
																<option value="{$FIELD->getId()}" >{vtranslate($FIELD->get('label'), $RELATION->get('modulename'))}</option>
																{if $COUNT_FIELDS == ($KEY - 1)}
																</optgroup>
															{/if} 
															{assign var=LAST_BLOCK value=$FIELD->get('block')}
														{/foreach}
													{/foreach}
												</select>
											</div>
										</div>
										<div class="form-group supportedType preMultiReferenceValue hide">
											<div class="col-md-3 control-label">
												{vtranslate('LBL_MULTI_REFERENCE_VALUE_FILTER_FIELD', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="filterField form-control" name="MRVFilterField">
													{foreach item=RELATION from=$SELECTED_MODULE_MODEL->getRelations()}
														<option value="-" data-module="{$RELATION->get('modulename')}">{vtranslate('--None--')}</option>
														{foreach item=FIELD key=KEY from=$RELATION->getFields('picklist')}
															<option value="{$FIELD->getName()}" data-module="{$RELATION->get('modulename')}">{vtranslate($FIELD->get('label'), $RELATION->get('modulename'))}</option>
														{/foreach}
													{/foreach}
												</select>
											</div>
										</div>
										<div class="form-group supportedType preMultiReferenceValue hide">
											<div class="col-md-3 control-label">
												{vtranslate('LBL_MULTI_REFERENCE_VALUE_FILTER_VALUE', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="MRVModule form-control" name="MRVFilterValue">
												</select>
											</div>
										</div>
										<div class="form-group supportedType picklistOption hide">
											<div class="col-md-3 control-label">
												&nbsp;
											</div>
											<div class="col-md-8 controls">
												<label class="checkbox">
													<input type="checkbox" class="checkbox" name="isRoleBasedPickList" value="1" >&nbsp;{vtranslate('LBL_ROLE_BASED_PICKLIST',$QUALIFIED_MODULE)}
												</label>
											</div>
										</div>
										<div class="form-group supportedType preDefinedTreeList hide">
											<div class="col-md-3 control-label">
												<span class="redColor">*</span>&nbsp;
												{vtranslate('LBL_TREE_TEMPLATE', $QUALIFIED_MODULE)}
											</div>
											<div class="col-md-8 controls">
												<select class="TreeList form-control" name="tree">
													{foreach key=key item=item from=$SELECTED_MODULE_MODEL->getTreeTemplates($SELECTED_MODULE_NAME)}
														<option value="{$key}">{vtranslate($item, $SELECTED_MODULE_NAME)}</option>
													{foreachelse}
														<option value="-">{vtranslate('LBL_NONE')}</option>
													{/foreach}
												</select>
											</div>
										</div>
									</div>
									{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
								</form>
							</div>
						</div>
					</div>


					<div class="modal inactiveFieldsModal fade" tabindex="-1">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h3 class="modal-title">{vtranslate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</h3>
								</div>
								<form class="form-horizontal inactiveFieldsForm" method="POST">
									<div class="modal-body">
										<div class="row inActiveList"></div>
									</div>
									<div class="modal-footer">
										<div class=" pull-right cancelLinkContainer">
											<a class="cancelLink btn btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
										</div>
										<button class="btn btn-success" type="submit" name="reactivateButton">
											<strong>{vtranslate('LBL_REACTIVATE', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				{if $IS_INVENTORY}
					<div class="tab-pane" id="inventoryViewLayout">
						{include file='Inventory.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
					</div>	
				{/if}
			</div>
		</div>
	</div>
{/strip}
