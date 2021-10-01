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

<button type="submit" name="import" id="importButton" class="crmButton big edit btn btn-success" disabled>
	<span class="fas fa-file-import mr-2"></span>
	{\App\Language::translate('LBL_IMPORT_BUTTON_LABEL', $MODULE)}
</button>
<button type="button" name="cancel" value="{\App\Language::translate('LBL_CANCEL', $MODULE)}" class="cursorPointer cancelLink btn btn-danger" onclick="window.history.back()">
	<span class="fas fa-times mr-2"></span>
	{\App\Language::translate('LBL_CANCEL', $MODULE)}
</button>
