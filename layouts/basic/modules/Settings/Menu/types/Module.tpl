<div class="form-group">
	<label class="col-md-5 control-label">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select name="module" class="select2 type form-control">
			{foreach from=$MODULE_MODEL->getModulesList() item=ITEM}
				<option value="{$ITEM['tabid']}" {if $RECORD && $ITEM['tabid'] == $RECORD->get('module')} selected="" {/if}>{vtranslate($ITEM['name'], $ITEM['name'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<div class="col-md-5">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
{include file='fields/Newwindow.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{assign var=FILTERS value=explode(',',$RECORD->get('filters'))}
<div class="form-group">
	<div class="col-md-5">{vtranslate('LBL_AVAILABLE_FILTERS', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<select name="filters" multiple class="select2 type form-control">
			{foreach from=$MODULE_MODEL->getCustomViewList() item=ITEM}
				<option value="{$ITEM.cvid}" {if $RECORD && in_array($ITEM['cvid'],$FILTERS)} selected="" {/if} data-tabid="{$ITEM['tabid']}">{vtranslate($ITEM['viewname'], $ITEM['entitytype'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<label class="col-md-5 control-label">{vtranslate('LBL_ICON_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="icon" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('icon')}{/if}"/>
	</div>
</div>
