{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Reference -->
	<div class="form-group row align-items-center m-0">
		<div class="checkbox col-md-4  col-form-label text-right">
			<label for="mandatory" class="ml-1">
				{App\Language::translate('LBL_MANDATORY_FIELD', $QUALIFIED_MODULE)}
			</label>
		</div>
		<div class="col-md-7 align-items-center ">
			<input type="hidden" name="mandatory" value="false"/>
			<input type="checkbox" {if $FIELD_INSTANCE->isMandatory() eq 'true'} checked {/if} name="mandatory"
				   id="mandatory"
				   value="true"/>
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('inventoryTypes/Base.tpl', $QUALIFIED_MODULE)}
	{if $FIELD_INSTANCE->getParams()}
		<div class="paramsJson">
			<input value='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INSTANCE->getParams()))}' type="hidden"
				   id="params"/>
			{assign var='PARAMS' value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
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
								{if $PARAMS[$ITEM]|is_array && in_array($ITEMS.id,$PARAMS[$ITEM])}
									{assign var='CONDITION' value=1}
								{elseif !($PARAMS[$ITEM]|is_array) && $ITEMS.id eq $PARAMS[$ITEM]}
									{assign var='CONDITION' value=1}
								{/if}
								<option value="{$ITEMS.id}" {if $CONDITION} selected {/if}>
									{\App\Language::translate($ITEMS.name, $ITEMS.module)}
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
