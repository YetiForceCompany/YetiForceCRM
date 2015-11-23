{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">{vtranslate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h4>
				</div>
				<div class="modal-body">
					<input type="hidden" id="mode" value="step1" />
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-md-5 control-label">{vtranslate('LBL_SELECT_TYPE_OF_INVENTORY', $QUALIFIED_MODULE)}:</label>
							<div class="col-md-7">
								<select name="type" class="select2 form-control type">
									{foreach from=$MODULE_MODELS item=ITEM key=KEY}
										{if ((in_array($ITEM->getColumnName(),$FIELDSEXISTS) && !$ITEM->isOnlyOne()) || !in_array($ITEM->getColumnName(),$FIELDSEXISTS) ) && in_array($BLOCK,$ITEM->getBlocks())}
											<option value="{$ITEM->getName()}">{vtranslate($ITEM->getDefaultLabel(), $QUALIFIED_MODULE)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="well well-small">
						{foreach from=$MODULE_MODELS item=ITEM key=KEY}
							{if ((in_array($ITEM->getColumnName(),$FIELDSEXISTS) && !$ITEM->isOnlyOne()) || !in_array($ITEM->getColumnName(),$FIELDSEXISTS) ) && in_array($BLOCK,$ITEM->getBlocks())}
								<h5>{vtranslate($ITEM->getDefaultLabel(), $QUALIFIED_MODULE)}</h5>
								<p>{vtranslate($ITEM->getDefaultLabel()|cat:'_DESC', $QUALIFIED_MODULE)}</p>
								<hr />
							{/if}
						{/foreach}
					</div>
				</div>
				<div class="modal-footer">
					<div class="pull-right cancelLinkContainer">
						<button class="btn btn-success nextButton" type="submit"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>
						<button class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
