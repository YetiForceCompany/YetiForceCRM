{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<div class="row conditionRow marginBottom10px" id="cnd_num_{$NUM}">
	{assign var=CONDITION_LIST value=''}
	<span class="col-md-4">
        <select data-num="{$NUM}" class="chzn-select form-control marginBottom5px comparator-select" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
            {foreach key=MODULE_NAME item=FIELD from=$FIELD_LIST}
				{$CONDITION_LIST = Settings_DataAccess_Module_Model::getConditionByType($FIELD[0]['info']['type'])}
                <optgroup label='{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}'>
                    {foreach from=$FIELD key=key item=item}
                        <option data-module="{$MODULE_NAME}" value="{$item['name']}" data-uitype="{$item['uitype']}" 
                                data-info="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($item['info']))}">{\App\Language::translate($item['label'], $BASE_MODULE)}</option>
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
    </span>
    <span class="col-md-3">
        <select data-num="{$NUM}" class="chzn-select form-control marginBottom5px" name="comparator">
            {foreach from=$CONDITION_LIST item=item key=key}
                <option value="{$item}">{\App\Language::translate($item,$QUALIFIED_MODULE)}</option>
            {/foreach}
        </select>
    </span>
    <span class="col-md-4 fieldUiHolder">
        <input name="val" data-value="value" class="form-control" type="text" value="{$CONDITION_INFO['value']|escape}" />
    </span>
    <span class="col-md-1 form-control-static">
        <i class="deleteCondition glyphicon glyphicon-trash alignMiddle" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" onclick="jQuery(this).parents('div#cnd_num_{$NUM}').remove()"></i>
    </span>
</div>
