{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Picklist -->
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	<div class="form-group row align-items-center">
		<input value='["values"]' type="hidden" id="params">
		<div class="col-md-4 col-form-label text-right">
			{\App\Language::translate('LBL_PICKLIST_VALUES', $QUALIFIED_MODULE)}:
		</div>
		<div class="col-md-7">
			<select name="values" class="form-control select2" data-select="tags" multiple="multiple"
				data-validation-engine="validate[required]">
				{foreach from=$FIELD_INSTANCE->getPicklistValues() item=VALUE}
					<option value="{\App\Purifier::encodeHtml($VALUE)}" selected>{\App\Purifier::encodeHtml($VALUE)}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Picklist -->
{/strip}
