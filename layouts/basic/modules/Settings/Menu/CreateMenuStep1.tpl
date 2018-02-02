{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{\App\Language::translate('LBL_CREATING_MENU', $QUALIFIED_MODULE)}</h4>
			</div>
			<div class="modal-body">
				{assign var=MENU_TYPES value=$MODULE_MODEL->getMenuTypes()}
				<input type="hidden" id="mode" value="step1" />
				<div class="row">
					<div class="col-md-5 marginLeftZero">{\App\Language::translate('LBL_SELECT_TYPE_OF_MENU', $QUALIFIED_MODULE)}:</div>
					<div class="col-md-7">
						<select name="type" class="select2 form-control type">
							{foreach from=$MENU_TYPES item=ITEM key=KEY}
								<option value="{$KEY}">{\App\Language::translate('LBL_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<br />
				<div class="well well-small" style="margin-bottom: 0;max-height: 280px;overflow-y: scroll;">
					{foreach from=$MENU_TYPES item=ITEM key=KEY}
						<h5>{\App\Language::translate('LBL_'|cat:strtoupper($ITEM), $QUALIFIED_MODULE)}</h5>
						<p>{\App\Language::translate('LBL_'|cat:strtoupper($ITEM)|cat:'_DESC', $QUALIFIED_MODULE)}</p>
					{/foreach}
				</div>
			</div>
			<div class="modal-footer">
				<div class="float-right cancelLinkContainer" style="margin-top:0px;">
					<button class="btn btn-success nextButton" type="submit"><strong>{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>
					<button class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
</div>
