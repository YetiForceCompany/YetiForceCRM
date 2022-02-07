{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewComment -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<label class="my-0 fieldLabel u-text-small-bold">
		{\App\Language::translate($FIELD->get('label'), $MODULE)}
	</label>
	<textarea name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" title="{\App\Language::translate("LBL_ROW_COMMENT",$MODULE)}"
		id="editView_comment{$FIELD->getColumnName()}{$ROW_NO}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode(['mandatory' => false]))}"
		data-height="{$FIELD->getHeight()}px" data-width="{$FIELD->getWidth()}%" data-emoji-enabled="true" data-mentions-enabled="true"
		class="comment js-inventory-item-comment form-control js-editor js-editor--basic" {if $FIELD->isReadOnly()}readonly="readonly" {/if} data-purify-mode="Html" data-js="ckeditor">{$VALUE}</textarea>
	<!-- tpl-Base-inventoryfields-EditViewComment -->
{/strip}
