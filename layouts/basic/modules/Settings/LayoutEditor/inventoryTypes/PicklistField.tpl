{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-PicklistField -->
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input value='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INSTANCE->getParams()))}' type="hidden"
				id="params" />
			{assign var=PARAMS value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
			{foreach from=$FIELD_INSTANCE->getParams() item=MODULE}
				<div class="form-group row align-items-center">
					<div class="col-md-4 col-form-label text-right">
						{\App\Language::translate($MODULE, $QUALIFIED_MODULE)}:
					</div>
					<div class="col-md-7">
						<select class="form-control select2" name="{$MODULE}"
							data-validation-engine="validate[required]">
							{foreach from=$FIELD_INSTANCE->getPicklist($MODULE) item=NAME key=VALUE}
								<option value="{$VALUE}" {if $PARAMS[$MODULE] == $VALUE} selected {/if}>{$NAME}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-PicklistField -->
{/strip}
