{strip}
<div class="summaryWidgetContainer summaryWidgetFastEditing">
	<div class="widgetContainer_{$key}" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
			<div class="widget_header marginBottom10px">
				<span class="margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
			</div>
		{/if}
		{assign var=MODULEINSTANCE value=vtlib\Module::getInstance($MODULE_NAME)}
		{if !$WIDGET['data']['FastEdit']}
			{vtranslate('LBL_RECORDS_NO_FOUND',$MODULE_NAME)}
		{else}
			{foreach item=item key=key from=$WIDGET['data']['FastEdit']}
				{assign var=FIELD value=Vtiger_Field_Model::getInstance($item,$MODULEINSTANCE)}
				{assign var=FIELD_MODEL value=$FIELD->getWithDefaultValue()}
				<div class="row marginBottom10px editField" data-prevvalue="{$FIELD_MODEL->get('fieldvalue')}" data-fieldname = "q_{$FIELD_MODEL->getFieldName()}">
					<div class="col-md-5 margin0px">
						<h4>{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}</h4>
					</div>
					<div class="col-md-7">
						{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
						{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
						{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
						<select class="chzn-select" name="q_{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
							{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}
								<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_MODEL->get('fieldvalue') neq ''} disabled{/if}>{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
							{/if}
							{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
								<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
		{/if}
	</div>
</div>
{/strip}
