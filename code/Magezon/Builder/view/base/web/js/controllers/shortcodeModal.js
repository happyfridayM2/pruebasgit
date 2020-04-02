define([
	'jquery',
	'angular',
	'Magezon_Builder/js/factories/FormlyUtils'
], function($, angular, FormlyUtils) {
	return [
	'$rootScope',
	'$scope',
	'$timeout',
	'$uibModalInstance',
	'magezonBuilderService',
	'formData',
	'formlyConfig',
	'profileManager',
	'elementManager',
	function(
		$rootScope,
		$scope,
		$timeout,
		$uibModalInstance,
		magezonBuilderService,
		formData,
		formlyConfig,
		profileManager,
		elementManager
	) {
		var mgz   = this;
		mgz.title = 'View Shortcode';
		mgz.tabs  = [];
		var element = angular.copy(formData.element);
		delete element['component'];
		delete element['elements'];
		mgz.model = {};
		mgz.model['shortcode'] = angular.toJson(element);

		elementManager.updateElementConfig(element.type);
		magezonBuilderService.getBuilderConfig('shortcode', function(result) {
			var newFields = angular.copy(result.form);
			var fields    = FormlyUtils.processFields(newFields, 'children');
			magezonBuilderService.registerTypes(fields, formlyConfig);
			magezonBuilderService.processFormFields(fields, function(tabs) {
				$timeout(function () {
					mgz.tabs = tabs;
				});
			});
		});

		mgz.cancel = function() {
			$uibModalInstance.dismiss('cancel');
		}

		mgz.onSubmit = function() {
			var excludeFields = ['component', 'elements'];
			try {
				var data = angular.fromJson(mgz.model.shortcode);
				if (data.type) {
					if (elementManager.getElement(data.type)) {
						var builderElement = elementManager.getElement(data.type);
						var defaultValues  = angular.copy(builderElement.defaultValues);
						var newData        = angular.merge(defaultValues, data);
						angular.forEach(builderElement.fields, function(field, key) {
							if (data.hasOwnProperty(field)) {
								newData[field] = data[field];
							}
						});
						var oldBuilderElement = elementManager.getElement(element.type);
						angular.forEach(oldBuilderElement.fields, function(field, key) {
							delete element[field];
						});
						angular.forEach(newData, function(value, key) {
							formData.element[key] = value;
						});
						$rootScope.$broadcast('loadStyles');
						$uibModalInstance.dismiss('cancel');
					} else {
						alert('Type "' + data.type + '" is not exist');
					}
				} else {
					alert('Type field is required');
				}
				
			} catch (e) {
				alert('Invalid JSON string');
			}
	    }

	    $uibModalInstance.rendered.then(function() {
	    	$('.mgz-builder-element-form .modal-content').resizable({
	    		minHeight: 300,
	    		minWidth: 380,
	    		resize: function( event, ui ) {
	    			var headerHeight = $('.mgz-builder-element-form .nav.nav-tabs').outerHeight();
	    			var footerHeight = $('.mgz-builder-element-form .mgz-modal-footer').outerHeight();
	    			var height = ui.size.height - headerHeight - footerHeight - 40;
	    			$('.mgz-builder-element-form .tab-content').height(height);
	    		}
	    	});
	    	$('.mgz-builder-element-form .modal-dialog').draggable({
	    		handle: ".mgz-modal-header-inner"
	    	});
	    });
	}]
});