{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Reference -->
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input value='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INSTANCE->getParams()))}' type="hidden" id="params" />
			{if $FIELD_INSTANCE->get('params')}
				{assign var=PARAMS value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
			{else}
				{assign var=PARAMS value=[]}
			{/if}
			{foreach from=$FIELD_INSTANCE->getParams() item=ITEM key=KEY}
				<div class="form-group row align-items-center">
					<div class="col-md-4 col-form-label text-right">
						{\App\Language::translate('LBL_PARAMS_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}:
					</div>
					{assign var='functionName' value=$ITEM|cat:'Values'}
					<div class="col-md-7">
						<select class='form-control select2' name="{$ITEM}"
							data-validation-engine="validate[required]" {if $ITEM eq 'modules'} multiple="multiple" {/if}>
							{foreach from=$FIELD_INSTANCE->$functionName() item=ITEMS key=KEY}
								{assign var='CONDITION' value=0}
								{if isset($PARAMS[$ITEM])}
									{if is_array($PARAMS[$ITEM]) && in_array($ITEMS.id,$PARAMS[$ITEM])}
										{assign var='CONDITION' value=1}
									{elseif !is_array($PARAMS[$ITEM]) && $ITEMS.id eq $PARAMS[$ITEM]}
										{assign var='CONDITION' value=1}
									{/if}
								{/if}
								<option value="{$ITEMS.id}" {if $CONDITION} selected {/if}>
									{if isset($ITEMS.module)}
										{\App\Language::translate($ITEMS['name'], $ITEMS.module)}
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
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Reference -->
{/strip}
