<br />
<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<input name="label" style="width: 90%;" class="" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>