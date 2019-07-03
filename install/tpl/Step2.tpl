{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="tpl-install-tpl-Step2 container px-2 px-sm-3">
		<main class="main-container">
			<div class="inner-container">
				<form name="step{$STEP_NUMBER}" method="post" action="Install.php">
					<input type="hidden" name="mode" value="{$NEXT_STEP}">
					<input type="hidden" name="lang" value="{$LANG}">
					<div class="row">
						<div class="col-12 text-center">
							<h2>{\App\Language::translate('LBL_LICENSE', 'Install')}</h2>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-12">
							<p>
								{\App\Language::translate('LBL_STEP2_DESCRIPTION_1','Install')}&nbsp;
								<a target="_blank" rel="noreferrer noopener"
								   href="https://yetiforce.com/en/yetiforce/license" aria-label="{\App\Language::translate('LBL_LICENSE', 'Install')}">
									<span class="fas fa-link"></span> </a><br/><br/>
								{\App\Language::translate('LBL_STEP2_DESCRIPTION_2','Install')}
							</p>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<p class="license">{$LICENSE}</p>
						</div>
					</div>
					<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
						<div class="text-center w-100">
							<button type="button" class="btn btn-lg c-btn-block-sm-down btn-outline-info mb-1 mb-md-0 mr-md-1" data-toggle="modal" data-target="#license-modal">
								<span class="fas fa-lg fas fa-bars mr-2"></span>
								{App\Language::translate('LBL_EXTERNAL_LIBRARIES_LICENSES', 'Install')}
							</button>
							<a class="btn btn-lg c-btn-block-sm-down btn-danger mb-1 mb-md-0 mr-md-1" href="Install.php" role="button">
								<span class="fas fa-lg fa-times-circle mr-2"></span>
								{App\Language::translate('LBL_DISAGREE', 'Install')}
							</a>
							<button type="submit" class="btn btn-lg c-btn-block-sm-down btn-primary">
								<span class="fas fa-lg fa-check mr-2"></span>
								{App\Language::translate('LBL_I_AGREE', 'Install')}
							</button>
						</div>
					</div>
				</form>
			</div>
		</main>
		<div class="modal js-license-modal" id="license-modal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true" data-js="shown.bs.modal | container">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="ModalLabel">
							<span class="fas fa-sm fas fa-bars mr-1"></span>
							{\App\Language::translate('LBL_EXTERNAL_LIBRARIES_LICENSES', 'Install')}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<table class="table table-sm table-bordered js-data-table" data-js="datatable">
							<thead>
							<th class="p-2">
								{\App\Language::translate('LBL_LIBRARY_NAME', 'Install')}
							</th>
							<th class="p-2 text-center">
								{\App\Language::translate('LBL_VERSION', 'Install')}
							</th>
							<th class="p-2 text-center">
								{\App\Language::translate('LBL_LICENSE', 'Install')}
							</th>
							</thead>
							<tbody>
							{foreach from=$LIBRARIES key=TYPE item=ITEMS}
								{if $ITEMS}
									{foreach from=$ITEMS item=ITEM}
										<tr>
											<td class="u-word-break">
												<a title="{\App\Language::translate('LBL_LIBRARY_HOMEPAGE', 'Install')}"
												   href="{if !empty($ITEM['homepage'])}{$ITEM['homepage']}{else}#{/if}" target="_blank"
												   rel="noreferrer noopener">
													{$ITEM['name']}
												</a>
												{if !empty($ITEM['description'])}
													({\App\Language::translate($ITEM['description'], 'Settings')})
												{/if}
											</td>
											<td class="text-center">
												{$ITEM['version']}
											</td>
											<td class="text-center">
												{$ITEM['license']}
											</td>
										</tr>
									{/foreach}
								{else}
									<div class="p-3 mb-2 bg-danger text-white">{\App\Language::translate('LBL_MISSING_FILE')}</div>
								{/if}
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
