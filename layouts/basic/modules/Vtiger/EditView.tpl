{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{include file="EditViewBlocks.tpl"|@\App\Layout::getTemplatePath:$MODULE}
{if $MODULE_TYPE == '1'}
	{include file='EditViewInventory.tpl'|@\App\Layout::getTemplatePath:$MODULE}
{/if}
{include file="EditViewActions.tpl"|@\App\Layout::getTemplatePath:$MODULE}
