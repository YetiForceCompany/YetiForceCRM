{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-Menu-fields-ShowNumberEntries -->
<div class="form-group row">
	<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SHOW_NUMBER_ENTRIES', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7 checkboxForm">
		<input name="showentries" type="checkbox" value="1" {if $RECORD && $RECORD->get('showentries') eq 1} checked="checked" {/if} />
	</div>
</div>
<!-- /tpl-Settings-Menu-fields-ShowNumberEntries -->
