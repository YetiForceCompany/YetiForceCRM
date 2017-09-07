{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<form name="condition" action="index.php" method="post" id="workflow_step2" class="form-horizontal" >
	<input type="hidden" name="view" value="Step3" />
	<input type="hidden" name="module" value="{$MODULE_NAME}" />
	<input type="hidden" name="parent" value="Settings" />
	<input type="hidden" name="base_module" value="{$BASE_MODULE}" />
	<input type="hidden" name="summary" value="{$SUMMARY}" />
	<input type="hidden" name="condition_all_json" value="" />
	<input type="hidden" name="condition_option_json" value="" />
	{if $TPL_ID}
		<input type="hidden" name="tpl_id" value="{$TPL_ID}" />
	{/if}
	<div class="row padding1per contentsBackground no-margin" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
		<div id="advanceFilterContainer" class="">
			<h5 class="padding-bottom1per"><strong>{\App\Language::translate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}</strong></h5>
			<div class="allConditionContainer conditionGroup contentsBackground well">
				<div class="header"><span><strong>{\App\Language::translate('LBL_CONDITION_ALL', $QUALIFIED_MODULE)}</strong></span> - <span>{\App\Language::translate('LBL_CONDITION_ALL_DSC', $QUALIFIED_MODULE)}</span></div>
				<div id="condition_all">
					{if $TPL_ID}
						{foreach from=$REQUIRED_CONDITIONS key=cnd_key item=cnd_item name=field_select}
							<div class="row conditionRow marginBottom10px" id="cnd_num_{$smarty.foreach.field_select.index}">
								<div class="col-md-4">
									<select data-num="{$smarty.foreach.field_select.index}" class="chzn-select comparator-select form-control field-name-select" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
										{foreach key=FIELD_MODULE_NAME item=FIELD from=$FIELD_LIST}
											<optgroup label='{\App\Language::translate($FIELD_MODULE_NAME, $FIELD_MODULE_NAME)}'>
												{foreach from=$FIELD key=key item=item}
													<option data-module="{$FIELD_MODULE_NAME}" value="{$item['name']}" {if $cnd_item['fieldname'] eq $item['name']}selected{/if}
															data-uitype="{$item['uitype']}" data-info="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($item['info']))}"
															>{\App\Language::translate($item['label'], $BASE_MODULE)}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
								</div>
								<div class="col-md-3">
									<select data-num="{$smarty.foreach.field_select.index}" class="chzn-select form-control" name="comparator">
										{assign var=CONDITION_LIST value=Settings_DataAccess_Module_Model::getConditionByType($cnd_item['field_type'])}
										{foreach from=$CONDITION_LIST item=item key=key}
											<option value="{$item}" {if $cnd_item['comparator'] eq $item}selected{/if}>
												{\App\Language::translate($item,$QUALIFIED_MODULE)}
											</option>
										{/foreach}
									</select>
								</div>
								<div class="col-md-4 fieldUiHolder">
									{if $cnd_item['field_type'] eq 'picklist' || $cnd_item['field_type'] eq 'tree' }
										<select name="val" data-value=value" class="form-control select2">
											{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
												<option value="{$pick_key}" {if $cnd_item['val'] eq $pick_key}selected{/if}>{$pick_item}</option>
											{/foreach}
										</select>
									{else if $cnd_item['field_type'] eq 'multipicklist'}
										<select multiple="multiple" name="val" data-value="value" class="form-control select2">
											{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
												<option value="{$pick_key}"  {if in_array($pick_key, $cnd_item['val'])} selected {/if}>{$pick_item}</option>
											{/foreach}
										</select>
									{else if $cnd_item['field_type'] eq 'owner'}
										<select multiple="multiple" name="val" data-value="value" class="form-control select2">
											{foreach from=$cnd_item['info']['picklistvalues'] key=TYPE item=ITEMS}
												<optgroup label="{$TYPE}"></optgroup>
												{foreach from=$ITEMS key=VALUE item=USER}
													<option value="{$VALUE}"  {if in_array($VALUE, $cnd_item['val'])} selected {/if}>{$USER}</option>
												{/foreach}
											{/foreach}
										</select>
									{else if $cnd_item['field_type'] eq 'time'}
										<div class="input-group time"><input type="text" data-format="24" value="{$cnd_item['val']}" class="clockPicker form-control" name="val" autocomplete="off"><span class="input-group-addon cursorPointer"><i class="glyphicon glyphicon-time"></i></span></div>
											{else if $cnd_item['field_type'] eq 'date'}
												{if $cnd_item['comparator'] == 'between'}
											<div class="date"><input class="dateRangeField bw form-control" data-calendar-type="range" name="val" data-date-format="yyyy-mm-dd" type="text" readonly="true" value="{$cnd_item['val']|escape}" data-value="value"></div>
											{else if in_array($cnd_item['comparator'], array("less than days ago", "more than days ago", "in less than", "in more than", "days ago", "days later"))}
											<input name="val" data-value="value" class="form-control" type="text" value="{$cnd_item['val']|escape}" />
										{else}
											<div class="input-group"><input class="col-md-9 dateField dateFieldNormal form-control" value="{$cnd_item['val']|escape}" name="val" data-date-format="yyyy-mm-dd"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span></div>
												{/if}
											{else}
										<input name="val" data-value="value" class="form-control" type="text" value="{$cnd_item['val']|escape}" />
									{/if}
								</div>
								<div class="col-md-1 form-control-static">
									<i class="deleteCondition glyphicon glyphicon-trash alignMiddle" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" onclick="jQuery(this).parents('div#cnd_num_{$smarty.foreach.field_select.index}').remove()"></i>
								</div>
							</div>
						{/foreach}
					{/if}
				</div>
				<div class="addCondition"><button class="add_condition btn btn-default" data-type="condition_all" type="button"><strong>{\App\Language::translate('ADD_CONDITIONS', $QUALIFIED_MODULE)}</strong></button></div>
			</div>
			<div class="allConditionContainer conditionGroup contentsBackground well">
				<div class="header"><span><strong>{\App\Language::translate('LBL_CONDITION_OPTION', $QUALIFIED_MODULE)}</strong></span> - <span>{\App\Language::translate('LBL_CONDITION_OPTION_DSC', $QUALIFIED_MODULE)}</span></div>
				<div id="condition_option">
					{if $TPL_ID}
						{foreach from=$OPTIONAL_CONDITIONS key=cnd_key item=cnd_item name=field_select}
							<div class="row conditionRow marginBottom10px" id="cnd_num_{$smarty.foreach.field_select.index}">
								<span class="col-md-4">
									<select data-num="{$smarty.foreach.field_select.index}" class="chzn-select comparator-select form-control field-name-select" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
										{foreach key=FIELD_MODULE_NAME item=FIELD from=$FIELD_LIST}
											<optgroup label='{\App\Language::translate($FIELD_MODULE_NAME, $FIELD_MODULE_NAME)}'>
												{foreach from=$FIELD key=key item=item}
													<option data-module="{$FIELD_MODULE_NAME}" value="{$item['name']}" {if $cnd_item['fieldname'] eq $item['name']}selected{/if}
															data-uitype="{$item['uitype']}" data-info="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($item['info']))}"
															>{\App\Language::translate($item['label'], $BASE_MODULE)}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
								</span>
								<span class="col-md-3">
									<select data-num="{$smarty.foreach.field_select.index}" class="chzn-select form-control" name="comparator">
										{assign var=CONDITION_LIST value=Settings_DataAccess_Module_Model::getConditionByType($cnd_item['field_type'])}
										{foreach from=$CONDITION_LIST item=item key=key}
											<option value="{$item}" {if $cnd_item['comparator'] eq $item}selected{/if}>
												{\App\Language::translate($item,$QUALIFIED_MODULE)}
											</option>
										{/foreach}
									</select>
								</span>
								<span class="col-md-4 fieldUiHolder">
									{if $cnd_item['field_type'] eq 'picklist'}
										<select name="val" data-value=value" class="form-control select2">
											{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
												<option value="{$pick_key}" {if $cnd_item['val'] eq $pick_key}selected{/if}>{$pick_item}</option>
											{/foreach}
										</select>
									{else if $cnd_item['field_type'] eq 'multipicklist'}
										<select multiple="multiple" name="val" data-value="value" class="form-control select2">
											{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
												<option value="{$pick_key}"  {if in_array($pick_key, $cnd_item['val'])} selected {/if}>{$pick_item}</option>
											{/foreach}
										</select>
									{else if $cnd_item['field_type'] eq 'time'}
										<div class="input-group time"><input type="text" data-format="24" value="{$cnd_item['val']}" class="timepicker-default input-sm ui-timepicker-input" name="val" autocomplete="off"><span class="input-group-addon cursorPointer"><i class="glyphicon glyphicon-time"></i></span></div>
											{else if $cnd_item['field_type'] eq 'date'}
												{if $cnd_item['comparator'] == 'between'}
											<div class="date"><input class="dateRangeField bw form-control" data-calendar-type="range" name="val" data-date-format="yyyy-mm-dd" type="text" readonly="true" value="{$cnd_item['val']|escape}" data-value="value"></div>
											{else if in_array($cnd_item['comparator'], array("less than days ago", "more than days ago", "in less than", "in more than", "days ago", "days later"))}
											<input name="val" data-value="value" class="form-control" type="text" value="{$cnd_item['val']|escape}" />
										{else}
											<div class="input-group"><input class="col-md-9 dateField dateFieldNormal form-control" value="{$cnd_item['val']|escape}" name="val" data-date-format="yyyy-mm-dd"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span></div>
												{/if}
											{else}
										<input name="val" data-value="value" class="form-control" type="text" value="{$cnd_item['val']|escape}" />
									{/if}
								</span>
								<div class="col-md-1 form-control-static">
									<i class="deleteCondition glyphicon glyphicon-trash alignMiddle" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" onclick="jQuery(this).parents('div#cnd_num_{$smarty.foreach.field_select.index}').remove()"></i>
								</div>
							</div>
						{/foreach}
					{/if}
				</div>
				<div class="addCondition"><button class="add_condition btn btn-default" data-type="condition_option" type="button"><strong>{\App\Language::translate('ADD_CONDITIONS', $QUALIFIED_MODULE)}</strong></button></div>
			</div>
			<br />
			<div class="pull-right">
				<button class="btn btn-danger backStep" type="button" onclick="javascript:window.history.back();"><strong>{\App\Language::translate('BACK', $QUALIFIED_MODULE)}</strong></button>
				<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('NEXT', $QUALIFIED_MODULE)}</strong></button>
				<a class="cancelLink btn btn-warning" href="index.php?module=DataAccess&parent=Settings&view=Index">{\App\Language::translate('CANCEL', $QUALIFIED_MODULE)}</a>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
</form>
<div id="condition_list" style="display: none;">{\App\Json::encode($CONDITION_BY_TYPE)}</div>
