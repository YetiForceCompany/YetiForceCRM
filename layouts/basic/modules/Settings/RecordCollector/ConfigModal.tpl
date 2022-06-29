{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-RecrodCollector-ConfigModal -->
	<div class="modal-body pb-0">
		<form class="js-form-validation">
			<div class="row no-gutters">
				<div class="col-sm-18 col-md-12">
					<table class="table table-sm mb-0">
						<tbody class="u-word-break-all small">
							{foreach from=$FIELDS item=FIELD}
								{if 'string' === $FIELD->getFieldDataType()}
									{assign var=TYPE value='text'}
								{/if}
								<td class="py-2 u-font-weight-550 align-middle border-bottom">
									{App\Language::translate($FIELD->get('label'), $QUALIFIED_MODULE)}
								</td>
								<td class="py-2 position-relative w-60 border-bottom">
							<input type="{$TYPE}" class="form-control js-custom-field" placeholder="{\App\Language::translate($FIELD->get('label'), $QUALIFIED_MODULE)}" name="{$FIELD->get('name')}" {if isset($FIELD->get('value'))}value="{$FIELD->get('value')}" {/if}/>
								</td>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-RecrodCollector-ConfigModal -->
{/strip}
