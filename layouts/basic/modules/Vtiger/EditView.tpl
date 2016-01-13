{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{include file="EditViewBlocks.tpl"|@vtemplate_path:$MODULE}
{if $MODULE_TYPE == '1'}
	{include file='EditViewInventory.tpl'|@vtemplate_path:$MODULE}
{/if}
{include file="EditViewActions.tpl"|@vtemplate_path:$MODULE}
