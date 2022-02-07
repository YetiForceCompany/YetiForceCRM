{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Currency -->
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input value='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INSTANCE->getParams()))}' type="hidden"
				id="params" />
			{assign var='PARAMS' value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
			{foreach from=$FIELD_INSTANCE->getParams() item=ITEM key=KEY}
				<div class="form-group paramsJson row">
					<div class="col-md-4 col-form-label text-right">
						{\App\Language::translate('LBL_PARAMS_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}:
					</div>
					{assign var='functionName' value=$ITEM|cat:'Values'}
					<div class="col-md-7">
						<select class='form-control select2' name="{$ITEM}"
							data-validation-engine="validate[required]" {if $ITEM eq 'modules'} multiple {/if}>
							{foreach from=$FIELD_INSTANCE->$functionName() item=ITEMS key=KEY}
								{assign var='CONDITION' value=0}
								{if $PARAMS[$ITEM]|is_array && in_array($ITEMS.id,$PARAMS[$ITEM])}
									{assign var='CONDITION' value=1}
								{elseif !($PARAMS[$ITEM]|is_array) && $ITEMS.id eq $PARAMS[$ITEM]}
									{assign var='CONDITION' value=1}
								{/if}
								<option value="{$ITEMS.id}" {if $CONDITION} selected {/if}>
									{if $ITEM eq 'type'}{\App\Language::translate($ITEMS.currency_name, $ITEMS.module)}{else}{\App\Language::translate($ITEMS.name, $ITEMS.module)}{/if}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Currency -->
{/strip}
