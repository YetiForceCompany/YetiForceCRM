<div class="row marginBottom5">
	<div class="col-md-5">{vtranslate('LBL_NEW_WINDOW', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<input name="newwindow" type="checkbox" value="1" {if $RECORD && $RECORD->get('newwindow') eq 1} checked="checked" {/if}/>
	</div>
</div>
