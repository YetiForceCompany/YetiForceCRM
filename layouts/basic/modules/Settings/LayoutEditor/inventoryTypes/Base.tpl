{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Base -->
	<div class="form-group row align-items-center">
		<div class="col-md-4 col-form-label text-right">
			{\App\Language::translate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:
		</div>
		<div class="col-md-7">
			{assign var='LABEL' value=$FIELD_INSTANCE->getDefaultLabel()}
			{if $FIELD_INSTANCE->get('label') }
				{assign var='LABEL' value=$FIELD_INSTANCE->get('label')}
			{/if}
			<input name="label" value="{$LABEL}" type="text" class="form-control"
				   data-validation-engine="validate[required]"/>
		</div>
	</div>
	<div class="form-group row align-items-center">
		<div class="col-md-4 col-form-label text-right">
			{\App\Language::translate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}:
		</div>
		<div class="col-md-7">
			<select class='form-control select2' name="displayType" data-validation-engine="validate[required]">
				{foreach from=$FIELD_INSTANCE->displayTypeBase() item=ITEM key=KEY}
					<option value="{$ITEM}" {if $ITEM eq $FIELD_INSTANCE->get('displaytype')} selected {/if}>
						{\App\Language::translate($KEY, $QUALIFIED_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-md-4 col-form-label text-right">
			{\App\Language::translate('LBL_COLSPAN', $QUALIFIED_MODULE)}:
		</div>
		<div class="col-md-7">
			<input name="colSpan" value="{$FIELD_INSTANCE->getColSpan()}" type="text" class="form-control"
				   data-validation-engine="validate[required, custom[integer]]"/>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Base -->
{/strip}
