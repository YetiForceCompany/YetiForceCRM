{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_HOTKEY', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<div class="input-group">
			<input name="hotkey" class="form-control" type="text" value="{if $RECORD}{$RECORD->get('hotkey')}{/if}" />
			<div class="input-group-append">
				<a class="btn btn-default testBtn" role="button" data-toggle="button">{\App\Language::translate('LBL_TEST_IT', $QUALIFIED_MODULE)}</a>
			</div>
			<a class="input-group-append js-popover-tooltip" data-js="popover" target="_blank" href="https://github.com/ccampbell/mousetrap"
				rel="noreferrer noopener" data-toggle="popover"
				data-content="{\App\Language::translate('LBL_MORE_INFO', $QUALIFIED_MODULE)}">
				<div class="input-group-text">
					<span class="fas fa-info-circle"></span>
				</div>
			</a>
		</div>
	</div>
</div>
