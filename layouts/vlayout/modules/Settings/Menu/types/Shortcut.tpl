<br />
<div class="row marginBottom5">
	<div class="col-md-5 text-right">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="row marginBottom5">
	<div class="col-md-5 text-right">{vtranslate('LBL_URL', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="dataurl" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('dataurl')}{/if}" placeholder="https://yetiforce.com" data-validation-engine="validate[custom[url]]" />
	</div>
</div>
{include file='fields/Newwindow.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
<br />
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
