{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-HelpInfo -->
	<form>
		<input type="hidden" value="{$FIELD_MODEL->getId()}" name="field">
		<div class="modal-body">
			<div class="form-group">
				<label for="view-type">{\App\Language::translate('LBL_SHOW_IN_VIEWS',$QUALIFIED_MODULE)}</label>
				<select class="form-control select2" multiple name="views">
					{foreach item=VIEW_NAME key=VIEW_LABEL from=$HELP_INFO_VIEWS}
						<option value="{$VIEW_NAME}" {if strpos($FIELD_MODEL->get('helpinfo'), $VIEW_NAME) !== false} selected {/if}>{App\Language::translate($VIEW_LABEL, $QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
			<hr>
			{assign var=CONTEXT_HELP value=$FIELD_MODEL->getModuleName()|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
			{assign var=TRANSLATE value=\App\Language::translate($CONTEXT_HELP, 'Other:HelpInfo', $LANG_DEFAULT)}
			{if $TRANSLATE eq $CONTEXT_HELP}
				{assign var=TRANSLATE value=""}
			{/if}
			<div class="form-group">
				<label for="lang">{\App\Language::translate('LBL_CHOOSE_LANGUAGE', $QUALIFIED_MODULE)}</label>
				<a href="#" class="js-help-info float-right" data-toggle="popover" title="{App\Purifier::decodeHtml(\App\Language::translate($FIELD_MODEL->getFieldLabel(),$FIELD_MODEL->getModuleName()))}" data-placement="top" data-content="{htmlspecialchars(App\Purifier::decodeHtml($TRANSLATE))}" data-original-title='{App\Purifier::decodeHtml(\App\Language::translate($FIELD_MODEL->getFieldLabel(),$FIELD_MODEL->getModuleName()))}'><span class="fa fa-info-circle"></span></a>
				<select class="form-control select2 js-lang" data-js="change" name="lang" id="lang">
					{foreach from=$LANGUAGES item=LABEL key=PREFIX}
						<option value="{$PREFIX}" {if $PREFIX eq $LANG_DEFAULT}selected{/if}>{$LABEL}</option>
					{/foreach}
				</select>
			</div>
			{foreach from=$LANGUAGES item=LABEL key=PREFIX}
				{assign var=TRANSLATE value=\App\Language::translate($CONTEXT_HELP, 'Other:HelpInfo', $PREFIX)}
				<div class="form-group js-context-block {if $PREFIX neq $LANG_DEFAULT} d-none {/if}">
					<textarea id="{$PREFIX}" name="context" {if $PREFIX neq $LANG_DEFAULT} disabled {/if} data-js="CkEditor" class="form-control js-editor js-context-area" data-purify-mode="Html">{if $TRANSLATE neq $CONTEXT_HELP}{$TRANSLATE}{/if}</textarea>
				</div>
			{/foreach}
		</div>
		<!-- /tpl-Settings-LayoutEditor-HelpInfo -->
{/strip}
