{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Base -->
	{if $FIELD_INSTANCE->getName() eq  'Reference'}
		<div class="form-group row align-items-center m-0">
			{assign var=PARAMS value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
			<div class="checkbox col-md-4  col-form-label text-right">
				<label for="mandatory" class="ml-1">
					{App\Language::translate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
				</label>
			</div>
			<div class="col-md-7 align-items-center ">
				<input type="hidden" name="mandatory" value="false"/>
				<input type="checkbox" {if $PARAMS['mandatory'] eq 'true'} checked {/if} name="mandatory"
					   id="mandatory"
					   value="true"/>
			</div>
		</div>
	{/if}
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
