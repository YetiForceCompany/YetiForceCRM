<br />
<div class="row marginBottom5">
	<div class="col-md-5">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" />
	</div>
</div>
<div class="row marginBottom5">
	<div class="col-md-5">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<select name="module" class="select2 form-control type">
			{foreach from=$MODULE_MODEL->getModulesList() item=ITEM}
				<option value="{$ITEM['tabid']}" {if $RECORD && $ITEM['tabid'] == $RECORD->get('module')} selected="" {/if}>{vtranslate($ITEM['name'], $ITEM['name'])}</option>
			{/foreach}
		</select>
	</div>
</div>
<br />
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
