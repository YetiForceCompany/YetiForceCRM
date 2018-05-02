{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{\App\Language::translate('LBL_SELECT_REPLACE_TREE_ITEM', $QUALIFIED_MODULE)}</h3>
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

