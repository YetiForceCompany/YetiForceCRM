{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="mode" value="step1" />
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-md-5 col-form-label">{\App\Language::translate('LBL_SELECT_TYPE_OF_INVENTORY', $QUALIFIED_MODULE)}:</label>
							<div class="col-md-7">
								<select name="type" class="select2 form-control type">
									{foreach from=$MODULE_MODELS item=ITEM key=KEY}
										{if ((in_array($ITEM->getColumnName(),$FIELDSEXISTS) && !$ITEM->isOnlyOne()) || !in_array($ITEM->getColumnName(),$FIELDSEXISTS) ) && in_array($BLOCK,$ITEM->getBlocks())}
											<option value="{$ITEM->getName()}">{\App\Language::translate($ITEM->getDefaultLabel(), $QUALIFIED_MODULE)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="well well-small">
						{foreach from=$MODULE_MODELS item=ITEM key=KEY}
							{if ((in_array($ITEM->getColumnName(),$FIELDSEXISTS) && !$ITEM->isOnlyOne()) || !in_array($ITEM->getColumnName(),$FIELDSEXISTS) ) && in_array($BLOCK,$ITEM->getBlocks())}
								<h5>{\App\Language::translate($ITEM->getDefaultLabel(), $QUALIFIED_MODULE)}</h5>
								<p>{\App\Language::translate($ITEM->getDefaultLabel()|cat:'_DESC', $QUALIFIED_MODULE)}</p>
								<hr />
							{/if}
						{/foreach}
					</div>
				</div>
				<div class="modal-footer">
					<div class="float-right cancelLinkContainer">
						<button class="btn btn-success nextButton" type="submit"><strong>{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>
						<button class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
