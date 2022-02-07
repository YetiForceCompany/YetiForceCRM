{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-CreateInventoryFieldsStep1 -->
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal"
						title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" value="step1" id="mode" />
					<div class="form-horizontal">
						<div class="form-group row align-items-center">
							<div class="col-md-4 col-form-label text-right">
								{\App\Language::translate('LBL_SELECT_TYPE_OF_INVENTORY', $QUALIFIED_MODULE)}:
							</div>
							<div class="col-md-7">
								<select name="type" class="select2 form-control type"
									data-validation-engine="validate[required]">
									{foreach from=$MODULE_MODELS item=ITEM key=KEY}
										{assign var=COLUMN value=$ITEM->getColumnName()}
										{if (!isset($FIELDS_EXISTS[$COLUMN]) || !$ITEM->isOnlyOne()) && in_array($BLOCK, $ITEM->getBlocks())}
											<option value="{$ITEM->getType()}">{\App\Language::translate($ITEM->getDefaultLabel(), $QUALIFIED_MODULE)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="well well-small">
						{foreach from=$MODULE_MODELS item=ITEM key=KEY}
							{assign var=COLUMN value=$ITEM->getColumnName()}
							{if (!isset($FIELDS_EXISTS[$COLUMN]) || !$ITEM->isOnlyOne()) && in_array($BLOCK, $ITEM->getBlocks())}
								<h5>{\App\Language::translate($ITEM->getDefaultLabel(), $QUALIFIED_MODULE)}</h5>
								<p>{\App\Language::translate($ITEM->getDefaultLabel()|cat:'_DESC', $QUALIFIED_MODULE)}</p>
								<hr />
							{/if}
						{/foreach}
					</div>
				</div>
				<div class="modal-footer">
					<div class="float-right cancelLinkContainer">
						<button class="btn btn-success mr-1 js-next-button" type="submit" data-js="click">
							<span class="fas fa-lg fa-arrow-circle-right mr-1"></span>
							<b>{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}</b>
						</button>
						<button class="btn btn-danger" type="reset" data-dismiss="modal" {' '}
							title="{\App\Language::translate('LBL_CLOSE')}">
							<span class="fas fa-times mr-1"></span>
							<b>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</b>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-CreateInventoryFieldsStep1 -->
{/strip}
