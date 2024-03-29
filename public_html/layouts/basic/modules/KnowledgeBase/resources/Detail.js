/* {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js(
	'KnowledgeBase_Detail_Js',
	{},
	{
		registerShowArticlePreview() {
			$('.js-show-article-preview').on('click', (e) => {
				ArticlePreviewVueComponent.mount({
					el: '#ArticlePreview',
					state: {
						moduleName: $(e.currentTarget).data('moduleName'),
						recordId: $(e.currentTarget).data('id')
					}
				});
			});
		},
		registerEvents() {
			this._super();
			this.registerShowArticlePreview();
		}
	}
);
