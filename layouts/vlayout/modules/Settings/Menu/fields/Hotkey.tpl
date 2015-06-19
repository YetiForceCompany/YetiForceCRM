<div class="row">
	<div class="col-md-5 marginLeftZero">{vtranslate('LBL_HOTKEY', $QUALIFIED_MODULE)}:</div>
	<div class="col-md-7">
		<div class="input-group">
			<input name="hotkey" style="width: 55%;" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('hotkey')}{/if}"/>
			<a class="btn btn-default testBtn">{vtranslate('LBL_TEST_IT', $QUALIFIED_MODULE)}</a>
			<a class="btn btn-default" target="_blank" href="https://github.com/ccampbell/mousetrap"><i class="glyphicon glyphicon-info-sign"></i></a>
		</div>
	</div>
</div>
