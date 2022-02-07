{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Name -->
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input value='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INSTANCE->getParams()))}' type="hidden" id="params" />
			{assign var='PARAMS' value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
			{foreach from=$FIELD_INSTANCE->getParams() item=ITEM key=KEY}
				<div class="form-group row align-items-center">
					<div class="col-md-4 col-form-label text-right">
						{\App\Language::translate('LBL_PARAMS_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}:
					</div>
					{assign var='functionName' value=$ITEM|cat:'Values'}
					<div class="col-md-7">
						<div class="input-group">
							<select class="select2"
								name="{$ITEM}" {if $ITEM eq 'modules'} data-validation-engine="validate[required]" multiple {/if}>
								{foreach from=$FIELD_INSTANCE->$functionName() item=ITEMS key=KEY}
									{assign var='CONDITION' value=0}
									{if isset($PARAMS[$ITEM])}
										{if $PARAMS[$ITEM]|is_array && in_array($ITEMS.id,$PARAMS[$ITEM])}
											{assign var='CONDITION' value=1}
										{elseif !($PARAMS[$ITEM]|is_array) && $ITEMS.id eq $PARAMS[$ITEM]}
											{assign var='CONDITION' value=1}
										{/if}
									{/if}
									<option value="{$ITEMS['id']}" {if $CONDITION}selected{/if}>
										{if isset($ITEMS['module'])}
											{\App\Language::translate($ITEMS['name'], $ITEMS['module'])}
										{else}
											{\App\Language::translate($ITEMS['name'], $QUALIFIED_MODULE)}
										{/if}
									</option>
								{/foreach}
							</select>
							{if $ITEM eq 'limit'}
								<div class="input-group-append">
									{assign var="GROSS_PRICE" value=$INVENTORY_MODEL->getFieldCleanInstance('GrossPrice')}
									<div class="input-group-text js-popover-tooltip u-cursor-pointer" data-js="popover" data-placement="top"
										data-content="{\App\Language::translate('LBL_PARAMS_LIMIT_CONDITIONS', $QUALIFIED_MODULE)}: {\App\Language::translate($GROSS_PRICE->getDefaultLabel(), $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								</div>
							{/if}
							{if $ITEM eq 'mandatory'}
								<div class="input-group-append">
									<div class="input-group-text js-popover-tooltip u-cursor-pointer" data-js="popover" data-placement="top"
										data-content="{\App\Language::translate('LBL_EDIT_MANDATORY_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								</div>
							{/if}
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Name -->
{/strip}
