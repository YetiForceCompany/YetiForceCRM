<br />
<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<input name="label" style="width: 90%;" class="" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" />
	</div>
</div>
<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<select name="module" class="select2 type" style="width: 70%;">
			{foreach from=$MODULE_MODEL->getModulesList() item=ITEM}
				<option value="{$ITEM['tabid']}" {if $RECORD && $ITEM['tabid'] == $RECORD->get('module')} selected="" {/if}>{vtranslate($ITEM['name'], $ITEM['name'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<br />
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
