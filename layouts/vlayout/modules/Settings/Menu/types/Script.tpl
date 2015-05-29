<br />
<div class="row">
	<div class="col-md-5 marginLeftZero">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="label" style="width: 90%;" class="" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="row">
	<div class="col-md-5 marginLeftZero">{vtranslate('LBL_JAVASCRIPT', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<textarea name="dataurl" style="width: 94%;">{if $RECORD}{$RECORD->get('dataurl')}{else}javascript:{/if}</textarea>
	</div>
</div>
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
