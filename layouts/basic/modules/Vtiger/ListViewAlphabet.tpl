{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="alphabetSearchKey" value= "{$MODULE_MODEL->getAlphabetSearchField()}" />
	<input type="hidden" id="Operator" value="{$OPERATOR}" />
	<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
	{assign var = ALPHABETS_LABEL value = vtranslate('LBL_ALPHABETS', 'Vtiger')}
	{assign var = ALPHABETS value = ','|explode:$ALPHABETS_LABEL}
	<div class="alphabetModal" tabindex="-1">
		<div  class="modal fade ">
			<div class="modal-dialog ">
				<div class="modal-content">
					<div class="modal-header">
						<div class="row no-margin">
							<div class="col-md-7 col-xs-10">
								<h3 class="modal-title">{vtranslate('LBL_ALPHABETIC_FILTERING', $MODULE_NAME)}</h3>
							</div>
							<div class="pull-right">
								<div class="pull-right">
									<button class="btn btn-default close" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
								</div>
							</div>
						</div>
					</div>
					{assign var=COUNT_ALPHABETS value=count($ALPHABETS)}
					<div class="modal-body">
						<div class="alphabetSorting noprint paddingLRZero">
							<div class="alphabetContents alphabet_{$COUNT_ALPHABETS} row ">
								{foreach item=ALPHABET from=$ALPHABETS}
									<div class="alphabetSearch cursorPointer">
										<a class="btn {if isset($ALPHABET_VALUE) && $ALPHABET_VALUE == $ALPHABET}btn-primary{else}btn-default{/if}" id="{$ALPHABET}" href="#">{$ALPHABET}</a>
									</div>
								{/foreach}
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="pull-right">
							<button class="btn btn-danger removeAlfabetCondition" type="button" title="{vtranslate('LBL_REMOVE_ALPH_SEARCH_INFO', $MODULE_NAME)}" >
								{vtranslate('LBL_REMOVE_FILTERING', $MODULE_NAME)}
							</button >
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
