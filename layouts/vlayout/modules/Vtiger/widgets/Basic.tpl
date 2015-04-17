<div class="summaryWidgetContainer">
	<div class="widgetContainer_{$key}" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		<div class="widget_header row-fluid">
			<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
			<span class="span8 margin0px">
				<div class="row-fluid">
					{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
						{assign var=span value='span5'}
					{else}
						{assign var=span value='span12'}
					{/if}
					<span class="{$span} margin0px"><h4 class="moduleColor_{$WIDGET['label']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
					{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
						{assign var=filter value=$WIDGET['data']['filter']}
						<input type="hidden" name="filter_data" value="{$filter}" />
						<span class="span7">
							<div class="row-fluid">
								{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($WIDGET['data']['relatedmodule'])}
								{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($WIDGET['field_name'])}
								{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
								{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
								{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
								<select class="chzn-select span12 filterField" name="{$FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldlable='{vtranslate($FIELD_MODEL->get('label'),$WIDGET['data']['relatedmodule'])}'>
									<option>{vtranslate($FIELD_MODEL->get('label'),$WIDGET['data']['relatedmodule'])}</option>
									{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
										<option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
									{/foreach}
								</select>
							</div>
						</span>
					{/if}
				</div>
			</span>
			{if $WIDGET['data']['actionSelect'] neq 1}<span class="span2 margin0px">&nbsp;</span>{/if}
			{if $WIDGET['data']['action'] eq 1}
				{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
				{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $WIDGET['data']['relatedmodule'])}
				{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
				{assign var=RELATION_FIELD value=$RELATIONMODEL->getRelationField()}
				{assign var=AUTOCOMPLETE_FIELD value=$RELATIONMODEL->getAutoCompleteField($VRM)}
				<span class="span2 margin0px">
					<span class="pull-right">
						<button class="btn pull-right createRecordFromFilter" type="button" data-url="{$WIDGET['actionURL']}"
						{if $RELATION_FIELD} data-prf="{$RELATION_FIELD->getName()}" {/if} {if $AUTOCOMPLETE_FIELD} data-acf='{Zend_Json::encode($AUTOCOMPLETE_FIELD)}'{/if}>
							<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
						</button>
					</span>
				</span>
			{/if}
			{if $WIDGET['data']['actionSelect'] eq 1}
				{if isset($VRM)}
					{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
					{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $WIDGET['data']['relatedmodule'])}
					{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
					{assign var=RESTRICTIONS_FIELD value=$RELATIONMODEL->getRestrictionsPopupField($VRM)}
				{/if}
				<span class="span2" style="margin-left: 16px;">
					<span class="pull-right">
						<button class="btn pull-right selectRelation" type="button" data-modulename="{$WIDGET['data']['relatedmodule']}" {if $RESTRICTIONS_FIELD}data-rf='{Zend_Json::encode($RESTRICTIONS_FIELD)}'{/if}>
							<strong>{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}</strong>
						</button>
					</span>
				</span>
			{/if}
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>
