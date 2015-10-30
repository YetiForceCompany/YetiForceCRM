<div class="row marginBottom5">
	<div class="col-md-5">{vtranslate('LBL_HOTKEY', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<div class="input-group">
			<input name="hotkey" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('hotkey')}{/if}"/>
			<a class="input-group-addon testBtn">{vtranslate('LBL_TEST_IT', $QUALIFIED_MODULE)}</a>
			<a class="input-group-addon popoverTooltip" target="_blank" href="https://github.com/ccampbell/mousetrap" data-toggle="popover" 
				data-content="{vtranslate('LBL_MORE_INFO', $QUALIFIED_MODULE)}">
				<i class="glyphicon glyphicon-info-sign"></i>
			</a>
		</div>
	</div>
</div>
