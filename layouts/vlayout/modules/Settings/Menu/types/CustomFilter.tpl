<br />
<input type="hidden" name="module" value="">
<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<select name="dataurl" class="select2 type" style="width: 70%;">
			{foreach from=$MODULE_MODEL->getCustomViewList() item=ITEM}
				<option value="{$ITEM.cvid}" {if $RECORD && $ITEM['cvid'] == $RECORD->get('dataurl')} selected="" {/if} data-tabid="{$ITEM['tabid']}">{vtranslate($ITEM['entitytype'], $ITEM['entitytype'])}: {vtranslate($ITEM['viewname'], $ITEM['entitytype'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<br />
{include file='fields/Newwindow.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
<br />
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
