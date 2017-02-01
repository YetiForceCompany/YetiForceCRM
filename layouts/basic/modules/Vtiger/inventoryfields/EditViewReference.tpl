{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="REFERENCE_LIST" value=$FIELD->getReferenceModules()}
	{assign var="FIELD_NAME" value={$FIELD->getColumnName()}|cat:$ROW_NO}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode(['mandatory'=>true]))}
	{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
	<div class="input-group referenceGroup" style="max-width: 250px;">
		{if $REFERENCE_LIST_COUNT eq 1}
			{assign var="REFERENCED_MODULE_NAME" value=reset($REFERENCE_LIST)}
			<input name="popupReferenceModule" type="hidden" data-multi-reference="0" title="{reset($REFERENCE_LIST)}" value="{reset($REFERENCE_LIST)}" />
		{/if}
		{if $REFERENCE_LIST_COUNT gt 1}
			{assign var="REFERENCED_MODULE_NAME" value=$FIELD->getReferenceModule($ITEM_VALUE)}
			{if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
				<input name="popupReferenceModule" type="hidden" data-multi-reference="1" value="{$REFERENCED_MODULE_NAME}" />
			{else}
				{assign var="REFERENCED_MODULE_NAME" value=$REFERENCE_LIST[0]}
				<input name="popupReferenceModule" type="hidden" data-multi-reference="1" value="{$REFERENCED_MODULE_NAME}" />
			{/if}
		{/if}
		{if $REFERENCE_LIST_COUNT > 1}
			<div class="input-group-addon noSpaces referenceModulesListGroup">
				<select class="referenceModulesList" title="{vtranslate('LBL_RELATED_MODULE_TYPE')}" required="required">
					{foreach key=index item=REFERENCE from=$REFERENCE_LIST}
						<option value="{$REFERENCE}" title="{vtranslate($REFERENCE, $REFERENCE)}" {if $REFERENCE eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($REFERENCE, $REFERENCE)}</option>
					{/foreach}
				</select>
			</div>
		{/if}
		<input name="{$FIELD_NAME}" type="hidden" value="{$ITEM_VALUE}" title="{$ITEM_VALUE}" class="sourceField" data-type="inventory" data-displayvalue="{Vtiger_Util_Helper::toSafeHTML($FIELD->getEditValue($ITEM_VALUE))}" data-columnname="{$FIELD->getColumnName()}" data-fieldinfo='{$FIELD_INFO}' {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />
		{assign var="displayId" value=$ITEM_VALUE}
		{if $FIELD->get('displaytype') != 10}
			<span class="input-group-addon clearReferenceSelection cursorPointer">
				<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class="glyphicon glyphicon-remove-sign" title="{vtranslate('LBL_CLEAR', $MODULE)}"></span>
			</span>
		{/if}
		<input id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" type="text" title="{Vtiger_Util_Helper::toSafeHTML($FIELD->getEditValue($ITEM_VALUE))}" class="marginLeftZero form-control autoComplete" {if !empty($ITEM_VALUE)}readonly="true"{/if}
			   value="{Vtiger_Util_Helper::toSafeHTML($FIELD->getEditValue($ITEM_VALUE))}" data-validation-engine="validate[{if $FIELD->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   data-fieldinfo="{$FIELD_INFO}" {if $FIELD->get('displaytype') != 10}placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"{/if}
			   {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>
		{if $FIELD->get('displaytype') != 10}
			<span class="input-group-addon relatedPopup cursorPointer">
				<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="glyphicon glyphicon-search relatedPopup" title="{vtranslate('LBL_SELECT', $MODULE)}" ></span>
			</span>
		{/if}
		{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCED_MODULE_NAME)}
		<!-- Show the add button only if it is edit view  -->
		{if $VIEW eq 'Edit' && $REFERENCE_MODULE_MODEL->isQuickCreateSupported() && $FIELD->get('displaytype') != 10}
			<span class="input-group-addon cursorPointer createReferenceRecord">
				<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="glyphicon glyphicon-plus" title="{vtranslate('LBL_CREATE', $MODULE)}"></span>
			</span>
		{/if}
	</div>
{/strip}
