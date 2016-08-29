{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{if $IS_FAVORITES}
	{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
	<a class="favorites" data-state="{$RECORD_IS_FAVORITE}">
		<span title="{vtranslate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}" class="glyphicon glyphicon-star alignMiddle {if !$RECORD_IS_FAVORITE}hide{/if}"></span>
		<span title="{vtranslate('LBL_ADD_TO_FAVORITES', $MODULE)}" class="glyphicon glyphicon-star-empty alignMiddle {if $RECORD_IS_FAVORITE}hide{/if}"></span>
	</a>
{/if}
{if AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $RELATED_MODULE->isPermitted('ReviewingUpdates') && $RELATED_MODULE->isTrackingEnabled() && $RELATED_RECORD->isViewable()}
	<a href="{$RELATED_RECORD->getUpdatesUrl()}" class="unreviewed">
		<span class="badge bgDanger"></span>&nbsp;
	</a>&nbsp;
{/if}
<span class="{Documents_Record_Model::getFileIconByFileType($RELATED_RECORD->get('filetype'))} fa-lg"> </span>
