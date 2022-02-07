{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-SortOrderModal -->
	<div class="modal-body js-modal-body" data-js="container">
		<div class="row">
			<div class="form-group col-12">
				<button type="button" class="btn btn-default js-add" data-js="click"
					title="{\App\Language::translate('LBL_ADD', $MODULE_NAME)}">
					<span class="fas fa-plus"></span>
				</button>
			</div>
			<div class="form-group col-12 mb-0">
				<div class="js-sort-container" id="js-sort-container" data-js="container">
					{function SELECT_STRUCTURE RECORD_STRUCTURES=[] SOURCE_MODULE_MODEL='' BASIC=false}
						<div class="input-group js-sort-container_element flex-nowrap mb-2{if $BASIC} js-base-element d-none{/if}" data-js="container">
							<div class="input-group-prepend">
								<button type="button" class="btn btn-danger js-clear" data-js="click"
									title="{\App\Language::translate('LBL_REMOVE', $MODULE_NAME)}">
									<span class="fas fa-times-circle"></span>
								</button>
							</div>
							<select class="{if !$BASIC}select2 {/if}form-control col-3 js-orderBy" name="orderBy" data-js="val">
								<option></option>
								{foreach key=MODULE_KEY item=RECORD_STRUCTURE_FIELD from=$RECORD_STRUCTURES}
									{foreach key=RELATED_FIELD_NAME item=RECORD_STRUCTURE from=$RECORD_STRUCTURE_FIELD}
										{assign	var=RELATED_FIELD	value=$SOURCE_MODULE_MODEL->getFieldByName($RELATED_FIELD_NAME)}
										{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
											{assign	var=BLOCK_LABEL	value={\App\Language::translate($BLOCK_LABEL, $MODULE_KEY)}}
											{if $RELATED_FIELD}
												{assign var=BLOCK_LABEL	value="{\App\Language::translate($RELATED_FIELD->getFieldLabel(), $RELATED_FIELD->getModuleName())}&nbsp;-&nbsp;{\App\Language::translate($MODULE_KEY, $MODULE_KEY)}&nbsp;-&nbsp;{$BLOCK_LABEL}"}
											{/if}
											<optgroup label="{$BLOCK_LABEL}">
												{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
													<option value="{$FIELD_MODEL->getFullName()}" data-exists="{$FIELD_MODEL->get('source_field_name')}">
														{$FIELD_MODEL->getFullLabelTranslation($SOURCE_MODULE_MODEL)}
													</option>
												{/foreach}
											</optgroup>
										{/foreach}
									{/foreach}
								{/foreach}
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary js-sort-order-button" data-js="click">
									<span class="fas fa-sort-amount-up js-sort-icon-active js-sort-icon"
										data-val="{\App\Db::ASC}"
										title="{\App\Language::translate('LBL_SORT_ASCENDING', $MODULE_NAME)}">
									</span>
									<span class="fas fa-sort-amount-down d-none js-sort-icon"
										data-val="{\App\Db::DESC}"
										title="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
									</span>
									<input type="hidden" class="js-sort-order" name="sortOrder" value="{\App\Db::ASC}" data-js="val" />
								</button>
							</div>
						</div>
					{/function}
					{SELECT_STRUCTURE RECORD_STRUCTURES=$RECORD_STRUCTURES SOURCE_MODULE_MODEL=$SOURCE_MODULE_MODEL BASIC=true}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Modals-SortOrderModal -->
{/strip}
