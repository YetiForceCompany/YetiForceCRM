{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($SUP_VALUE)}
	<textarea name="comment{$ROW_NO}" title="{vtranslate("LBL_ROW_COMMENT",$SUPMODULE)}" id="editView_comment{$ROW_NO}" data-fieldinfo="{Zend_Json::encode(['mandatory' => false])|escape}"
			  class="comment commentTextarea form-control {if Supplies_EditView_Model::isWysiwygType($REFERENCE_MODULE)}ckEditorSource ckEditorBasic{/if}">
		{$VALUE}
	</textarea>
{/strip}
