{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{\App\Language::translate('LBL_EDITION_MENU', $QUALIFIED_MODULE)}</h3>
			</div>
			<div class="modal-body">
				{assign var=MENU_TYPES value=$MODULE_MODEL->getMenuTypes()}
				{assign var=MENU_TYPE value=$MENU_TYPES[$RECORD->get('type')]}
				<form class="form-horizontal">
					<input type="hidden" id="menuType" value="{$MENU_TYPE}" />
					<input type="hidden" name="id" value="{$ID}" />
					<input type="hidden" name="role" value="{$RECORD->get('role')}" />
					<div class="form-group">
						<label class="col-md-4 control-label">{\App\Language::translate('LBL_TYPE_OF_MENU', $QUALIFIED_MODULE)}:</label>
						<div class="col-md-7 form-control-static">
							{\App\Language::translate('LBL_'|cat:strtoupper($MENU_TYPE), $QUALIFIED_MODULE)}
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('types/'|cat:$MENU_TYPE|cat:'.tpl', $QUALIFIED_MODULE)}
				</form>
			</div>
			<div class="modal-footer">
				<div class="float-right cancelLinkContainer" style="margin-top:0px;">
					<a class="btn cancelLink btn-warning" type="reset" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					<a class="btn btn-success saveButton"><strong>{\App\Language::translate('LBL_SAVE_MENU', $QUALIFIED_MODULE)}</strong></a>
				</div>
			</div>
		</div>
	</div>
</div>
