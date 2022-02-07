{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Comment -->
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
				data-validation-engine="validate[required]" />
		</div>
	</div>
	<div class="form-group row align-items-center">
		<div class="col-md-4 col-form-label text-right">
			{\App\Language::translate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}:
		</div>
		<div class="col-md-7">
			<select class='form-control select2' name="displayType" data-validation-engine="validate[required]">
				{foreach from=$FIELD_INSTANCE->displayTypeBase() item=ITEM key=KEY}
					<option value="{$ITEM}" {if $ITEM eq $FIELD_INSTANCE->get('displayType')} selected {/if}>
						{\App\Language::translate($KEY, $QUALIFIED_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input value='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INSTANCE->getParams()))}'
				type="hidden"
				id="params" />
			<div class="form-group paramsJson">
				<div class="form-group row">
					<div class="col-md-4 col-form-label text-right">
						{\App\Language::translate('LBL_COLSPAN', $QUALIFIED_MODULE)}:
					</div>
					<div class="col-md-7">
						<div class=" input-group">
							<input name="width" value="{$FIELD_INSTANCE->getWidth()}" type="text" class="form-control"
								data-validation-engine="validate[required, custom[integer]]" />
							<div class="input-group-append">
								<div class="input-group-text js-popover-tooltip u-cursor-pointer" data-js="popover"
									data-placement="top"
									data-content="{App\Language::translate('LBL_MAX_WIDTH_COLUMN_INFO', $QUALIFIED_MODULE)}">
									<span class="fas fa-info-circle"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group paramsJson">
				<div class="form-group row">
					<div class="col-md-4 col-form-label text-right">
						{\App\Language::translate('LBL_HEIGHT', $QUALIFIED_MODULE)}:
					</div>
					<div class="col-md-7">
						<div class=" input-group">
							<input name="height" value="{$FIELD_INSTANCE->getHeight()}" type="text" class="form-control"
								data-validation-engine="validate[required, custom[integer]]" />
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Comment -->
{/strip}
