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
	<div id="addFolderContainer" class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_ADD_NEW_FOLDER', $MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal contentsBackground" id="addFolder" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="Folder" />
					<input type="hidden" name="mode" value="save" />
					<input type="hidden" name="folderid" value="{$FOLDER_MODEL->getId()}" />
					<div class="modal-body">
						<div class="row verticalBottomSpacing">
							<span class="col-md-4"><span class="redColor">*</span>{\App\Language::translate('LBL_FOLDER_NAME', $MODULE)}</span>
							<span class="col-md-7 row"><input data-validation-engine='validate[required]' id="foldername" title="{\App\Language::translate('LBL_FOLDER_NAME', $MODULE)}" name="foldername" class="form-control" type="text" value="{\App\Language::translate($FOLDER_MODEL->getName(), $MODULE)}" /></span>
						</div>
						<div class="row">
							<span class="col-md-4">{\App\Language::translate('LBL_FOLDER_DESCRIPTION', $MODULE)}</span>
							<span class="col-md-7 row">
								<textarea class="form-control" name="description" title="{\App\Language::translate('LBL_DESCRIPTION',$MODULE)}" placeholder="{\App\Language::translate('LBL_WRITE_YOUR_DESCRIPTION_HERE', $MODULE)}">{\App\Language::translate($FOLDER_MODEL->getDescription(), $MODULE)}</textarea>
							</span>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE)}
				</form>
			</div>
		</div>
	</div>
{/strip}
