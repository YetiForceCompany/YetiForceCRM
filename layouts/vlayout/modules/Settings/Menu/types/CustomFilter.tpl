<input type="hidden" name="module" value="">
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select name="dataurl" class="select2 form-control type">
			{foreach from=$MODULE_MODEL->getCustomViewList() item=ITEM}
				<option value="{$ITEM.cvid}" {if $RECORD && $ITEM['cvid'] == $RECORD->get('dataurl')} selected="" {/if} data-tabid="{$ITEM['tabid']}">{vtranslate($ITEM['entitytype'], $ITEM['entitytype'])}: {vtranslate($ITEM['viewname'], $ITEM['entitytype'])}</option>
			{/foreach}
		</select>
	</div>
</div>
{include file='fields/Newwindow.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_ICON_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="icon" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('icon')}{/if}"/>
	</div>
</div>
