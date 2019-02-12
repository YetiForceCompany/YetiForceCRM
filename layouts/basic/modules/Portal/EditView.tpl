{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<h5 class="modal-title">
						<span class="fas fa-plus mr-1"></span>
						{if $RECORD}
							{\App\Language::translate('LBL_EDIT_BOOKMARK', $MODULE)}
						{else}
							{\App\Language::translate('LBL_ADD_NEW_BOOKMARK', $MODULE)}
						{/if}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="saveBookmark" method="post" action="index.php">
					<input type="hidden" name="record" value="{$RECORD}" />
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<div class="modal-body">
						<div class="form-group row my-0">
							<label class="col-form-label col-md-4 text-right">
								<span class="redColor">*</span>&nbsp;{\App\Language::translate('LBL_BOOKMARK_NAME',$MODULE)}
							</label>
							<div class="col-md-8 align-self-center">
								<input type="text" name="bookmarkName" class="form-control" title="{\App\Language::translate('LBL_BOOKMARK_NAME',$MODULE)}" id="bookmarkName"{if $RECORD} value="{$BOOKMARK_NAME}" {/if} placeholder="{\App\Language::translate('LBL_ENTER_BOOKMARK_NAME', $MODULE)}" />
							</div>
						</div>
						<div class="form-group row mb-0 mt-1">
							<label class="col-form-label col-md-4 text-right">
								<span class="redColor">*</span>&nbsp;{\App\Language::translate('LBL_BOOKMARK_URL',$MODULE)}
							</label>
							<div class="col-md-8 align-self-center">
								<input type="text" name="bookmarkUrl" class="form-control" title="{\App\Language::translate('LBL_BOOKMARK_URL',$MODULE)}" id="bookmarkUrl"{if $RECORD} value="{$BOOKMARK_URL}" {/if} placeholder="{\App\Language::translate('LBL_ENTER_URL', $MODULE)}" />
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
    </div>
{/strip}
