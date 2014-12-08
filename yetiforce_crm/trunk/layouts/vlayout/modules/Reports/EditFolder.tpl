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
	<div id="addFolderContainer" class="modelContainer" style='min-width:350px;'>
		<div class="modal-header">
			<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
			<h3>{vtranslate('LBL_ADD_NEW_FOLDER', $MODULE)}</h3>
		</div>
		<form class="form-horizontal contentsBackground" id="addFolder" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="Folder" />
			<input type="hidden" name="mode" value="save" />
			<input type="hidden" name="folderid" value="{$FOLDER_MODEL->getId()}" />
			<div class="modal-body">
				<div class="row-fluid verticalBottomSpacing">
					<span class="span4">{vtranslate('LBL_FOLDER_NAME', $MODULE)}<span class="redColor">*</span></span>
					<span class="span7 row-fluid"><input data-validation-engine='validate[required]' id="foldername" name="foldername" class="span12" type="text" value="{vtranslate($FOLDER_MODEL->getName(), $MODULE)}"/></span>
				</div>
				<div class="row-fluid">
					<span class="span4">{vtranslate('LBL_FOLDER_DESCRIPTION', $MODULE)}</span>
					<span class="span7 row-fluid">
						<textarea class="span12" name="description" placeholder="{vtranslate('LBL_WRITE_YOUR_DESCRIPTION_HERE', $MODULE)}">{vtranslate($FOLDER_MODEL->getDescription(), $MODULE)}</textarea>
					</span>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}