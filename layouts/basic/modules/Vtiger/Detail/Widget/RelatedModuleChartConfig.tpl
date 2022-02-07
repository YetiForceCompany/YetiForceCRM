{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-RelatedModuleChartConfig -->
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget form-horizontal validateForm">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">
							<span class="fas fa-plus mr-1"></span>{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}
						</h5>
						<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						{if empty($WIDGETINFO['data']['relatedmodule'])}
							{assign var=RELATED_MODULE_ID value=0}
						{else}
							{assign var=RELATED_MODULE_ID value=$WIDGETINFO['data']['relatedmodule']}
						{/if}
						{if empty($WIDGETINFO['data']['relation_id'])}
							{assign var=RELATED_ID value=0}
						{else}
							{assign var=RELATED_ID value=$WIDGETINFO['data']['relation_id']}
						{/if}
						<div class="form-container-sm">
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}
									:</label>
								<div class="col-md-7 col-form-label">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">
									{\App\Language::translate('Label', $QUALIFIED_MODULE)}:
								</label>
								<div class="col-md-7 py-1">
									<input name="label" class="form-control form-control-sm" data-validation-engine="validate[required]" type="text" value="{$WIDGETINFO['label']}" />
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Related module', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top" data-content="{\App\Language::translate('Related module info', $QUALIFIED_MODULE)}"
										data-original-title="{\App\Language::translate('Related module', $QUALIFIED_MODULE)}">
										<i class="fas fa-info-circle"></i>
									</a>:
								</label>
								<div class="col-md-7 py-1">
									<select name="relation_id" {if $RELATED_ID} readonly="readonly" {/if} class="select2 form-control form-control-sm" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=item key=key}
											<option value="{$item['relation_id']}" {if $RELATED_ID == $item['relation_id']}selected{/if} data-relatedmodule="{$item['related_tabid']}">
												{\App\Language::translate($item['label'], $item['related_modulename'])}
											</option>
										{/foreach}
									</select>
									<input name="relatedmodule" type="hidden" value="{$RELATED_MODULE_ID}" />
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_GROUP_FIELD','Home')}:</label>
								<div class="col-md-7 py-1">
									<select name="groupField" class="select2 form-control form-control-sm" data-validation-engine="validate[required]">
										{assign var=MODULES value=[]}
										{foreach from=$RELATEDMODULES item=RELATED_MODULE key=key}
											{if ($RELATED_ID && $RELATED_ID == $RELATED_MODULE['relation_id']) || (!$RELATED_ID && !isset($MODULES[$RELATED_MODULE['related_tabid']]) && $RELATED_MODULE['related_tabid'])}
												{foreach from=Vtiger_Module_Model::getInstance($RELATED_MODULE['related_modulename'])->getFieldsByBlocks() key=BLOCK_NAME item=FIELDS}
													<optgroup label="{\App\Language::translate($BLOCK_NAME, $RELATED_MODULE['related_modulename'])}" data-module="{$RELATED_MODULE['related_tabid']}">
														{foreach from=$FIELDS item=FIELD_MODEL key=FIELD_NAME}
															{assign var=VALUE_NAME value="{$FIELD_NAME}"}
															<option value="{$VALUE_NAME}" {' '}
																{if !empty($WIDGETINFO['data']['groupField']) && $VALUE_NAME eq $WIDGETINFO['data']['groupField']}
																	selected="selected"
																{/if} data-module="{$RELATED_MODULE['related_tabid']}">
																{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATED_MODULE['related_modulename'])}
															</option>
														{/foreach}
													</optgroup>
												{/foreach}
												{append var='MODULES' value=$RELATED_MODULE['relation_id'] index=$RELATED_MODULE['related_tabid']}
											{/if}
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SELECT_CHART', 'Home')}:</label>
								<div class="col-md-7 py-1">
									<select name="chartType" class="select2 form-control form-control-sm" data-validation-engine="validate[required]">
										{foreach from=['Pie' => 'LBL_PIE_CHART','Donut' => 'LBL_DONUT_CHART','Bar' => 'LBL_VERTICAL_BAR_CHART','Horizontal' => 'LBL_HORIZONTAL_BAR_CHART','Line' => 'LBL_LINE_CHART','LinePlain' => 'LBL_LINE_CHART_PLAIN'] item=LABEL key=KEY}
											<option value="{$KEY}" {if !empty($WIDGETINFO['data']['chartType']) && $KEY eq $WIDGETINFO['data']['chartType']}
													selected="selected"
												{/if}>
												{\App\Language::translate($LABEL)}
											</option>
										{/foreach}
									</select>
								</div>
							</div>
							<input type="hidden" name="color" value="{if empty($WIDGETINFO['data']['color'])}1{else}{$WIDGETINFO['data']['color']}{/if}" />
							<input type="hidden" name="valueType" value="{if empty($WIDGETINFO['data']['valueType'])}count{else}{$WIDGETINFO['data']['valueType']}{/if}" />
							<input type="hidden" name="valueField" value="{if !empty($WIDGETINFO['data']['valueField'])}{$WIDGETINFO['data']['valueField']}{/if}" />
							<input type="hidden" name="limit" value="99999" />
							<input type="hidden" name="search_params" value="{if empty($WIDGETINFO['data']['search_params'])}[]{else}{\App\Purifier::encodeHtml(\App\Json::encode($WIDGETINFO['data']['search_params']))}{/if}" />
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-RelatedModuleChartConfig -->
{/strip}
