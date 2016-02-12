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
