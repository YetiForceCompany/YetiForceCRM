<div class="summaryWidgetContainer">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
		<div class="widget_header">
			<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
			<div class="row">
				<div class="col-md-9">
					<div class="widgetTitle textOverflowEllipsis"><h4 class="moduleColor_{$WIDGET['label']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></div>
				</div>
				<div class="col-md-3">
					<div class="pull-right">
						{if $WIDGET['data']['action'] eq 1}
							{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
							{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $WIDGET['data']['relatedmodule'])}
							{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
							{assign var=RELATION_FIELD value=$RELATIONMODEL->getRelationField()}
							{assign var=AUTOCOMPLETE_FIELD value=$RELATIONMODEL->getAutoCompleteField($VRM)}
							<button style="margin-left: 4px;" class="btn btn-sm pull-right btn-default createRecordFromFilter" type="button" data-url="{$WIDGET['actionURL']}"
									{if $RELATION_FIELD} data-prf="{$RELATION_FIELD->getName()}" {/if} {if $AUTOCOMPLETE_FIELD} data-acf='{Zend_Json::encode($AUTOCOMPLETE_FIELD)}'{/if}>
								<span class="glyphicon glyphicon-plus" border="0" title="{vtranslate('LBL_ADD',$MODULE_NAME)}" alt="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
							</button>
						{/if}
						{if $WIDGET['data']['actionSelect'] eq 1}
							{if isset($VRM)}
								{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
								{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $WIDGET['data']['relatedmodule'])}
								{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
								{assign var=RESTRICTIONS_FIELD value=$RELATIONMODEL->getRestrictionsPopupField($VRM)}
							{/if}
							<button class="btn btn-sm btn-default pull-right selectRelation" type="button" data-modulename="{$RELATIONMODEL->getRelationModuleName()}" {if $RESTRICTIONS_FIELD}data-rf='{Zend_Json::encode($RESTRICTIONS_FIELD)}'{/if}>
								<span class="glyphicon glyphicon-resize-small" border="0" title="{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}" alt="{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}"></span>
							</button>
						{/if}
					</div>
				</div>
			</div>
			<hr class="widgetHr"/>
			<div class="row">
				{if (isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-') AND (isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-')}
					{assign var=span value='col-md-6'}
				{else}
					{assign var=span value='col-md-12'}
				{/if}
				{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
					<div class="{$span} small-select">
						{assign var=filter value=$WIDGET['data']['filter']}
						<input type="hidden" name="filter_data" value="{$filter}" />
						<div class="row">
							{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($WIDGET['data']['relatedmodule'])}
							{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($filter)}
							{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
							{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
							{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
							<select class="chzn-select col-md-12 filterField" name="{$FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldlable='{vtranslate($FIELD_MODEL->get('label'),$WIDGET['data']['relatedmodule'])}'>
								<option>{vtranslate($FIELD_MODEL->get('label'),$WIDGET['data']['relatedmodule'])}</option>
								{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
									<option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
								{/foreach}
							</select>
						</div>
					</div>
				{/if}
				{if isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-'}
					<div class="{$span} small-select">
						{assign var=checkbox value=$WIDGET['data']['checkbox']}
						<input type="hidden" name="checkbox_data" value="{$checkbox}" />
						<div class="pull-right">
							<input class="switchBtn switchBtnReload" type="checkbox" checked="" data-size="mini" data-label-width="5" data-handle-width="100" data-on-text="{$WIDGET['checkboxLables']['on']}" data-off-text="{$WIDGET['checkboxLables']['off']}" data-urlparams="whereCondition[{$WIDGET['data']['checkbox']}]" data-on-val="1" data-off-val="">
						</div>
					</div>
				{/if}
			</div>
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>
