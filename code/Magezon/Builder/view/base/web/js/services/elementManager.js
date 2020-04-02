define([
	'jquery',
	'angular',
	'Magezon_Builder/js/factories/FormlyUtils',
    'mage/adminhtml/events'
], function ($, angular, FormlyUtils, varienGlobalEvents) {

	var registerTypes = function(array, formlyConfigProvider) {
		for (var i = 0; i < array.length; i++) {
			var elem = array[i];
			// DynamicRows
			if (elem.templateOptions.children) {
				registerTypes(elem.templateOptions.children, formlyConfigProvider);
			}
			if (elem.hasOwnProperty('fieldGroup')) {
				formlyConfigProvider.setWrapper({
					name: elem.wrapper,
					templateUrl: elem.templateOptions.templateUrl
				});
				registerTypes(elem['fieldGroup'], formlyConfigProvider);
			} else {
				var newType = {
					name: elem.type,
					templateUrl: elem.templateOptions.templateUrl
				};
				if (elem.defaultOptions) {
					newType.defaultOptions = Object.assign({}, elem.defaultOptions);
				}
				formlyConfigProvider.setType(newType);
			}
			if (elem.templateOptions.wrapperTemplateUrl) {
				formlyConfigProvider.setWrapper({
					name: elem.data.wrapperType,
					templateUrl: elem.templateOptions.wrapperTemplateUrl
				});
			}
		}
	}

	var array_diff = function(arr1) {
		var retArr = {}
		var argl = arguments.length
		var k1 = ''
		var i = 1
		var k = ''
		var arr = {}

		  	arr1keys: for (k1 in arr1) {
		  		for (i = 1; i < argl; i++) {
		  			arr = arguments[i]
		  			for (k in arr) {
		  				if (arr[k] === arr1[k1]) {
		          			continue arr1keys;
		      			}
		  			}
		  			retArr[k1] = arr1[k1]
				}
			}
		return retArr
	}

	var array_values = function (input) {
		var tmpArr = []
		var key = ''

		for (key in input) {
			tmpArr[tmpArr.length] = input[key]
		}

		return tmpArr
	}

	var elementManagerService = function($rootScope) {
		this.elements = {};
		this.groups   = {};

		this.registerElements = function(elements) {
			var newElements = {};
			angular.forEach(elements, function(element, index) {
				if (!element.hasOwnProperty('disabled') || !element['disabled']) {
					newElements[index] = element;
				}
			});
			this.elements = newElements;
		}

		this.registerGroup = function(name, group) {
			if (!this.groups[name]) {
				this.groups[name] = group;
			} else {
				angular.extend(this.groups[name], group);
			}
		}

		this.registerElement = function(name, element) {
			if (element.tabs) {
				element.tabs = FormlyUtils.processFields(element.tabs, 'children');
				registerTypes(element.tabs, formlyConfigProvider);
			}
			if (!this.elements[name]) {
				var processFields = function(array) {
					angular.forEach(array, function(row) {
						if (row.fieldGroup) {
							processFields(row.fieldGroup);
						}
					})
				}
				processFields(element.tabs);
				this.elements[name] = element;
			} else {
				angular.extend(this.elements[name], element);
			}
		}

		this.processAllowTypes = function() {
			$allTypes      = [];
			angular.forEach(this.elements, function(element, index) {
				$allTypes.push(element.type);
			});

			$childrenTypes = [];
            angular.forEach(this.elements, function(element, index) {
				$allowedTypes  = element.allowed_types;
				$excludedTypes = element.excluded_types;

                if (angular.isString($allowedTypes)) {
                    element.allowed_types = $allowedTypes.split(',');
                }

                if (!element.allowed_types && $excludedTypes) {
                    if (angular.isString($excludedTypes)) {
                        $excludedTypes = $excludedTypes.split(',');
                    }
                    $allowedTypes = array_diff($allTypes, $excludedTypes);
                    element.allowed_types = array_values($allowedTypes);
                }

                if (element.children) {
                    $childrenTypes.push(element.children);
                }
            });

            $validTypes = array_diff($allTypes, $childrenTypes);
            angular.forEach(this.elements, function(element, index) {
                $allowedTypes  = element.allowed_types;
                if ($allowedTypes) {
                    if ($.inArray( element.children, $allowedTypes) === -1) {
						$_allowedTypes        = array_diff($allowedTypes, $childrenTypes);
						element.allowed_types = array_values($_allowedTypes);
                    }
                } else {
                    element.allowed_types = $validTypes;
                }
            });
		}

		this.getElement = function(type) {
			if (angular.isObject(type)) {
				type = type.type;
			}
			return this.elements[type];
		}

		this.prepareElements = function(elements) {
			var self = this;
			angular.forEach(elements, function(_element) {
				_element['id']        = self.getUniqueId();
				_element['component'] = self.getComponentDefault(_element.type);
				_element['elements']  = self.prepareElements(_element.elements);
			});
			return elements;
		}

		this.prepareElement = function(type) {
			var builderElement   = this.getElement(type);
			var element          = angular.copy(builderElement.defaultValues);
			element['id']        = this.getUniqueId();
			element['type']      = type;
			element['component'] = this.getComponentDefault(type);
			element['control']   = false;
			element['hover']     = false;
			element['hovered']   = false;
			element['loading']   = false;
			element['elements']  = [];
			return element;
		}

		this.getComponentDefault = function(type) {
			var builderElement = this.getElement(type);
			var allowedTypes = builderElement.hasOwnProperty('allowed_types') ? builderElement.allowed_types : [];
	        return {
	        	active: false,
	            visible: true,
	            control: true,
	            allowedTypes: allowedTypes
	        };   
	    }

	    this.updateElementConfig = function(type) {
	    	var self = this;
	    	if (!self.elements[type]['loadConfig']) {
		    	var self = this;
		    	var requestKey = 'elements.' + type;
		    	$.ajax({
	                url: this.getUrl('mgzbuilder/ajax/loadConfig'),
	                type:'POST',
	                data: {
	                	key: requestKey,
	                	class: $rootScope.builderConfig.builderClass
	                },
	            	dataType: 'json',
	                success: function(data) {
	            		self.elements[type]['fields'] = data.config.fields;
	            		self.elements[type]['loadConfig'] = true;
	                }
	            });
		    }
	    }

		this.getUrl = function(url) {
			return $rootScope.builderConfig.baseUrl + url;
		}

		this.getUniqueId = function (size) {
	        var code = Math.random() * 25 + 65 | 0,
	            idstr = String.fromCharCode(code);

	        size = size || 12;

	        while (idstr.length < size) {
	            code = Math.floor(Math.random() * 42 + 48);

	            if (code < 58 || code > 64) {
	                idstr += String.fromCharCode(code);
	            }
	        }

	        return idstr.toLowerCase();
	    }
	}

	return elementManagerService;
});