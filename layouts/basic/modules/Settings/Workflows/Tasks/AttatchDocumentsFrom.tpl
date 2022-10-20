 {*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
 {strip}
	 <!-- tpl-Settings-Workflows-Tasks-AttatchDocumentsFrom -->
	 <select class="select2 form-control" name="attachments"
	 	data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
	 	<option value="">{\App\Language::translate('LBL_NONE')}</option>
	 	{if $DOCUMENTS_MODULE}
		 	<option value="{$SOURCE_MODULE}" {if isset($TASK_OBJECT->attachments) && $TASK_OBJECT->attachments === $SOURCE_MODULE}selected="selected" {/if}>{\App\Language::translate($SOURCE_MODULE,$SOURCE_MODULE)}</option>
	 	{/if}
	 	{foreach from=$DOCUMENTS_RELATED_MODULES item=RELATED_MODULES}
		 	{foreach from=$RELATED_MODULES key=RELATED_MODULE_NAME item=FIELD_MODEL}
			 	<option value="{$RELATED_MODULE_NAME}::{$FIELD_MODEL->getFieldName()}"
			 		{if isset($TASK_OBJECT->attachments) && $TASK_OBJECT->attachments === {$RELATED_MODULE_NAME}|cat:'::'|cat:{$FIELD_MODEL->getFieldName()}}selected="selected" {/if}>
			 		{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$SOURCE_MODULE)}&nbsp;({$FIELD_MODEL->getFieldName()})&nbsp;-&nbsp;{\App\Language::translate($RELATED_MODULE_NAME,$RELATED_MODULE_NAME)}
			 	</option>
		 	{/foreach}
	 	{/foreach}
	 	{foreach from=$DOCUMENTS_MULTI_REFERENCE_FIELD key=FIELD_NAME item=FIELD_MODEL}
		 	<option value="{$FIELD_NAME}::allAttachments"
		 		{if isset($TASK_OBJECT->attachments) && $TASK_OBJECT->attachments === {$FIELD_NAME}|cat:'::'|cat:'allAttachments'}selected="selected" {/if}>
		 		{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$SOURCE_MODULE)} - {App\Language::translate("LBL_ALL_ATTACHMENTS", $QUALIFIED_MODULE)}
		 	</option>
		 	<option value="{$FIELD_NAME}::latestAttachments" {if isset($TASK_OBJECT->attachments) && $TASK_OBJECT->attachments === {$FIELD_NAME}|cat:'::'|cat:'latestAttachments'}selected="selected" {/if}>
		 		{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$SOURCE_MODULE)} - {App\Language::translate("LBL_LATEST_ATTACHMENTS", $QUALIFIED_MODULE)}
		 	</option>
	 	{/foreach}
	 </select>
	 <!-- /tpl-Settings-Workflows-Tasks-AttatchDocumentsFrom -->
{/strip}
