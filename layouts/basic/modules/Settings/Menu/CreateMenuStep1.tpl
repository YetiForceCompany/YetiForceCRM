{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-Menu-CreateMenuStep1 modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><span class="fa fa-plus u-mr-5px"></span>{\App\Language::translate('LBL_CREATING_MENU', $QUALIFIED_MODULE)}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				{assign var=MENU_TYPES value=$MODULE_MODEL->getMenuTypes()}
				<input type="hidden" id="mode" value="step1" />
				<div class="row">
					<div class="col-md-5 mx-0">{\App\Language::translate('LBL_SELECT_TYPE_OF_MENU', $QUALIFIED_MODULE)}:</div>
					<div class="col-md-7">
						<select name="type" class="select2 type">
							{foreach from=$MENU_TYPES item=ITEM key=KEY}
								<option value="{$KEY}">{\App\Language::translate('LBL_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<br />
				<div class="well well-small mb-0 bg-light" style="max-height: 280px;overflow-y: scroll;">
					{foreach from=$MENU_TYPES item=ITEM key=KEY}
						<h5>{\App\Language::translate('LBL_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}</h5>
						<p>{\App\Language::translate('LBL_'|cat:strtoupper($ITEM)|cat:'_DESC', $QUALIFIED_MODULE)}</p>
					{/foreach}
				</div>
			</div>
			<div class="modal-footer">
				<div class="float-right cancelLinkContainer mt-0">
					<button class="btn btn-success nextButton" type="submit">
						<strong>
							<span class="fas fa-caret-right mr-1"></span>
							{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
						</strong>
					</button>
					<button class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">
						<span class="fas fa-times mr-1"></span>
						{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
