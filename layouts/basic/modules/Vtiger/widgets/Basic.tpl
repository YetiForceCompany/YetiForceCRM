{strip}
	<div class="summaryWidgetContainer">
		{assign var=RELATED_MODULE_NAME value=App\Module::getModuleName($WIDGET['data']['relatedmodule'])}
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="widget_header">
				<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}" />
				<div class="row">
					<div class="col-xs-9 col-md-5 col-sm-6">
						<div class="widgetTitle textOverflowEllipsis">
							<h4 class="modCT_{$WIDGET['label']}">
								{if $WIDGET['label'] eq ''}
									{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
								{else}
									{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h4>
						</div>
					</div>
					{if isset($WIDGET['switchHeader'])}
						<div class="col-xs-8 col-md-4 col-sm-3 paddingBottom10">
							<input class="switchBtn switchBtnReload filterField" type="checkbox" checked="" data-size="small" data-label-width="5" data-on-text="{$WIDGET['switchHeaderLables']['on']}" data-off-text="{$WIDGET['switchHeaderLables']['off']}" data-urlparams="search_params" data-on-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['on'])}' data-off-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['off'])}'>
						</div>
					{/if}
					<div class="col-md-3 col-sm-3 float-right paddingBottom10">
						<div class="float-right">
							<div class="btn-group">
								{if $WIDGET['data']['viewtype'] eq 'Summary'}
									<div class="btn-group control-widget">
										<button class="btn btn-sm btn-light prev disabled" type="button" title="{\App\Language::translate('LBL_PREV',$MODULE_NAME)}" >
											<span class="fas fa-angle-left"></span>
										</button>
										<button class="btn btn-sm btn-light next" type="button" title="{\App\Language::translate('LBL_NEXT',$MODULE_NAME)}">
											<span class="fas fa-angle-right"></span>
										</button>
									</div>
								{/if}
								{if isset($WIDGET['data']['actionSelect']) || isset($WIDGET['data']['action'])}
									{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
									{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME)}
									{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
									{if $WIDGET['data']['actionSelect'] eq 1}
										{assign var=RESTRICTIONS_FIELD value=$RELATIONMODEL->getRestrictionsPopupField($VRM)}
										<button class="btn btn-sm btn-light selectRelation" type="button" data-modulename="{$RELATIONMODEL->getRelationModuleName()}" {if $RESTRICTIONS_FIELD}data-rf='{\App\Json::encode($RESTRICTIONS_FIELD)}'{/if} title="{\App\Language::translate('LBL_SELECT_OPTION',$MODULE_NAME)}" alt="{\App\Language::translate('LBL_SELECT_OPTION',$MODULE_NAME)}">
											<span class="fas fa-search"></span>
										</button>
									{/if}
									{if $WIDGET['data']['action'] eq 1 && \App\Privilege::isPermitted($RELATIONMODEL->getRelationModuleName(), 'CreateView')}
										{assign var=RELATION_FIELD value=$RELATIONMODEL->getRelationField()}
										{assign var=AUTOCOMPLETE_FIELD value=$RELATIONMODEL->getAutoCompleteField($VRM)}
										<button class="btn btn-sm btn-light createRecordFromFilter" type="button" data-url="{$WIDGET['actionURL']}"
												{if $RELATION_FIELD} data-prf="{$RELATION_FIELD->getName()}" {/if} {if $AUTOCOMPLETE_FIELD} data-acf='{\App\Json::encode($AUTOCOMPLETE_FIELD)}'{/if} title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}" alt="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}">
											<span class="fas fa-plus"></span>
										</button>
									{/if}
									{foreach from=$WIDGET['buttonHeader'] item=$LINK}
										{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic'}
									{/foreach}
								{/if}
							</div>
						</div>
					</div>
				</div>
				<hr class="widgetHr" />
				<div class="row">
					{if (isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-') AND (isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-')}
						{assign var=span value='col-xs-6'}
					{else}
						{assign var=span value='col-xs-12'}
					{/if}
					{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
						<div class="{$span} form-group-sm">
							{assign var=FILTER value=$WIDGET['data']['filter']}
							{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME)}
							{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FILTER)}
							{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
							{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
							{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
							<select class="select2 filterField form-control input-sm" name="{$FIELD_MODEL->getName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldlable='{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$RELATED_MODULE_NAME)}' data-filter="{$FILTER}" data-urlparams="search_params">
								<option>{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$RELATED_MODULE_NAME)}</option>
								{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
									<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
								{/foreach}
							</select>
						</div>
					{/if}
					{if isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-'}
						<div class="{$span} small-select">
							{assign var=checkbox value=$WIDGET['data']['checkbox']}
							<input type="hidden" name="checkbox_data" value="{$checkbox}" />
							<div class="float-right">
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
