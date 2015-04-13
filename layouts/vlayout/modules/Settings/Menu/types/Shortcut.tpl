<br />
<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<input name="label" style="width: 90%;" class="" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_URL', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<input name="dataurl" style="width: 90%;" class="" type="text" value="{if $RECORD}{$RECORD->get('dataurl')}{/if}" placeholder="https://yetiforce.com" data-validation-engine="validate[custom[url]]" />
	</div>
</div>
{include file='fields/Newwindow.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
<br />
{include file='fields/Hotkey.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
