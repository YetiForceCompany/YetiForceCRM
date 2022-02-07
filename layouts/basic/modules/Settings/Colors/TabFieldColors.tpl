{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Colors-TabFieldColors">
		<div class="form-row">
			<label class="fieldLabel col-md-2"><strong>{\App\Language::translate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
			<div class="col-md-4 fieldValue pickListModulesSelectContainer">
				<select class="select2 form-control js-selected-module" data-js="change">
					<optgroup>
						<option value="">{\App\Language::translate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
						{foreach item=$MODULE from=$ALL_ACTIVE_MODULES}
							<option {if $SELECTED_MODULE_NAME eq $MODULE['name']} selected="" {/if} value="{$MODULE['name']}">{\App\Language::translate($MODULE['name'], $MODULE['name'])}</option>
						{/foreach}
					</optgroup>
				</select>
			</div>
		</div>
		{if $SELECTED_MODULE_NAME && $SELECTED_MODULE_FIELDS}
			<div class="mt-3">
				<table class="table table-bordered table-sm listViewEntriesTable">
					<thead>
						<tr class="blockHeader">
							<th><strong>{\App\Language::translate('LBL_FIELD',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$SELECTED_MODULE_FIELDS item=$FIELD}
							{assign var=FIELD_ID value=$FIELD->getId()}
							<tr data-id="{$FIELD_ID}" data-color="{$FIELD->get('color')}">
								<td>{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE_NAME)}</td>
								<td class="js-color-preview" data-js="container" data-color="{$FIELD->get('color')}" data-field-id="{$FIELD_ID}" style="background: {$FIELD->get('color')};"></td>
								<td>
									<button data-field-id="{$FIELD_ID}"
										class="btn btn-sm btn-danger mr-1 float-right js-remove-color" data-js="click"><span
											class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-field-id="{$FIELD_ID}"
										class="btn btn-sm btn-primary mr-1 float-right js-update-color" data-js="click"><span
											class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-field-id="{$FIELD_ID}"
										class="btn btn-sm btn-warning mr-1 float-right js-generate-color" data-js="click"><span
											class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}
									</button>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		{/if}
	</div>
	</div>
{/strip}
