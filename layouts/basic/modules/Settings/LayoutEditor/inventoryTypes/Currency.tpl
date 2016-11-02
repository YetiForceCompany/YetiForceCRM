{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		{assign var='LABEL' value=$FIELD_INSTANCE->getDefaultLabel()}
		{if $FIELD_INSTANCE->get('label') }
			{assign var='LABEL' value=$FIELD_INSTANCE->get('label')}
		{/if}
		<input name="label" class="form-control" type="text" value="{$LABEL}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select class='form-control select2' name="displayType" data-validation-engine="validate[required]">
			{foreach from=$FIELD_INSTANCE->displayTypeBase() item=ITEM key=KEY}
				<option value="{$ITEM}" {if $ITEM eq $FIELD_INSTANCE->get('displaytype')} selected {/if}>{vtranslate($KEY, $QUALIFIED_MODULE)}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_COLSPAN', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="colSpan" class="form-control" type="text" value="{$FIELD_INSTANCE->getColSpan()}" data-validation-engine="validate[required]" />
	</div>
</div>
{if $FIELD_INSTANCE->getParams()}
	<div class="paramsJson">
		<input id="params" class="" type="hidden" value='{\App\Json::encode($FIELD_INSTANCE->getParams())}'/>
		{assign var='PARAMS' value=\App\Json::decode($FIELD_INSTANCE->get('params'))}
		{foreach from=$FIELD_INSTANCE->getParams() item=ITEM key=KEY}
			<div class="form-group paramsJson">
				<label class="col-md-4 control-label">{vtranslate('LBL_PARAMS_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}:</label>
				{assign var='functionName' value=$ITEM|cat:'Values'}
				<div class="col-md-7">
					<select class='form-control select2' name="{$ITEM}" data-validation-engine="validate[required]" {if $ITEM eq 'modules'} multiple {/if}>
						{foreach from=$FIELD_INSTANCE->$functionName() item=ITEMS key=KEY}
							{assign var='CONDITION' value=0}
							{if $PARAMS[$ITEM]|is_array && in_array($ITEMS.id,$PARAMS[$ITEM])}
								{assign var='CONDITION' value=1}
							{elseif !($PARAMS[$ITEM]|is_array) && $ITEMS.id eq $PARAMS[$ITEM]}
								{assign var='CONDITION' value=1}
							{/if}
							<option value="{$ITEMS.id}" {if $CONDITION} selected {/if}>{if $ITEM eq 'type'}{vtranslate($ITEMS.currency_name, $ITEMS.module)}{else}{vtranslate($ITEMS.name, $ITEMS.module)}{/if}</option>
						{/foreach}
					</select>
				</div>
			</div>
		{/foreach}
	</div>
{/if}
