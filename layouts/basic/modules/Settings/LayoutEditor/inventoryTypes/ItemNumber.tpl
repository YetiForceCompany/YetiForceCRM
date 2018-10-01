{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-LayoutEditor-inventoryTypes-ItemNumber form-group row">
		<div class="col-md-4 col-form-label text-right">
			{\App\Language::translate('LBL_COLSPAN', $QUALIFIED_MODULE)}:
		</div>
		<div class="col-md-7">
			<input name="colSpan" value="{$FIELD_INSTANCE->getColSpan()}" type="text" class="form-control"
				   data-validation-engine="validate[required, custom[integer]]"/>
		</div>
	</div>
{/strip}
