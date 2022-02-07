{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_NEW_WINDOW', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7 checkboxForm">
		<input name="newwindow" type="checkbox" value="1" {if $RECORD && $RECORD->get('newwindow') eq 1} checked="checked" {/if} />
	</div>
</div>
