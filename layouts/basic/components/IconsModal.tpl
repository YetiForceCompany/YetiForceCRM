{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Components-IconsModal-->
	<div class="modal-body">
		<div class="row">
			<div class="col-md-6">
				<div class="mb-2">
					<div class="input-group">
						<span class="input-group-prepend">
							<label class="input-group-text"><span class="fas fa-search"></span></label>
						</span>
						<input type="text" name="search" class="form-control js-icon-search" placeholder="{\App\Language::translate('LBL_SEARCH')}" autocomplete="off">
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<nav class="float-right">
					<input type="hidden" class="js-page-size" value="{$PAGE_LIMT}" />
					<ul class="pagination js-pagination-list">
						<li class="js-page--first page-item"
							data-id="1"
							data-js="data">
							<a class="page-link" href="#"><span
									class="fas fa-fast-backward mr-1 d-inline-block d-sm-none"></span><span
									class="d-none d-sm-inline">{\App\Language::translate('LBL_FIRST')}</span></a>
						</li>
						<li class="page-item js-page--previous">
							<a class="page-link" href="#" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span>
								<span class="sr-only">Previous</span>
							</a>
						</li>
						<li class="page-item js-page--set active" data-id="1"><a class="page-link" href="#">1</a></li>
						<li class="page-item js-page--next">
							<a class="page-link" href="#" aria-label="Next">
								<span aria-hidden="true">&raquo;</span>
								<span class="sr-only">Next</span>
							</a>
						</li>
						<li class="js-page--last page-item" data-js="click">
							<a class="page-link" href="#"><span
									class="fas fa-fast-forward mr-1 d-inline-block d-sm-none"></span><span
									class="d-none d-sm-inline">{\App\Language::translate('LBL_LAST')}</span></a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
		<div id="icons-results" class="c-grid">
			{foreach from=$ICONS item=item name=icons}
				<article id="icon-{$smarty.foreach.icons.iteration}" class="w-100 {if $smarty.foreach.icons.iteration gt $PAGE_LIMT} d-none{/if} js-icon-item" data-icon-search="{strtolower($item.name)}" data-name="{$item.name}" data-type="{$item.type}">
					<button type="button" class="btn btn-light w-100 h-100">
						<span class="{$item.name} u-fs-xlg"> </span>
						<span class="c-grid-item--signature u-fs-xs">{$item.name}</span>
					</button>
				</article>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Components-IconsModal-->
{/strip}
