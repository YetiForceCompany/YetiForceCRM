{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="rowName">
		{if $SUP_VALUE == '0'}
			{assign var="REFERENCE_MODULE" value=reset($MAIN_PARAMS['modules'])}
		{else}
			{assign var="REFERENCE_MODULE" value=Vtiger_Functions::getCRMRecordType($SUP_VALUE)}
		{/if}

		{assign var="FIELD_NAME" value={$FIELD->getColumnName()}|cat:$ROW_NO}
		{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode(['mandatory'=>true]))}
		{assign var="CRMEntity" value=CRMEntity::getInstance($REFERENCE_MODULE)}

		<div class="input-group">
			<input name="popupReferenceModule" type="hidden" data-multi-reference="1" data-field="{$CRMEntity->table_index}" value="{$REFERENCE_MODULE}" />
			<input name="{$FIELD_NAME}" type="hidden" value="{$SUP_VALUE}" title="{$SUP_VALUE}" class="sourceField" data-displayvalue='{$FIELD->getEditValue($SUP_VALUE)}' data-fieldinfo='{$FIELD_INFO}' {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />

			{assign var="displayId" value=$SUP_VALUE}
			{if $FIELD->get('displaytype') != 10}
				<span class="input-group-addon clearReferenceSelection cursorPointer">
					<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class="glyphicon glyphicon-remove-sign" title="{vtranslate('LBL_CLEAR', $SUPMODULE)}"></span>
				</span>
			{/if}

			<input id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" type="text" title="{$FIELD->getEditValue($SUP_VALUE)}" class="marginLeftZero input-sm form-control autoComplete recordLabel" {if !empty($SUP_VALUE)}readonly="true"{/if}
				   value="{Vtiger_Util_Helper::toSafeHTML($FIELD->getEditValue($SUP_VALUE))}" data-validation-engine="validate[{if $FIELD->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				   data-fieldinfo="{$FIELD_INFO}" {if $FIELD->get('displaytype') != 10}placeholder="{vtranslate('LBL_TYPE_SEARCH',$SUPMODULE)}"{/if}
				   {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>

			{if $FIELD->get('displaytype') != 10}
				<span class="input-group-addon relatedPopup cursorPointer">
					<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="glyphicon glyphicon-search relatedPopup" title="{vtranslate('LBL_SELECT', $SUPMODULE)}" ></span>
				</span>
			{/if}

			{assign var=REFERENCE_MODULE_MODEL value=Vtiger_Module_Model::getInstance($REFERENCE_MODULE)}
			{if $REFERENCE_MODULE_MODEL->isQuickCreateSupported() && $FIELD->get('displaytype') != 10}
				<span class="input-group-addon cursorPointer createReferenceRecord">
					<span id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="glyphicon glyphicon-plus" title="{vtranslate('LBL_CREATE', $SUPMODULE)}"></span>
				</span>
			{/if}
		</div>
		<div class="subProductsContainer">
			<ul>
			</ul>
		</div>
		<div>
			<textarea name="comment{$ROW_NO}" title="{vtranslate("LBL_ROW_COMMENT",$SUPMODULE)}" class="comment commentTextarea form-control">{$SUP_DATA['comment']}</textarea>
		</div>
	</div>
{/strip}
