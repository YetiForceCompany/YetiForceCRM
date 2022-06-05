{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PickListDependency-DependentTable -->
	<div class="mb-3">
		<span class="btn-toolbar">
			<button class="btn sourceValues btn-light mr-2" type="button">
				<strong>
					<span class="fas fa-hand-point-up mr-1"></span>
					{\App\Language::translate('LBL_SELECT_SOURCE_VALUES', $QUALIFIED_MODULE)}
				</strong>
			</button>
			<button class="btn unmarkAll btn-light" type="button">
				<strong>
					<span class="far fa-times-circle mr-2"></span>
					{\App\Language::translate('LBL_UNMARK_ALL', $QUALIFIED_MODULE)}
				</strong>
			</button>
		</span>
	</div>
	{assign var=SELECTED_MODULE value=$RECORD_MODEL->get('sourceModule')}
	{assign var=SOURCE_FIELD value=$RECORD_MODEL->get('source_field')}
	{assign var=MAPPED_SOURCE_PICKLIST_VALUES value=[]}
	{assign var=MAPPED_TARGET_PICKLIST_VALUES value=[]}

	{foreach item=MAPPING from=$MAPPED_VALUES}
		{append var="MAPPED_SOURCE_PICKLIST_VALUES" value=$MAPPING['sourcevalue']}
		{$MAPPED_TARGET_PICKLIST_VALUES[$MAPPING['sourcevalue']] = $MAPPING['secondValues']}
	{/foreach}
	{var_dump($MAPPED_VALUES)}

	<input type="hidden" class="allSourceValues"
		value='{\App\Purifier::encodeHtml(\App\Json::encode($SOURCE_PICKLIST_VALUES))}' />
	<div class="js-picklist-dependency-table mb-2" data-js="container">
		<div class="row depandencyTable m-0">
			<div class="col-2 col-lg-1 p-0  table-responsive">
				<table class="table table-sm themeTableColor w-100">
					<thead>
						<tr class="blockHeader">
							<th class="text-center">{$RECORD_MODEL->getSourceFieldLabel()}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-center h5">
								<select class="form-control select2 js-source-field-value" name="sourceFieldValue" data-js="container">
									{foreach item=SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
										<option value="{\App\Purifier::encodeHtml($SOURCE_PICKLIST_VALUE)}"
											{if $SOURCE_PICKLIST_VALUE@iteration == 1}
												{assign var=SELECTED_SOURCE_VALUE value=$SOURCE_PICKLIST_VALUE}
												data-old-source-value="{\App\Purifier::encodeHtml($SOURCE_PICKLIST_VALUE)}" selected
											{/if}>{\App\Language::translate($SOURCE_PICKLIST_VALUE, $SELECTED_MODULE)}</option>

									{/foreach}
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-10 col-lg-11 px-0 ml-0 dependencyMapping table-responsive">
				<table class="table-bordered table-sm table themeTableColor pickListDependencyTable">
					<thead>
						<tr class="blockHeader">

							{foreach item=TARGET_PICKLIST_VALUE from=$TARGET_PICKLIST_VALUES}
								<th class="align-baseline js-second-field-value" data-source-value="{\App\Purifier::encodeHtml($TARGET_PICKLIST_VALUE)}">
									{\App\Language::translate($TARGET_PICKLIST_VALUE, $SELECTED_MODULE)}</th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach key=TARGET_INDEX item=THIRD_VALUE from=$THIRD_FIELD_PICKLIST_VALUES name=targetValuesLoop}
							<tr>
								{foreach item=SECOND_PICKLIST_VALUE from=$TARGET_PICKLIST_VALUES}
									{assign var=PURIFIER_TMP_VAL value=\App\Purifier::encodeHtml($SECOND_PICKLIST_VALUE)}
									{if !empty($MAPPED_TARGET_PICKLIST_VALUES[$PURIFIER_TMP_VAL])}
										{assign var=targetValues value=$MAPPED_TARGET_PICKLIST_VALUES[$PURIFIER_TMP_VAL]}
									{/if}
									{assign var=SOURCE_INDEX value=$smarty.foreach.mappingIndex.index}
									{assign var=IS_SELECTED value=false}
									{assign var=targetValues value=[]}
									{if !isset($MAPPING_FOR_THREE[$SELECTED_SOURCE_VALUE][$SECOND_PICKLIST_VALUE]) || in_array($THIRD_VALUE, $MAPPING_FOR_THREE[$SELECTED_SOURCE_VALUE][$SECOND_PICKLIST_VALUE])}
										{assign var=IS_SELECTED value=true}
									{/if}
									<td data-source-value='{\App\Purifier::encodeHtml($SECOND_PICKLIST_VALUE)}'
										data-target-value='{\App\Purifier::encodeHtml($THIRD_VALUE)}'
										class="{if $IS_SELECTED}selectedCell {else}unselectedCell {/if} targetValue picklistValueMapping u-cursor-pointer">
										{\App\Language::translate($THIRD_VALUE, $SELECTED_MODULE)}
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-PickListDependency-DependentTable -->
{/strip}
