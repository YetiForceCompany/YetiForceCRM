{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var = ALPHABETS_LABEL value = vtranslate('LBL_ALPHABETS', 'Vtiger')}
	{assign var = ALPHABETS value = ','|explode:$ALPHABETS_LABEL}
	<div class="alphabetModal" tabindex="-1">
		<div  class="modal fade ">
			<div class="modal-dialog ">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row no-margin">
							<div class="col-xs-1 pull-right">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
							</div>
							<div class="col-md-7 col-xs-10">
								<h3 class="modal-title">{vtranslate('LBL_ALPABETIC_FILTERING', $MODULE_NAME)}</h3>
							</div>
							<div class="col-md-4 paddingLRZero">
								<a class="btn btn-danger pull-right" href="{$MODULE_MODEL->getListViewUrl()}" title="{vtranslate('LBL_REMOVE_ALPH_SEARCH_INFO', $MODULE_NAME)}" >
									{vtranslate('LBL_REMOVE FILTERING', $MODULE_NAME)}
								</a>
							</div>
						</div>
					</div>
					{assign var=COUNT_ALPHABETS value=count($ALPHABETS)}
					<div class="modal-body">
						<div class="alphabetSorting noprint paddingLRZero">
							<div class="alphabetContents alphabet_{$COUNT_ALPHABETS} row ">
								{foreach item=ALPHABET from=$ALPHABETS}
									<div class="alphabetSearch cursorPointer">
										<a class="btn {if $ALPHABET_VALUE eq $ALPHABET}btn-primary{else}btn-default{/if}" id="{$ALPHABET}" href="#">{$ALPHABET}</a>
									</div>
								{/foreach}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
