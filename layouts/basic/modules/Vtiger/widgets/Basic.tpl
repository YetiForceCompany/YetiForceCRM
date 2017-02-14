{strip}
<div class="summaryWidgetContainer">
	<div class="widgetContainer_{$key} widgetContentBlock" data-url="{Vtiger_Util_Helper::toSafeHTML($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
		<div class="widget_header">
			<input type="hidden" name="relatedModule" value="{$WIDGET['data']['relatedmodule']}" />
			<div class="row">
				<div class="col-xs-9 col-md-5 col-sm-6">
					<div class="widgetTitle textOverflowEllipsis">
						<h4 class="moduleColor_{$WIDGET['label']}">
							{if $WIDGET['label'] eq ''}
								{vtranslate(vtlib\Functions::getModuleName($WIDGET['data']['relatedmodule']),vtlib\Functions::getModuleName($WIDGET['data']['relatedmodule']))}
							{else}	
								{vtranslate($WIDGET['label'],$MODULE_NAME)}
							{/if}
						</h4>
					</div>
				</div>
				{if isset($WIDGET['switchHeader'])}
					<div class="col-xs-8 col-md-4 col-sm-3 paddingBottom10">
						<input class="switchBtn switchBtnReload filterField" type="checkbox" checked="" data-size="small" data-label-width="5" data-on-text="{$WIDGET['switchHeaderLables']['on']}" data-off-text="{$WIDGET['switchHeaderLables']['off']}" data-urlparams="search_params" data-on-val='{$WIDGET['switchHeader']['on']}' data-off-val='{$WIDGET['switchHeader']['off']}'>
					</div>
				{/if}
				<div class="col-md-3 col-sm-3 pull-right paddingBottom10">
					<div class="pull-right">
						<div class="btn-group">
							{if isset($WIDGET['data']['actionSelect']) || isset($WIDGET['data']['action'])}
								{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
								{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $WIDGET['data']['relatedmodule'])}
								{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
								{if $WIDGET['data']['actionSelect'] eq 1}
									{assign var=RESTRICTIONS_FIELD value=$RELATIONMODEL->getRestrictionsPopupField($VRM)}
									<button class="btn btn-sm btn-default selectRelation" type="button" data-modulename="{$RELATIONMODEL->getRelationModuleName()}" {if $RESTRICTIONS_FIELD}data-rf='{\App\Json::encode($RESTRICTIONS_FIELD)}'{/if} title="{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}" alt="{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}">
										<span class="glyphicon glyphicon-search"></span>
									</button>
								{/if}
								{if $WIDGET['data']['action'] eq 1}
									{assign var=RELATION_FIELD value=$RELATIONMODEL->getRelationField()}
									{assign var=AUTOCOMPLETE_FIELD value=$RELATIONMODEL->getAutoCompleteField($VRM)}
									<button class="btn btn-sm btn-default createRecordFromFilter" type="button" data-url="{$WIDGET['actionURL']}"
											{if $RELATION_FIELD} data-prf="{$RELATION_FIELD->getName()}" {/if} {if $AUTOCOMPLETE_FIELD} data-acf='{\App\Json::encode($AUTOCOMPLETE_FIELD)}'{/if} title="{vtranslate('LBL_ADD',$MODULE_NAME)}" alt="{vtranslate('LBL_ADD',$MODULE_NAME)}">
										<span class="glyphicon glyphicon-plus"></span>
									</button>
								{/if}
								{foreach from=$WIDGET['buttonHeader'] item=$LINK}
									{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='detailViewBasic'}
								{/foreach}
							{/if}
						</div>
					</div>
				</div>
			</div>
			<hr class="widgetHr"/>
			<div class="row">
				{if (isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-') AND (isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-')}
					{assign var=span value='col-xs-6'}
				{else}
					{assign var=span value='col-xs-12'}
				{/if}
				{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
					<div class="{$span} form-group-sm">
						{assign var=filter value=$WIDGET['data']['filter']}
						{*<input type="hidden" name="filter_data" value="{$filter}" />*}
						{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($WIDGET['data']['relatedmodule'])}
						{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($filter)}
						{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
						{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
						{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
						<select class="select2 filterField form-control input-sm" name="{$FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldlable='{vtranslate($FIELD_MODEL->get('label'),$WIDGET['data']['relatedmodule'])}' data-filter="{$FIELD_MODEL->get('table')|cat:'.'|cat:$filter}" data-urlparams="whereCondition">
							<option>{vtranslate($FIELD_MODEL->get('label'),$WIDGET['data']['relatedmodule'])}</option>
							{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
								<option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
							{/foreach}
						</select>
					</div>
				{/if}
				{if isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-'}
					<div class="{$span} small-select">
						{assign var=checkbox value=$WIDGET['data']['checkbox']}
						<input type="hidden" name="checkbox_data" value="{$checkbox}" />
						<div class="pull-right">
							<input class="switchBtn switchBtnReload filterField" type="checkbox" checked="" data-size="mini" data-label-width="5" data-on-text="{$WIDGET['checkboxLables']['on']}" data-off-text="{$WIDGET['checkboxLables']['off']}" data-urlparams="search_params" data-on-val='{$WIDGET['checkbox']['on']}' data-off-val='{$WIDGET['checkbox']['off']}'>
						</div>
					</div>
				{/if}
			</div>
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>
{/strip}
