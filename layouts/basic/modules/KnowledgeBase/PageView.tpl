{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div {if $IS_POPUP === true}class="knowledgePopoutContainer{/if}">
		<div class="col-12{if $IS_POPUP === true} knowledgePopoutSubject{/if}">
			<h4>{$RECORD->getDisplayValue('subject')}</h4>
			<hr>
		</div>
		<div class="col-md-12{if $IS_POPUP === true} knowledgePopoutContent{/if}">
			{\App\Purifier::purifyHtml($CONTENT)}
		</div>
	</div>
{/strip}
