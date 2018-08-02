{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{\App\Language::translate('LBL_SELECT_REPLACE_TREE_ITEM', $QUALIFIED_MODULE)}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="form-horizontal" method="post" action="javascript:;">
				<div class="modal-body">	
					<div id="treePopupContainer" class="paddingLeftRight10px">
						<div class="paddingLeftRight10px">
							<div class="contentsBackground">
								<div id="treePopupContents"></div>
							</div>
						</div>
					</div>
				</div>
				{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
			</form>
		</div>
	</div>
</div>

