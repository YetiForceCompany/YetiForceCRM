{strip}
	<div class="tpl-Detail-Widget-Basic c-detail-widget u-mb-13px js-detail-widget" data-js="container">
		{assign var=RELATED_MODULE_NAME value=App\Module::getModuleName($WIDGET['data']['relatedmodule'])}
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
			 data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
			<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
				<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}"/>
				<div class="form-row align-items-center py-1">
					<div class="col-9 col-md-5 col-sm-6">
						<div class="widgetTitle u-text-ellipsis">
							<h5 class="mb-0 modCT_{$WIDGET['label']}">
								{if $WIDGET['label'] eq ''}
									{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
								{else}
									{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h5>
						</div>
					</div>
					{if isset($WIDGET['switchHeader'])}
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-sm btn-outline-primary active">
								<input class="js-switch" type="radio" name="options" id="option1" data-js="change"
									   data-on-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['on'])}'
									   data-urlparams="search_params"
									   autocomplete="off"
									   checked> {$WIDGET['switchHeaderLables']['on']}
							</label>
							<label class="btn btn-sm btn-outline-primary">
								<input class="js-switch" type="radio" name="options" id="option2" data-js="change"
									   data-off-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['off'])}'
									   data-urlparams="search_params"
									   autocomplete="off"> {$WIDGET['switchHeaderLables']['off']}
							</label>
						</div>
					{/if}
					<div class="col float-right">
						<div class="float-right">
							<div class="btn-group">
								{if !empty($WIDGET['data']['viewtype']) && $WIDGET['data']['viewtype'] eq 'Summary'}
									<div class="btn-group control-widget">
										<button class="btn btn-sm btn-light prev disabled" type="button"
												title="{\App\Language::translate('LBL_PREV',$MODULE_NAME)}">
											<span class="fas fa-angle-left"></span>
										</button>
										<button class="btn btn-sm btn-light next" type="button"
												title="{\App\Language::translate('LBL_NEXT',$MODULE_NAME)}">
											<span class="fas fa-angle-right"></span>
										</button>
									</div>
								{/if}
								{if isset($WIDGET['data']['actionSelect']) || isset($WIDGET['data']['action'])}
									{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
									{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME)}
									{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
									{if !empty($WIDGET['data']['actionSelect'])}
										{assign var=RESTRICTIONS_FIELD value=$RELATIONMODEL->getRestrictionsPopupField($VRM)}
										<button class="btn btn-sm btn-light selectRelation js-popover-tooltip"
												type="button" data-placement="top"
												data-modulename="{$RELATIONMODEL->getRelationModuleName()}"
												{if $RESTRICTIONS_FIELD}data-rf='{\App\Json::encode($RESTRICTIONS_FIELD)}'{/if}
												data-content="{\App\Language::translate('LBL_SELECT_RELATION',$RELATIONMODEL->getRelationModuleName())}">
											<span class="fas fa-search"></span>
										</button>
									{/if}
									{if !empty($WIDGET['data']['action']) && \App\Privilege::isPermitted($RELATIONMODEL->getRelationModuleName(), 'CreateView')}
										{assign var=RELATION_FIELD value=$RELATIONMODEL->getRelationField()}
										{assign var=AUTOCOMPLETE_FIELD value=$RELATIONMODEL->getAutoCompleteField($VRM)}
										<button class="btn btn-sm btn-light {if $WIDGET['isInventory']} createInventoryRecordFromFilter {else} createRecordFromFilter{/if} js-popover-tooltip"
												type="button" data-url="{$WIDGET['actionURL']}"
												{if $RELATION_FIELD} data-prf="{$RELATION_FIELD->getName()}" {/if} {if $AUTOCOMPLETE_FIELD} data-acf='{\App\Json::encode($AUTOCOMPLETE_FIELD)}'{/if}
												data-placement="top"
												data-content="{\App\Language::translate('LBL_ADD_RELATION',$RELATIONMODEL->getRelationModuleName())}">
											<span class="fas fa-plus"></span>
										</button>
									{/if}
									{foreach from=$WIDGET['buttonHeader'] item=$LINK}
										{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewBasic' MODULE=$MODULE_NAME}
									{/foreach}
								{/if}
							</div>
						</div>
					</div>
				</div>
				<hr class="widgetHr"/>
				<div class="row">
					{if (isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-') AND (isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-')}
						{assign var=span value='col-6'}
					{else}
						{assign var=span value='col-12'}
					{/if}
					{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
						<div class="{$span} form-group-sm">
							{assign var=FILTER value=$WIDGET['data']['filter']}
							{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME)}
							{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FILTER)}
							{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
							{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
							{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
							<select name="{$FIELD_MODEL->getName()}"
									class="select2 form-control form-control-sm js-filter_field"
									data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
									data-fieldinfo='{$FIELD_INFO|escape}'
									{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if}
									data-fieldlable='{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$RELATED_MODULE_NAME)}'
									data-filter="{$FILTER}" data-urlparams="search_params"
									data-js="change">
								<option>{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$RELATED_MODULE_NAME)}</option>
								{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
									<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
								{/foreach}
							</select>
						</div>
					{/if}
					{if isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-'}
						{assign var=checkbox value=$WIDGET['data']['checkbox']}
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-sm btn-outline-primary active">
								<input class="js-switch" type="radio" name="options" id="option1" data-js="change"
									   data-on-val='{\App\Purifier::encodeHtml($WIDGET['checkbox']['on'])}'
									   data-urlparams="search_params"
									   autocomplete="off"
									   checked> {$WIDGET['checkboxLables']['on']}
							</label>
							<label class="btn btn-sm btn-outline-primary">
								<input class="js-switch" type="radio" name="options" id="option2" data-js="change"
									   data-off-val='{\App\Purifier::encodeHtml($WIDGET['checkbox']['off'])}'
									   data-urlparams="search_params"
									   autocomplete="off"> {$WIDGET['checkboxLables']['off']}
							</label>
						</div>
					{/if}
				</div>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value">
			</div>
		</div>
	</div>
{/strip}
