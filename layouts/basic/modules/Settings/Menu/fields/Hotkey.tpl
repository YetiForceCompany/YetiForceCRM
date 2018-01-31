{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div class="form-group">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_HOTKEY', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<div class="input-group">
			<input name="hotkey" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('hotkey')}{/if}" />
			<a class="input-group-addon testBtn">{\App\Language::translate('LBL_TEST_IT', $QUALIFIED_MODULE)}</a>
			<a class="input-group-addon popoverTooltip" target="_blank" href="https://github.com/ccampbell/mousetrap" rel="noreferrer" data-toggle="popover" 
				data-content="{\App\Language::translate('LBL_MORE_INFO', $QUALIFIED_MODULE)}">
				<i class="fa fa-info-circle"></i>
			</a>
		</div>
	</div>
</div>
