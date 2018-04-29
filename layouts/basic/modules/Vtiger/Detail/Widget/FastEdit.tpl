{strip}
	<div class="c-detail-widget u-mb-13px js-detail-widget summaryWidgetFastEditing" data-js=”container”>
		<div class="widgetContainer_{$key}" data-name="{$WIDGET['label']}">
			{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
				<div class="c-detail-widget__header js-detail-widget-header" data-js=”container|value>
					<h5 class="mb-0 py-2">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					<hr class="widgetHr">
				</div>
			{/if}
			{assign var=MODULEINSTANCE value=vtlib\Module::getInstance($MODULE_NAME)}
			<div class="c-detail-widget__content">
				{if !$WIDGET['data']['FastEdit']}
					{\App\Language::translate('LBL_RECORDS_NO_FOUND',$MODULE_NAME)}
				{else}
					{foreach item=item key=key from=$WIDGET['data']['FastEdit']}
						{assign var=FIELD value=Vtiger_Field_Model::getInstance($item,$MODULEINSTANCE)}
						{assign var=FIELD_MODEL value=$FIELD->getWithDefaultValue()}
						<div class="row mb-0 editField" data-prevvalue="{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}" data-fieldname = "q_{$FIELD_MODEL->getFieldName()}">
							<div class="col-md-5 m-0">
								<h4>{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</h4>
							</div>
							<div class="col-md-7">
								{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
								{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
								{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
								<select class="select2" name="q_{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
									{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}
										<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_MODEL->get('fieldvalue') neq ''} disabled{/if}>{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
									{/if}
									{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
										<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if trim(App\Purifier::decodeHtml($FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/foreach}
				{/if}
			</div>
		</div>
	</div>
{/strip}
