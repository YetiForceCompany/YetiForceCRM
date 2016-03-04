{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div {if $POPUP === true}class="knowledgePopoutContainer{/if}">	
	<div class="col-xs-12{if $IS_POPUP === true} knowledgePopoutSubject{/if}">
		<h4>{$RECORD->get('subject')}</h4>
		<hr>
	</div>
	<div class="col-md-12{if $IS_POPUP === true} knowledgePopoutContent{/if}">
		{$CONTENT}
	</div>
</div>
{/strip}
