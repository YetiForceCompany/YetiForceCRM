<div class="row-fluid">
	<div class="span5 marginLeftZero">{vtranslate('LBL_HOTKEY', $QUALIFIED_MODULE)}:</div>
	<div class="span7">
		<div class="input-append">
			<input name="hotkey" style="width: 55%;" class="" type="text" value="{if $RECORD}{$RECORD->get('hotkey')}{/if}"/>
			<a class="btn testBtn">{vtranslate('LBL_TEST_IT', $QUALIFIED_MODULE)}</a>
			<a class="btn" target="_blank" href="https://github.com/ccampbell/mousetrap"><i class="icon-info-sign"></i></a>
		</div>
	</div>
</div>