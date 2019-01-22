{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewComment -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<textarea name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" title="{\App\Language::translate("LBL_ROW_COMMENT",$MODULE)}" id="editView_comment{$FIELD->getColumnName()}{$ROW_NO}" data-fieldinfo="{\App\Json::encode(['mandatory' => false])|escape}" data-height="{$FIELD->height}" data-emoji-enabled="true" data-mentions-enabled="true"
			  class="comment commentTextarea form-control js-editor js-editor--basic" {if $FIELD->isReadOnly()}readonly="readonly"{/if} data-js="ckeditor">
		{$VALUE}
	</textarea>
	<!-- tpl-Base-inventoryfields-EditViewComment -->
{/strip}
