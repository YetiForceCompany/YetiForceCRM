{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		{assign var='LABEL' value=$MODULE_MODEL->getDefaultLabel()}
		{if $MODULE_MODEL->get('label') }
			{assign var='LABEL' value=$MODULE_MODEL->get('label')}
		{/if}
		<input name="label" class="form-control" type="text" value="{$LABEL}" data-validation-engine="validate[required]" />
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_DISPLAY_TYPE', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<select class='form-control select2' name="displayType" data-validation-engine="validate[required]">
			{foreach from=$MODULE_MODEL->displayTypeBase() item=ITEM key=KEY}
				<option value="{$ITEM}" {if $ITEM eq $MODULE_MODEL->get('displaytype')} selected {/if}>{vtranslate($KEY, $QUALIFIED_MODULE)}</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<label class="col-md-4 control-label">{vtranslate('LBL_COLSPAN', $QUALIFIED_MODULE)}:</label>
	<div class="col-md-7">
		<input name="colSpan" class="form-control" type="text" value="{$MODULE_MODEL->getColSpan()}" data-validation-engine="validate[required]" />
	</div>
</div>
