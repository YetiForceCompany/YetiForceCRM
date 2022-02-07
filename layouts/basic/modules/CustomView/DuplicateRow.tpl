{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-CustomView-DuplicateRow d-flex my-1">
		<div class="col-5">
			<select class="form-control js-duplicates-field" data-js="select2|val">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label="{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							<option value="{$FIELD_MODEL->getId()}" {if isset($FIELD['fieldid']) && $FIELD['fieldid'] eq $FIELD_MODEL->getId()} selected="selected" {/if}>
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
		<div class="col-5 mt-2">
			<label>
				{\App\Language::translate('LBL_IGNORE_EMPTY_VALUES',$MODULE_NAME)}
				<input type="checkbox" class="ml-2 js-duplicates-ignore" data-js="is"
					{if isset($FIELD['ignore']) && $FIELD['ignore']}checked{/if}>
			</label>
		</div>
		<div class="col-2">
			<button type="button" class="btn btn-sm btn-danger js-duplicates-remove" data-js="click">
				<span class="fa fa-trash"></span>
			</button>
		</div>
	</div>
{/strip}
