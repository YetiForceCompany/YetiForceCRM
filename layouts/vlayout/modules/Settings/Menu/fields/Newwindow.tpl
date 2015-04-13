<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_NEW_WINDOW', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<input name="newwindow" style="width: 70%;" class="" type="checkbox" value="1" {if $RECORD && $RECORD->get('newwindow') eq 1} checked="checked" {/if}/>
	</div>
</div>