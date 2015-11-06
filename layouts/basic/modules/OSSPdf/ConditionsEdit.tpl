{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>{vtranslate('LBL_CONDITIONS_INFO',$MODULE)}</div>
<h5 class="padding-bottom1per"><strong>{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}</strong></h5>
<div class="allConditionContainer conditionGroup contentsBackground well">
	<div class="header"><span><strong>{vtranslate('LBL_CONDITION_ALL', $MODULE)}</strong></span> - <span>{vtranslate('LBL_CONDITION_ALL_DSC', $MODULE)}</span></div>
	<div id="condition_all">
			{foreach from=$REQUIRED_CONDITIONS key=cnd_key item=cnd_item name=field_select}
				<div class="row conditionRow marginBottom10px" id="cnd_num_{$smarty.foreach.field_select.index}">
					<div class="col-md-4">
						<select data-num="{$smarty.foreach.field_select.index}" class="select2 chzn-select chzn-done row field-select form-control" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
							{foreach key=FIELD_MODULE item=FIELD from=$FIELD_LIST}
								<optgroup label='{vtranslate($FIELD_MODULE, $FIELD_MODULE)}'>
									{foreach from=$FIELD key=key item=item}
										<option data-module="{$FIELD_MODULE}" value="{$item['name']}" {if $cnd_item['fieldname'] eq $item['name']}selected{/if}
												data-uitype="{$item['uitype']}" data-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($item['info']))}"
												>{vtranslate($item['label'], $BASE_MODULE)}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
					<div class="col-md-3">
						<select data-num="{$smarty.foreach.field_select.index}" class="select2 row form-control" name="comparator">
							{assign var=CONDITION_LIST value=Conditions::getConditionByType($cnd_item['field_type'])}
							{foreach from=$CONDITION_LIST item=item key=key}
								<option value="{$item}" {if $cnd_item['comparator'] eq $item}selected{/if}>{Conditions::translateType($item,$MODULE)}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-4 fieldUiHolder">
{*                                    {var_dump($cnd_item)}*}
						{if $cnd_item['field_type'] eq 'picklist'}
							<select name="val" data-value=value" class="row select2 form-control">
								{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
									<option value="{$pick_key}" {if $cnd_item['val'] eq $pick_key}selected{/if}>{$pick_item}</option>
								{/foreach}
							</select>
						{else if $cnd_item['field_type'] eq 'multipicklist'}
							<select multiple="multiple" name="val" data-value="value" class="row select2 form-control">
								{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
									<option value="{$pick_key}"  {if in_array($pick_key, $cnd_item['val'])} selected {/if}>{$pick_item}</option>
								{/foreach}
							</select>
						{else if $cnd_item['field_type'] eq 'time'}
							<div class="input-group time"><input type="text" data-format="24" value="{$cnd_item['val']}" class="timepicker-default form-control input-sm ui-timepicker-input" name="val" autocomplete="off"><span class="input-group-addon cursorPointer"><i class="glyphicon glyphicon-time"></i></span></div>
						{else if $cnd_item['field_type'] eq 'date'}
							{if $cnd_item['comparator'] == 'between'}
								<div class="date"><input class="dateField bw row form-control" data-calendar-type="range" name="val" data-date-format="yyyy-mm-dd" type="text" readonly="true" value="{$cnd_item['val']|escape}" data-value="value"></div>
								{else if in_array($cnd_item['comparator'], array("less than days ago", "more than days ago", "in less than", "in more than", "days ago", "days later"))}
								<input name="val" data-value="value" class="row form-control" type="text" value="{$cnd_item['val']|escape}" />
							{else}
								<div class="input-group row"><input class="col-md-9 dateField form-control dateFieldNormal" value="{$cnd_item['val']|escape}" name="val" data-date-format="yyyy-mm-dd"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span></div>
							{/if}
						{else}
						<input name="val" data-value="value" class="row form-control " type="text" value="{$cnd_item['val']|escape}" />
						{/if}
					</div>
					<div class="col-md-1">
						<i class="deleteCondition glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" onclick="jQuery(this).parents('div#cnd_num_{$smarty.foreach.field_select.index}').remove()"></i>
					</div>
				</div>
			{/foreach}
	</div>

	<div class="addCondition"><button class="add_condition btn btn-default" data-type="condition_all" type="button"><strong>{vtranslate('ADD_CONDITIONS', $MODULE)}</strong></button></div>
</div>
<div class="allConditionContainer conditionGroup contentsBackground well">
	<div class="header"><span><strong>{vtranslate('LBL_CONDITION_OPTION', $MODULE)}</strong></span> - <span>{vtranslate('LBL_CONDITION_OPTION_DSC', $MODULE)}</span></div>
	<div id="condition_option">
			{foreach from=$OPTIONAL_CONDITIONS key=cnd_key item=cnd_item name=field_select}
				<div class="row conditionRow marginBottom10px" id="cnd_num_{$smarty.foreach.field_select.index}">
					<span class="col-md-4">
						<select data-num="{$smarty.foreach.field_select.index}" class="select2 chzn-select chzn-done row field-select form-control"data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
							{foreach key=FIELD_MODULE item=FIELD from=$FIELD_LIST}
								<optgroup label='{vtranslate($FIELD_MODULE, $FIELD_MODULE)}'>
									{foreach from=$FIELD key=key item=item}
										<option data-module="{$FIELD_MODULE}" value="{$item['name']}" {if $cnd_item['fieldname'] eq $item['name']}selected{/if}
												data-uitype="{$item['uitype']}" data-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($item['info']))}"
												>{vtranslate($item['label'], $BASE_MODULE)}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</span>
					<span class="col-md-3">
						<select data-num="{$smarty.foreach.field_select.index}" class="select2 row form-control" name="comparator">
							{assign var=CONDITION_LIST value=Conditions::getConditionByType($cnd_item['field_type'])}
							{foreach from=$CONDITION_LIST item=item key=key}
								<option value="{$item}" {if $cnd_item['comparator'] eq $item}selected{/if}>{Conditions::translateType($item,$MODULE)}</option>
							{/foreach}
						</select>
					</span>
					<span class="col-md-4 fieldUiHolder">
{*                                    {var_dump($cnd_item)}*}
						{if $cnd_item['field_type'] eq 'picklist'}
							<select name="val" data-value=value" class="row select2 form-control">
								{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
									<option value="{$pick_key}" {if $cnd_item['val'] eq $pick_key}selected{/if}>{$pick_item}</option>
								{/foreach}
							</select>
						{else if $cnd_item['field_type'] eq 'multipicklist'}
							<select multiple="multiple" name="val" data-value="value" class="row select2 form-control">
								{foreach from=$cnd_item['info']['picklistvalues'] key=pick_key item=pick_item}
									<option value="{$pick_key}"  {if in_array($pick_key, $cnd_item['val'])} selected {/if}>{$pick_item}</option>
								{/foreach}
							</select>
						{else if $cnd_item['field_type'] eq 'time'}
							<div class="input-group time"><input type="text" data-format="24" value="{$cnd_item['val']}" class="timepicker-default form-control input-sm ui-timepicker-input" name="val" autocomplete="off"><span class="input-group-addon cursorPointer"><i class="glyphicon glyphicon-time"></i></span></div>
						{else if $cnd_item['field_type'] eq 'date'}
							{if $cnd_item['comparator'] == 'between'}
								<div class="date"><input class="dateField bw row form-control" data-calendar-type="range" name="val" data-date-format="yyyy-mm-dd" type="text" readonly="true" value="{$cnd_item['val']|escape}" data-value="value"></div>
								{else if in_array($cnd_item['comparator'], array("less than days ago", "more than days ago", "in less than", "in more than", "days ago", "days later"))}
								<input name="val" data-value="value" class="row form-control" type="text" value="{$cnd_item['val']|escape}" />
							{else}
								<div class="input-group row"><input class="col-md-9 dateField form-control dateFieldNormal" value="{$cnd_item['val']|escape}" name="val" data-date-format="yyyy-mm-dd"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span></div>
							{/if}
						{else}
						<input name="val" data-value="value" class="row form-control" type="text" value="{$cnd_item['val']|escape}" />
						{/if}
					</span>
					<span class="col-md-1">
						<i class="deleteCondition glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" onclick="jQuery(this).parents('div#cnd_num_{$smarty.foreach.field_select.index}').remove()"></i>
					</span>
				</div>
			{/foreach}
	</div>
	<div class="addCondition"><button class="add_condition btn btn-default" data-type="condition_option" type="button"><strong>{vtranslate('ADD_CONDITIONS', $MODULE)}</strong></button></div>
</div>
</div>
<input type="hidden" name="condition_all_json" value="" />
<input type="hidden" name="condition_option_json" value="" />
<div id="condition_list" style="display: none;">{ZEND_JSON::encode($CONDITION_BY_TYPE)}</div>
