<br />
<div class="row marginBottom5">
	<div class="col-md-5 text-right">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="label" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('label')}{/if}" data-validation-engine="validate[required]" />
	</div>
</div>
