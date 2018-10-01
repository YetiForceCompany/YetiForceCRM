{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Name -->
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input type="hidden" value='{\App\Json::encode($FIELD_INSTANCE->getParams())}' id="params"/>
			{assign var='PARAMS' value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
			{foreach from=$FIELD_INSTANCE->getParams() item=ITEM key=KEY}
				<div class="form-group paramsJson">
					<div class="col-md-7 col-form-label text-left">
						{\App\Language::translate('LBL_PARAMS_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}
						{if $ITEM eq 'limit'}
						{assign var="GROSS_PRICE" value=Vtiger_InventoryField_Model::getFieldInstance($MODULE, 'GrossPrice')}
						<a href="#" class="js-help-info" data-placement="top"
						   data-content="{\App\Language::translate('LBL_PARAMS_LIMIT_CONDITIONS', $QUALIFIED_MODULE)}: {\App\Language::translate($GROSS_PRICE->getDefaultLabel(), $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</a>
						{/if}:
					</div>
					{assign var='functionName' value=$ITEM|cat:'Values'}
					<div class="col-md-7">
						<select class="form-control select2"
								name="{$ITEM}" {if $ITEM eq 'modules'} data-validation-engine="validate[required]" multiple {/if}>
							{foreach from=$FIELD_INSTANCE->$functionName() item=ITEMS key=KEY}
								{assign var='CONDITION' value=0}
								{if $PARAMS[$ITEM]|is_array && in_array($ITEMS.id,$PARAMS[$ITEM])}
									{assign var='CONDITION' value=1}
								{elseif !($PARAMS[$ITEM]|is_array) && $ITEMS.id eq $PARAMS[$ITEM]}
									{assign var='CONDITION' value=1}
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
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Name -->
{/strip}
