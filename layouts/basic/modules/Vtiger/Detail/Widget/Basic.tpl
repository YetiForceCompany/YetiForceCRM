{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-Basic -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
	{assign var=RELATED_MODULE_NAME value=App\Module::getModuleName($WIDGET['data']['relatedmodule'])}
	{assign var=RELATION_ID value=null}
	{if $RELATED_MODULE_NAME}
		{if isset($WIDGET['data']['relation_id'])}
			{assign var=RELATION_ID value=$WIDGET['data']['relation_id']}
		{/if}
		<div class="c-detail-widget js-detail-widget" data-name="{$WIDGET['label']}" data-module-name="{$RELATED_MODULE_NAME}"
			{if $RELATION_ID}data-relation-id="{$RELATION_ID}" {/if}
			data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}" data-js="container">
			<div class="widgetContainer_{$key} widgetContentBlock" data-url="{\App\Purifier::encodeHtml($WIDGET['url'])}"
				data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}" data-id="{$WIDGET['id']}">
				<div class="c-detail-widget__header js-detail-widget-header collapsed border-bottom-0"
					data-js="container|value">
					<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}" />
					<div class="c-detail-widget__header__container d-flex align-items-center py-1">
						<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
							data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
							<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>
						</div>
						<div class="c-detail-widget__header__title">
							<h5 class="mb-0 text-truncate modCT_{$RELATED_MODULE_NAME}">
								{if $WIDGET['label'] eq ''}
									{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
								{else}
									{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
								{/if}
							</h5>
						</div>
						<div class="row inline justify-center js-hb__container ml-auto">
							<button type="button" tabindex="0" class="btn js-hb__btn u-hidden-block-btn text-grey-6 py-0 px-1">
								<div class="text-center col items-center justify-center row">
									<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
								</div>
							</button>
							<div class="u-hidden-block items-center js-comment-actions">
								{if isset($WIDGET['switchHeader'])}
									<div class="ml-auto btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-sm btn-outline-primary active">
											<input class="js-switch" type="radio" name="options" id="search_params-option1"
												data-js="change"
												data-on-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['on'])}'
												data-urlparams="search_params" autocomplete="off" checked>
											{$WIDGET['switchHeaderLables']['on']}
										</label>
										<label class="btn btn-sm btn-outline-primary">
											<input class="js-switch" type="radio" name="options" id="search_params-option2"
												data-js="change"
												data-off-val='{\App\Purifier::encodeHtml($WIDGET['switchHeader']['off'])}'
												data-urlparams="search_params" autocomplete="off">
											{$WIDGET['switchHeaderLables']['off']}
										</label>
									</div>
								{/if}
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
									{if empty($IS_READ_ONLY) && (isset($WIDGET['data']['actionSelect']) || isset($WIDGET['data']['action']))}
										{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
										{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME, $RELATION_ID)}
										{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
										{if !empty($WIDGET['data']['actionSelect']) && $VRMM->getSelectRelationLinks()}
											{assign var=RESTRICTIONS_FIELD value=$RELATIONMODEL->getRestrictionsPopupField($VRM)}
											<button class="btn btn-sm btn-light selectRelation js-popover-tooltip ml-1" type="button"
												data-placement="top" data-modulename="{$RELATIONMODEL->getRelationModuleName()}"
												{if $RESTRICTIONS_FIELD}data-rf='{\App\Purifier::encodeHtml(\App\Json::encode($RESTRICTIONS_FIELD))}'
												{/if}
												data-content="{\App\Language::translate('LBL_SELECT_RELATION',$RELATIONMODEL->getRelationModuleName())}">
												<span class="fas fa-search"></span>
											</button>
										{/if}
										{if !empty($WIDGET['data']['action']) && $VRMM->getAddRelationLinks()}
											{assign var=AUTOCOMPLETE_FIELD value=$RELATIONMODEL->getAutoCompleteField($VRM)}
											<button
												class="btn btn-sm btn-light {if $WIDGET['isQuickCreateSupport']}createInventoryRecordFromFilter{else}createRecordFromFilter{/if} js-popover-tooltip ml-1"
												type="button" data-url="{$WIDGET['actionURL']}" {if $AUTOCOMPLETE_FIELD}
												data-acf='{\App\Purifier::encodeHtml(\App\Json::encode($AUTOCOMPLETE_FIELD))}' {/if}
												data-placement="top"
												data-content="{\App\Language::translate('LBL_ADD_RELATION',$RELATIONMODEL->getRelationModuleName())}">
												<span class="fas fa-plus"></span>
											</button>
										{/if}
										{if !empty($WIDGET['buttonHeader'])}
											{foreach from=$WIDGET['buttonHeader'] item=$LINK}
												{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewBasic' MODULE=$MODULE_NAME}
											{/foreach}
										{/if}
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="c-detail-widget__content js-detail-widget-collapse collapse multi-collapse pt-0"
					id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}">
					<div
						class="{if isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-'} pb-2 {/if} d-flex m-0">
						{if (isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-') && (isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-')}
							{assign var=span value='col-6'}
						{else}
							{assign var=span value='col-12'}
						{/if}
						{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME)}
						{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
							<div class="form-group-sm w-100 mr-2">
								{assign var=FILTER value=$WIDGET['data']['filter']}
								{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FILTER)}
								{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
								{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
								{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
								<select name="{$FIELD_MODEL->getName()}" class="select2 form-control form-control-sm js-filter_field"
									data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
									data-fieldinfo='{$FIELD_INFO|escape}'
									{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
									data-fieldlable='{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$RELATED_MODULE_NAME)}'
									data-filter="{$FILTER}" data-urlparams="search_params" data-js="change">
									<option>{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$RELATED_MODULE_NAME)}</option>
									{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
										<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME}selected{/if}>
											{\App\Purifier::encodeHtml($PICKLIST_VALUE)}
										</option>
									{/foreach}
								</select>
							</div>
						{/if}
						{if !empty($WIDGET['data']['customView'])}
							{if count($WIDGET['data']['customView']) > 1}
								{assign var=CUSTOM_VIEWS_DETAILS value=\App\CustomView::getInstance($RELATED_MODULE_NAME)->getFilters()}
								<div class="form-group-sm w-100 mb-1">
									<select class="select2 form-control form-control-sm js-filter_field" data-urlparams="cvId"
										data-return="value" data-js="change|value">
										{foreach item=CV_ID from=$WIDGET['data']['customView']}
											{if isset($CUSTOM_VIEWS_DETAILS[$CV_ID])}
												<option value="{$CV_ID}">
													{\App\Language::translate($CUSTOM_VIEWS_DETAILS[$CV_ID]['viewname'], $RELATED_MODULE_NAME)}
												</option>
											{/if}
										{/foreach}
									</select>
								</div>
							{/if}
						{/if}
						{if isset($WIDGET['data']['checkbox']) && $WIDGET['data']['checkbox'] neq '-'}
							{assign var=checkbox value=$WIDGET['data']['checkbox']}
							{assign var=FIELD_NAME value=explode('.', $checkbox)}
							<div class="js-popover-tooltip ml-auto btn-group btn-group-toggle" data-toggle="buttons"
								{if !empty($RELATED_MODULE_MODEL->getFieldByName($FIELD_NAME[1]))} data-js="popover"
									data-content="{\App\Language::translate($RELATED_MODULE_MODEL->getFieldByName($FIELD_NAME[1])->getFieldLabel(),$RELATED_MODULE_NAME)}"
								{/if}>
								<label class="btn btn-sm btn-outline-primary active">
									<input class="js-switch" type="radio" name="options" id="option1" data-js="change"
										data-on-val='{\App\Purifier::encodeHtml($WIDGET['checkbox']['on'])}'
										data-urlparams="search_params" autocomplete="off" checked> <span
										class="far fa-check-circle fa-lg" title="{$WIDGET['checkboxLables']['on']}">
								</label>
								<label class="btn btn-sm btn-outline-primary">
									<input class="js-switch" type="radio" name="options" id="option2" data-js="change"
										data-off-val='{\App\Purifier::encodeHtml($WIDGET['checkbox']['off'])}'
										data-urlparams="search_params" autocomplete="off"> <span class="far fa-times-circle fa-lg"
										title="{$WIDGET['checkboxLables']['off']}"></span>
								</label>
							</div>
						{/if}
						{if !empty($WIDGET['instance']) && method_exists($WIDGET['instance'], 'getCustomFields')}
							{foreach from=$WIDGET['instance']->getCustomFields() item=FIELD_MODEL}
								{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
								<div class="form-group-sm w-100 mr-1 mb-1">
									<select name="{$FIELD_MODEL->getName()}"
										class="select2 form-control form-control-sm js-filter_field"
										data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
										data-fieldinfo='{$FIELD_INFO|escape}'
										{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if} data-return="value" data-urlparams="{$FIELD_MODEL->getName()}" data-js="change">
										<optgroup class="p-0">
											<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
										</optgroup>
										{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$FIELD_MODEL->getPicklistValues()}
											<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME}selected{/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
										{/foreach}
									</select>
								</div>
							{/foreach}
						{/if}
					</div>
					<div class="js-detail-widget-content" data-js="container|value"></div>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-Base-Detail-Widget-Basic -->
{/strip}
