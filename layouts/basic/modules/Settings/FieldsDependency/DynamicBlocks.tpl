{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-FieldsDependency-DynamicBlocks -->
	<div class="form-group row">
		<label for="inputFields" class="col-sm-3 col-form-label text-right">
			<span class="redColor">*</span>{\App\Language::translate('LBL_FIELDS',$QUALIFIED_MODULE)}
			<a href="#" class="js-popover-tooltip ml-2" data-placement="top" data-content="{\App\Language::translate('LBL_FIELDS_INFO', $QUALIFIED_MODULE)}">
				<i class="fas fa-info-circle"></i>
			</a>
		</label>
		<div class="col-sm-9">
			<select name="fields[]" class="select2 form-control" id="inputFields" multiple="multiple" data-validation-engine="validate[required]">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label="{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							<option value="{$FIELD_NAME}" {if in_array($FIELD_NAME,$FIELDS)}selected="selected" {/if}>{$FIELD_MODEL->getFullLabelTranslation()}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
	</div>
	<p class="border-top mb-1">
		{\App\Language::translate('LBL_CONDITIONS', $QUALIFIED_MODULE)}:
	</p>
	<div class="js-condition-builder-view" data-js="container">
		{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', $QUALIFIED_MODULE)}
	</div>
	<!-- /tpl-Settings-FieldsDependency-DynamicBlocks -->
{/strip}
