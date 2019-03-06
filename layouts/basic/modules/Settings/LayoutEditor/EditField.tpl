{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-LayoutEditor-EditField">
		<div class="modal-header">
			<h5 class="modal-title">{App\Language::translate('LBL_EDIT_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h5>
			<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body row">
			<div class="col-md-12">
				<form class="form-horizontal fieldDetailsForm sendByAjax validateForm" method="POST">
					<input type="hidden" name="module" value="LayoutEditor"/>
					<input type="hidden" name="parent" value="Settings"/>
					<input type="hidden" name="action" value="Field"/>
					<input type="hidden" name="mode" value="save"/>
					<input type="hidden" name="fieldid" value="{$FIELD_MODEL->getId()}"/>
					<input type="hidden" name="sourceModule" value="{$SELECTED_MODULE_NAME}"/>
					{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					<strong>{App\Language::translate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}
						: </strong>{App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}<br/>
					<strong>{App\Language::translate('LBL_FIELD_NAME', $QUALIFIED_MODULE)}
						: </strong>{$FIELD_MODEL->getFieldName()}
					<hr class="marginTop10">
					<div class="checkbox">
						<input type="hidden" name="mandatory" value="O"/>
						<input type="checkbox"
							   name="mandatory"
							   id="mandatory" {if $IS_MANDATORY} checked {/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()} readonly="readonly" {/if}
							   value="M"/>
						<label for="mandatory" class="ml-1">
							{App\Language::translate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
						</label>
					</div>
					<div class="checkbox">
						<input type="hidden" name="presence" value="1"/>
						<input type="checkbox"
							   name="presence"
							   id="presence" {if $FIELD_MODEL->isActiveField()} checked {/if} {strip} {/strip}
								{if $FIELD_MODEL->isActiveOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if}
							   value="{$FIELD_MODEL->get('presence')}"/>
						<label for="presence">
							{App\Language::translate('LBL_ACTIVE', $QUALIFIED_MODULE)}
						</label>
					</div>

					<div class="checkbox">
						<input type="hidden" name="quickcreate" value="1"/>
						<input type="checkbox"
							   name="quickcreate"
							   id="quickcreate" {if $FIELD_MODEL->isQuickCreateEnabled()} checked {/if}{strip} {/strip}
								{if $FIELD_MODEL->isQuickCreateOptionDisabled()} readonly="readonly" class="optionDisabled"{/if} {if $IS_MANDATORY} readonly="readonly" {/if}
							   value="2"/>
						<label for="quickcreate">
							{App\Language::translate('LBL_QUICK_CREATE', $QUALIFIED_MODULE)}
						</label>
					</div>
					<div class="checkbox">
						<input type="hidden" name="summaryfield" value="0"/>
						<input type="checkbox"
							   name="summaryfield"
							   id="summaryfield" {if $FIELD_MODEL->isSummaryField()} checked {/if}{strip} {/strip}
								{if $FIELD_MODEL->isSummaryFieldOptionDisabled()} readonly="readonly" class="optionDisabled"{/if}
							   value="1"/>
						<label for="summaryfield">
							{App\Language::translate('LBL_SUMMARY_FIELD', $QUALIFIED_MODULE)}
						</label>
					</div>
					<div class="checkbox">
						<input type="hidden" name="header_field" value="0"/>
						<input type="checkbox" name="header_field"
							   id="header_field" {if $FIELD_MODEL->isHeaderField()} checked {/if}
							   value="1"/>
						<label for="header_field">
							{App\Language::translate('LBL_HEADER_FIELD', $QUALIFIED_MODULE)}
						</label>
						<div class="js-toggle-hide form-group{if !$FIELD_MODEL->isHeaderField()} zeroOpacity {/if}{if $FIELD_MODEL->getFieldDataType() neq 'picklist'} hide{/if}" data-js="class:zeroOpacity">
							{assign var=HEADER_FIELD_VALUE value=$FIELD_MODEL->getHeaderValue('class')}
							{assign var=HEADER_FIELD_TYPE value=$FIELD_MODEL->getHeaderValue('type')}
							<select name="header_type" class="form-control select2">
								{foreach key=LABEL item=VALUE from=$FIELD_MODEL->getUITypeModel()->getHeaderTypes()}
									<option value="{$VALUE}" {if $VALUE == $HEADER_FIELD_TYPE} selected {/if}>{App\Language::translate($LABEL, $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
							<input name="header_class" value="{if $HEADER_FIELD_VALUE}{$HEADER_FIELD_VALUE}{else}badge-info{/if}" type="text" class="hide">
						</div>
					</div>
					<div class="checkbox">
						<input type="hidden" name="masseditable" value="2"/>
						<input type="checkbox"
							   name="masseditable"
							   id="masseditable" {if $FIELD_MODEL->isMassEditable()} checked {/if} {strip} {/strip}
								{if $FIELD_MODEL->isMassEditOptionDisabled()} readonly="readonly" {/if} value="1"/>
						<label for="masseditable">
							{App\Language::translate('LBL_MASS_EDIT', $QUALIFIED_MODULE)}
						</label>
					</div>

					<div class="checkbox">
						<input type="hidden" name="defaultvalue" value="0"/>
						<input type="checkbox"
							   name="defaultvalue"
							   id="defaultvalue" {if $FIELD_MODEL->hasDefaultValue()} checked {/if} {strip} {/strip}
								{if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if} value="1"/>
						<label for="defaultvalue">
							{App\Language::translate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}
						</label>
						<div class="js-toggle-hide form-group{if !$FIELD_MODEL->hasDefaultValue()} zeroOpacity {/if}" data-js="container">
							{if $FIELD_MODEL->isDefaultValueOptionDisabled() neq "true"}
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDefaultEditTemplateName(), $FIELD_MODEL->getModuleName())}
							{/if}
						</div>
					</div>
					{if in_array($FIELD_MODEL->getFieldDataType(),['string','currency','url','integer','double'])}
					<div>
						<div class="form-group">
							<label for="fieldMask"><strong>{App\Language::translate('LBL_FIELD_MASK', $QUALIFIED_MODULE)}</strong></label>
							<div class=" input-group">
								<input type="text" class="form-control" id="fieldMask" name="fieldMask"
										{if $FIELD_MODEL->get('maximumlength')} data-validation-engine="validate[maxSize[{$FIELD_MODEL->get('maximumlength')}]]{/if}"
									   value="{$FIELD_MODEL->get('fieldparams')}"/>
								<div class="input-group-append">
								<span class="input-group-text js-popover-tooltip u-cursor-pointer" data-js="popover"
									  data-placement="top"
									  data-content="{App\Language::translate('LBL_FIELD_MASK_INFO', $QUALIFIED_MODULE)}">
									<span class="fas fa-info-circle"></span>
								</span>
								</div>
							</div>
						</div>
						{/if}
						<div class="form-group">
							<label for="maxlengthtext"><strong>{App\Language::translate('LBL_MAX_LENGTH_TEXT', $QUALIFIED_MODULE)}</strong></label>
							<input type="text" class="form-control" id="maxlengthtext" name="maxlengthtext"
								   value="{$FIELD_MODEL->get('maxlengthtext')}"/>
						</div>
						<div class="form-group">
							<label for="maxwidthcolumn"><strong>{App\Language::translate('LBL_MAX_WIDTH_COLUMN', $QUALIFIED_MODULE)}</strong></label>
							<div class=" input-group">
								<input type="text" class="form-control" id="maxwidthcolumn" name="maxwidthcolumn"
									   value="{$FIELD_MODEL->get('maxwidthcolumn')}"/>
								<div class="input-group-append">
									<div class="input-group-text js-popover-tooltip u-cursor-pointer" data-js="popover"
										 data-placement="top"
										 data-content="{App\Language::translate('LBL_MAX_WIDTH_COLUMN_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								</div>
							</div>
						</div>
						{if AppConfig::developer('CHANGE_GENERATEDTYPE')}
							<div class="checkbox">
							<input type="hidden" name="generatedtype" value="0"/>
							<input type="checkbox" name="generatedtype" id="generatedtype"
									   value="1" {if $FIELD_MODEL->get('generatedtype') eq 1} checked {/if} />
								<label for="generatedtype">
									{App\Language::translate('LBL_GENERATED_TYPE', $QUALIFIED_MODULE)}
								</label>
							</div>
						{/if}
						{if AppConfig::developer('CHANGE_VISIBILITY')}
							<div class="form-group">
								<label for="displaytype">
									<strong>{App\Language::translate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}</strong>
									{assign var=DISPLAY_TYPE value=Vtiger_Field_Model::showDisplayTypeList()}
								</label>
								<div class="js-toggle-hide">
									<select name="displaytype" class="form-control select2" id="displaytype">
										{foreach key=DISPLAY_TYPE_KEY item=DISPLAY_TYPE_VALUE from=$DISPLAY_TYPE}
											<option value="{$DISPLAY_TYPE_KEY}" {if $DISPLAY_TYPE_KEY == $FIELD_MODEL->get('displaytype')} selected {/if} >{App\Language::translate($DISPLAY_TYPE_VALUE, $QUALIFIED_MODULE)}</option>
										{/foreach}
									</select>
								</div>
							</div>
						{/if}
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL' MODULE=$QUALIFIED_MODULE}
				</form>
			</div>
		</div>
	</div>
{/strip}
