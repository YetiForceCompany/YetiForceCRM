/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("KnowledgeBase_Tree_Js", {},
{
	treeInstance: false,
	content: false,
	getContent: function (){
		if(!this.content){
			this.content = $('.contentOfData');
		}
		return this.content;
	},
	generateTree: function (container, data) {
		var thisInstance = this;
		thisInstance.treeInstance = container.find('#treeContent');
		var values = data.result;
		var plugins = ['search'];
		thisInstance.treeInstance.jstree({
			core: {
				data: values,
				themes: {
					name: 'proton',
					responsive: true
				}
			},
			plugins: plugins
		});
	},
	loadTree: function(reload){
		var thisInstance = this;
		var container = $('.treeContainer');
		var params = {
			module  : app.getModuleName(),
			action : 'TreeAJAX',
		};
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		if(reload){
			thisInstance.treeInstance.jstree('destroy');
		}
		AppConnector.request(params).then(function(data){
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			thisInstance.generateTree(container, data);
			thisInstance.registerTreeEvents(container);
		});
	},
	searchingInTree: function (text) {
		this.treeInstance.jstree(true).search(text);
	},
	registerSearchEvent: function () {
		var thisInstance = this;
		var valueSearch = $('#valueSearchTree');
		var btnSearch = $('#btnSearchTree');
		valueSearch.keypress(function (e) {
			if (e.which == 13) {
				thisInstance.searchingInTree(valueSearch.val());
			}
		});
		btnSearch.click(function () {
			thisInstance.searchingInTree(valueSearch.val());
		});
	},
	loadContent: function(recordId){
		var thisInstance = this;
		var contentData = thisInstance.getContent();
		var params = {
			module: app.getModuleName(),
			view: 'ContentAJAX',
		};
		if(typeof recordId != 'undefined'){
			params['record'] = recordId;
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		AppConnector.request(params).then(function(data){
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			contentData.html(data);
		});
	},
	registerTreeEvents: function(){
		var thisInstance = this;
		thisInstance.registerSearchEvent();
		thisInstance.treeInstance.on('changed.jstree',function (e,data){
			if(data.node.original.type != 'folder'){
				thisInstance.loadContent(data.node.original.record_id);
			}
		});
	},
	registerBasicEvents: function(){
		var thisInstance = this;
		$('.addRecord').click(function(){
			var headerInstance = Vtiger_Header_Js.getInstance();
			var moduleName = app.getModuleName();
			var postQuickCreate = function(data){
				thisInstance.loadTree(true);
			};
			var quickCreateParams = {
				callbackFunction: postQuickCreate,
				noCache: false
			};
			headerInstance.quickCreateModule(moduleName, quickCreateParams);
		});
	},
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.registerBasicEvents();
		thisInstance.loadTree(false);
		thisInstance.loadContent();
	
	}
});


