/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

/**
 * Waterfall select is a component for select that will replace second container available options basing on tree-like structure from master select
 * Data for selects is keeped in data-data property of the master element as json string.
 * Data structure is just an array of objects with three keys: value, text and children which contain elements for second select
 * Just create two empty selects(with no options) and attach data-data and data-slave properties to master select element.
 * data-slave property is used to find slave select element.
 */
class WaterfallSelect {

	/**
	 * Constructor
	 *
	 * We can give multiple elements as container
	 * data-slave should be an #id of child element which options/values will change on master element change event
	 *
	 * @param {String|jQuery|HTMLElement} element
	 * @param {Object} data
	 */
	constructor(element) {
		let elements = $(element).toArray();
		if (elements.length > 1) {
			return elements.map((element) => {
				return new WaterfallSelect(element);
			});
		}
		this.elementMaster = $(element);
		this.slaveSelector = this.elementMaster.data('slave');
		if (this.slaveSelector) {
			this.elementSlave = $(this.elementMaster.data('slave'));
			this.data = this.elementMaster.data('data');
			this.elementMaster.html(this.renderOptions(this.data));
			this.elementMaster.on('change', this.masterChange.bind(this));
		}
	}

	/**
	 * Render options for select
	 * @param {Array} data
	 * @returns {string} html options string
	 */
	renderOptions(data) {
		let html = '';
		for (let item of data) {
			html += `<option value=${item.value}>${item.text}</option>`;
		}
		return html;
	}

	/**
	 * Get options by value from data array
	 *
	 * @param {Array} values
	 * @returns {Array}
	 */
	getOptionsByValues(values) {
		let options = [];
		for (let value of values) {
			for (let item of this.data) {
				if (item.value === value) {
					item.children.forEach((child) => {
						options.push(child);
					});
				}
			}
		}
		return options;
	}

	/**
	 * Listen to master element change event
	 * @param {Event} e
	 */
	masterChange(e) {
		let value = $(e.target).val();
		if (!Array.isArray(value)) {
			value = [value];
		}
		this.elementSlave.html(this.renderOptions(this.getOptionsByValues(value)));
	}

}
