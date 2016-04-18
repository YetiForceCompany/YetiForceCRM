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

<button name="next" class="create btn btn-success marginBottom10px"
	   onclick="location.href='index.php?module={$FOR_MODULE}&view=Import&return_module={$FOR_MODULE}&return_action=index'" ><strong>{'LBL_IMPORT_MORE'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
<button name="next" class="cancel btn btn-primary marginBottom10px"
		onclick="return window.open('index.php?module={$MODULE}&for_module={$FOR_MODULE}&view=List&start=1&foruser={$OWNER_ID}','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');"><strong>{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
{if $MERGE_ENABLED eq '0'}
<button name="next" class="delete btn btn-danger marginBottom10px"
		onclick="location.href='index.php?module={$FOR_MODULE}&view=Import&mode=undoImport&foruser={$OWNER_ID}&type={$TYPE}'"><strong>{'LBL_UNDO_LAST_IMPORT'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
{/if}
<button name="cancel" class="edit btn btn-success marginBottom10px"
		onclick="location.href='index.php?module={$FOR_MODULE}&view=List{if $FOR_MODULE eq 'Users'}&parent=Settings{/if}'"><strong>{'LBL_FINISH_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>
