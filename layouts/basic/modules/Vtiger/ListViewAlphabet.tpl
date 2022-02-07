{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="alphabetSearchKey" value="{$MODULE_MODEL->getAlphabetSearchField()}" />
	<input type="hidden" id="Operator" value="{if isset($OPERATOR)}{$OPERATOR}{/if}" />
	<input type="hidden" id="alphabetValue" value="{if isset($ALPHABET_VALUE)}{$ALPHABET_VALUE}{/if}" />
	{assign var = ALPHABETS_LABEL value = \App\Language::translate('LBL_ALPHABETS', 'Vtiger')}
	{assign var = ALPHABETS value = ','|explode:$ALPHABETS_LABEL}
	<div class="alphabetModal" tabindex="-1">
		<div class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{\App\Language::translate('LBL_ALPHABETIC_FILTERING', $MODULE_NAME)}</h5>
						<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					{assign var=COUNT_ALPHABETS value=count($ALPHABETS)}
					<div class="modal-body p-1">
						<div class="text-center noprint">
							<div class="p-0 m-auto alphabet_{$COUNT_ALPHABETS} row ">
								{foreach item=ALPHABET from=$ALPHABETS}
									<div class="alphabetSearch u-cursor-pointer">
										<h5>
											<a class="btn font-weight-bold {if isset($ALPHABET_VALUE) && $ALPHABET_VALUE == $ALPHABET}btn-primary{else}btn-light{/if}" role="button" id="{$ALPHABET}" href="#">{$ALPHABET}</a>
										</h5>
									</div>
								{/foreach}
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-danger removeAlfabetCondition" type="button" title="{\App\Language::translate('LBL_REMOVE_ALPH_SEARCH_INFO', $MODULE_NAME)}">
							{\App\Language::translate('LBL_REMOVE_FILTERING', $MODULE_NAME)}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
