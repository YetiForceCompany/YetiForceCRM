<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_URL', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="dataurl" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('dataurl')}{/if}" placeholder="https://yetiforce.com" data-validation-engine="validate[custom[url]]" />
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
