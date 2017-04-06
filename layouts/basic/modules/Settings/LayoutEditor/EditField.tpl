{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 class="modal-title">{App\Language::translate('LBL_EDIT_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body row">
		<div class="col-md-12">
			<form class="form-horizontal fieldDetailsForm sendByAjax validateForm" method="POST">
				<input type="hidden" name="module" value="LayoutEditor">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="action" value="Field">
				<input type="hidden" name="mode" value="save">
				<input type="hidden" name="fieldid" value="{$FIELD_MODEL->getId()}">
				<input type="hidden" name="sourceModule" value="{$SELECTED_MODULE_NAME}">
				{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
				{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
				<strong>{App\Language::translate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:&nbsp;</strong>{App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}<br>
				<strong>{App\Language::translate('LBL_FIELD_NAME', $QUALIFIED_MODULE)}:&nbsp;</strong>{$FIELD_MODEL->getFieldName()}
				<hr class="marginTop10">
				<div class="checkbox">
					<input type="hidden" name="mandatory" value="O" />
					<label>
						<input type="checkbox" name="mandatory" {if $IS_MANDATORY} checked {/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()} readonly="readonly" {/if} value="M" />&nbsp;
						{App\Language::translate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
					</label>
				</div>
				<div class="checkbox">
					<input type="hidden" name="presence" value="1" />
					<label>
						<input type="checkbox" name="presence" {if $FIELD_MODEL->isViewable()} checked {/if} {strip} {/strip}
							   {if $FIELD_MODEL->isActiveOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if} value="{$FIELD_MODEL->get('presence')}" />&nbsp;
						{App\Language::translate('LBL_ACTIVE', $QUALIFIED_MODULE)}
					</label>
				</div>

				<div class="checkbox">
					<input type="hidden" name="quickcreate" value="1" />
					<label>
						<input type="checkbox" name="quickcreate" {if $FIELD_MODEL->isQuickCreateEnabled()} checked {/if}{strip} {/strip}
							   {if $FIELD_MODEL->isQuickCreateOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if} value="2" />&nbsp;
						{App\Language::translate('LBL_QUICK_CREATE', $QUALIFIED_MODULE)}
					</label>
				</div>
				<div class="checkbox">
					<input type="hidden" name="summaryfield" value="0"/>
					<label>
						<input type="checkbox" name="summaryfield" {if $FIELD_MODEL->isSummaryField()} checked {/if}{strip} {/strip}
							   {if $FIELD_MODEL->isSummaryFieldOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} value="1" />&nbsp;
						{App\Language::translate('LBL_SUMMARY_FIELD', $QUALIFIED_MODULE)}
					</label>
				</div>
				<div class="checkbox">
					<input type="hidden" name="header_field" value="0"/>
					<label>
						<input type="checkbox" name="header_field" {if $FIELD_MODEL->isHeaderField()} checked {/if} value="btn-default" />&nbsp;
						{App\Language::translate('LBL_HEADER_FIELD', $QUALIFIED_MODULE)}
					</label>
				</div>
				<div class="checkbox">
					<input type="hidden" name="masseditable" value="2" />
					<label>
						<input type="checkbox" name="masseditable" {if $FIELD_MODEL->isMassEditable()} checked {/if} {strip} {/strip}
							   {if $FIELD_MODEL->isMassEditOptionDisabled()} readonly="readonly" {/if} value="1" />&nbsp;
						{App\Language::translate('LBL_MASS_EDIT', $QUALIFIED_MODULE)}
					</label>
				</div>

				<div class="checkbox">
					<input type="hidden" name="defaultvalue" value="" />
					<label>
						<input type="checkbox" name="defaultvalue" {if $FIELD_MODEL->hasDefaultValue()} checked {/if} {strip} {/strip}
							   {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} value="" />&nbsp;
						{App\Language::translate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}
					</label>
					<div class="marginLeft20 defaultValueUi {if !$FIELD_MODEL->hasDefaultValue()} zeroOpacity {/if}">
						{if $FIELD_MODEL->isDefaultValueOptionDisabled() neq "true"}
							{if $FIELD_MODEL->getFieldDataType() eq "picklist"}
								{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
								<select class="col-md-2 select2" name="fieldDefaultValue" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}'>
									{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
										<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if decode_html($FIELD_MODEL->get('defaultvalue')) eq $PICKLIST_NAME} selected {/if}>{App\Language::translate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
									{/foreach}
								</select>
							{elseif $FIELD_MODEL->getFieldDataType() eq "multipicklist"}
								{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
								{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('defaultvalue'))}
								<select multiple class="col-md-2 select2" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}'>
									{foreach item=PICKLIST_VALUE from=$PICKLIST_VALUES}
										<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE), $FIELD_VALUE_LIST)} selected {/if}>{App\Language::translate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
									{/foreach}
								</select>
							{elseif $FIELD_MODEL->getFieldDataType() eq "boolean"}
								<div class="checkbox">
									<input type="hidden" name="fieldDefaultValue" value="" />
									<label>
										<input type="checkbox" name="fieldDefaultValue" value="1"{strip} {/strip}
											   {if $FIELD_MODEL->get('defaultvalue') eq 1} checked {/if} data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' />
									</label>
								</div>
							{elseif $FIELD_MODEL->getFieldDataType() eq "time"}
								<div class="input-group time">
									<input type="text" class="input-sm form-control clockPicker" data-format="{$USER_MODEL->get('hour_format')}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} data-toregister="time" value="{$FIELD_MODEL->get('defaultvalue')}" name="fieldDefaultValue" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'/>
									<span class="input-group-addon cursorPointer">
										<span class="glyphicon glyphicon-time"></span>
									</span>
								</div>
							{elseif $FIELD_MODEL->getFieldDataType() eq "date"}
								<div class="input-group date">
									{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
									<input type="text" class="form-control dateField" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if} name="fieldDefaultValue" data-toregister="date" data-date-format="{$USER_MODEL->get('date_format')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'{strip} {/strip}
										   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}" />
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							{elseif $FIELD_MODEL->getFieldDataType() eq "percentage"}
								<div class="input-group">
									<input type="number" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  class="form-control" name="fieldDefaultValue"{strip} {/strip}
										   value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' step="any" />
									<span class="input-group-addon">%</span>
								</div>
							{elseif $FIELD_MODEL->getFieldDataType() eq "currency"}
								<div class="input-group">
									<span class="input-group-addon">{$USER_MODEL->get('currency_symbol')}</span>
									<input type="text" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  class="form-control" name="fieldDefaultValue"{strip} {/strip}
										   data-fieldinfo='{\App\Json::encode($FIELD_INFO)}' value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('defaultvalue'))}"
										   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
								</div>
							{else if $FIELD_MODEL->get('uitype') eq 19}
								<textarea class="input-medium" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'></textarea>
							{else}
								<input type="text" class="input-medium form-control" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !$FIELD_MODEL->hasDefaultValue()} disabled="" {/if}  name="fieldDefaultValue" value="{$FIELD_MODEL->get('defaultvalue')}" data-fieldinfo='{\App\Json::encode($FIELD_INFO)}'/>
							{/if}
						{/if}
					</div>
				</div>
				{if in_array($FIELD_MODEL->getFieldDataType(),['string','phone','currency','url','integer','double'])}
					<div>
						<strong>{App\Language::translate('LBL_FIELD_MASK', $QUALIFIED_MODULE)}</strong>&nbsp;
						<div class="marginLeft20 input-group marginBottom10px">
							<input type="text" class="form-control" name="fieldMask" value="{$FIELD_MODEL->get('fieldparams')}" />
							<span class="input-group-addon"><span class="glyphicon glyphicon-info-sign popoverTooltip" data-placement="top" data-content="{App\Language::translate('LBL_FIELD_MASK_INFO', $QUALIFIED_MODULE)}"></span></span>
						</div>
					</div>
				{/if}
				<div>
					<strong>{App\Language::translate('LBL_MAX_LENGTH_TEXT', $QUALIFIED_MODULE)}</strong>
					<div class="marginLeft20">
						<input type="text" class="form-control" name="maxlengthtext" value="{$FIELD_MODEL->get('maxlengthtext')}" />&nbsp;
					</div>
				</div>
				<div>
					<strong>{App\Language::translate('LBL_MAX_WIDTH_COLUMN', $QUALIFIED_MODULE)}</strong>
					<div class="marginLeft20">
						<input type="text" class="form-control" name="maxwidthcolumn" value="{$FIELD_MODEL->get('maxwidthcolumn')}" />&nbsp;
					</div>
				</div>
				{if AppConfig::developer('CHANGE_GENERATEDTYPE')}
					<div class="checkbox">
						<label>
							<input type="checkbox" name="generatedtype" value="1" {if $FIELD_MODEL->get('generatedtype') eq 1} checked {/if} />&nbsp;
							{App\Language::translate('LBL_GENERATED_TYPE', $QUALIFIED_MODULE)}
						</label>
					</div>
				{/if}
				{if AppConfig::developer('CHANGE_VISIBILITY')}
					<label class="checkbox">
						{App\Language::translate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}
						{assign var=DISPLAY_TYPE value=Vtiger_Field_Model::showDisplayTypeList()}
					</label>
					<div class="marginLeft20 defaultValueUi">
						<select name="displaytype" class="form-control select2">
							{foreach key=DISPLAY_TYPE_KEY item=DISPLAY_TYPE_VALUE from=$DISPLAY_TYPE}
								<option value="{$DISPLAY_TYPE_KEY}" {if $DISPLAY_TYPE_KEY == $FIELD_MODEL->get('displaytype')} selected {/if} >{App\Language::translate($DISPLAY_TYPE_VALUE, $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				{/if}
				{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</form>
		</div>
	</div>
{/strip}