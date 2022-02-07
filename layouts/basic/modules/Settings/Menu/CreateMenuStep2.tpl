{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-Menu-CreateMenuStep2 modal fade" tabindex="-1">
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
				{assign var=MENU_TYPE value=$MENU_TYPES[$TYPE]}
				<form class="form-horizontal">
					<input type="hidden" name="type" id="menuType" value="{$MENU_TYPE}" />
					<div class="form-group row">
						<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_TYPE_OF_MENU', $QUALIFIED_MODULE)}:</label>
						<div class="col-md-7 form-control-plaintext">{\App\Language::translate('LBL_'|cat:strtoupper($MENU_TYPE), $QUALIFIED_MODULE)}</div>
					</div>
					{include file=\App\Layout::getTemplatePath('types/'|cat:$MENU_TYPE|cat:'.tpl', $QUALIFIED_MODULE)}
				</form>
			</div>
			<div class="modal-footer">
				<div class="float-right cancelLinkContainer">
					<button class="btn btn-success saveButton"><strong><span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_ADD_NEW_MENU', $QUALIFIED_MODULE)}</strong></button>
					<button class="btn btn-warning" type="reset"
						data-dismiss="modal"><span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
</div>
