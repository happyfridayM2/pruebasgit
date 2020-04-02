define([
	'jquery',
	'angular',
	'codemirror',
	'Magezon_Builder/js/factories/FormlyUtils',
	'jquery/ui'
], function($, angular, CodeMirror, FormlyUtils) {
	window.CodeMirror = CodeMirror;

return [
	'$rootScope',
	'$scope',
	'$timeout',
	'$uibModalInstance',
	'elementManager',
	'magezonBuilderService',
	'formData',
	'formlyConfig',
	function(
		$rootScope,
		$scope,
		$timeout,
		$uibModalInstance,
		elementManager,
		magezonBuilderService,
		formData,
		formlyConfig
	) {
		//console.time('START1');
		var mgz             = this;
		var element         = formData.element;
		var builderElement  = elementManager.getElement(element.type);
		mgz.title           = builderElement.name + ' Settings';
		//mgz.icon          = builderElement.icon;
		mgz.tabs            = {};
		mgz.model           = angular.copy(formData.element);
		var tabs            = angular.copy(builderElement.tabs);

		$scope.element = formData.element;

		mgz.visible = false;

		// Lazy Load
		var cacheTag  = 'elements.' + element.type + '.form';
		var cacheData = magezonBuilderService.getFromCache(cacheTag);
		// if (cacheData) {
		// 	$timeout(function () {
		// 		mgz.tabs = cacheData;
		// 		$scope.activeTab();
		// 		//console.timeEnd('START1');
		// 	});
		// } else {
			elementManager.updateElementConfig(element.type);
			magezonBuilderService.getBuilderConfig('elements.' + element.type, function(result) {
				var newFields = angular.copy(result.form);
				var fields = FormlyUtils.processFields(newFields, 'children');
				magezonBuilderService.registerTypes(fields, formlyConfig);
				magezonBuilderService.processFormFields(fields, function(tabs) {
					$timeout(function () {
						magezonBuilderService.saveToCache(cacheTag, angular.copy(tabs));
						mgz.tabs = tabs;
						$scope.activeTab();
					});
				});
			});
		//}

		//Instant Load
		// $scope.onSuccess = function(fields) {
		// 	$timeout(function() {
		// 		$scope.$apply(function () {
		// 			mgz.tabs = fields;
		// 		});
		// 		$scope.activeTab();
		// 	}, 400);
		// }
		// var config = magezonBuilderService.processFormFields(tabs, $scope.onSuccess);

		$scope.activeTab = function() {
			var activeIndex = 0;
			if (formData.activeTab) {
				angular.forEach(mgz.tabs[0]['templateOptions']['children'], function(tab, index) {
					if (tab.templateOptions.wrapperClass.indexOf("mgz-element-" + formData.activeTab) !=-1) {
						activeIndex = index;
						return;
					}
				});
				mgz.tabs[0]['templateOptions']['activeTab'] = activeIndex;
			} else {
				mgz.tabs[0]['templateOptions']['activeTab'] = activeIndex;
			}
		}

		mgz.cancel = function() {
			$uibModalInstance.dismiss('cancel');
		}

		mgz.onSubmit = function() {
			var excludeFields = ['component', 'elements'];
			var newData = mgz.model;
			angular.forEach(newData, function(value, key) {
				if ($.inArray(key, excludeFields)===-1 && $.inArray(key, builderElement.fields)!==-1) {
					element[key] = value;
				}
			});
			$uibModalInstance.dismiss('cancel');
			$rootScope.$broadcast('afterSaveForm');
			//$rootScope.$broadcast('parentChanged', element);
	    }

		mgz.replace = function() {
			mgz.cancel();
			magezonBuilderService.setTargetElement($scope.element);
			magezonBuilderService.setTargetAction('replace');
			$rootScope.$broadcast('openElementsModal', element);
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

		$rootScope.$on('showField', function(event, data) {
			var indexesMap = {};
			var valuesMap = {};

			angular.forEach(data.groupsConfig, function (fields, group) {
                angular.forEach(fields, function (index) {
                    if (!indexesMap[index]) {
                        indexesMap[index] = [];
                    }
                    indexesMap[index].push(group);
                });
            });

            angular.forEach(indexesMap, function (groups, field) {
				var visible   = (groups.indexOf(data.value) !== -1);
				var field     = mgz.findField(mgz.tabs, field);
				if (field) {
					var hide = !visible;
					field['status'] = !hide;
					if (hide) {
						$('.mgz-element-' + field.key).hide();
					} else {
						$('.mgz-element-' + field.key).show();
					}
					setTimeout(function() {
						if (hide) {
							$('.mgz-element-' + field.key).hide();
						} else {
							$('.mgz-element-' + field.key).show();
						}
					}, 200);
				}
            });
		});

		mgz.findField = function(fieldGroup, value) {
			var result;
			angular.forEach(fieldGroup, function(field, index) {
				if ((field.className.indexOf('mgz-element-' + value + ' ') !== -1) || (field.templateOptions.wrapperClass && field.templateOptions.wrapperClass.indexOf('mgz-element-' + value + ' ') !== -1)) {
					result = field;
					return;
				}
				if (field.fieldGroup && !result) {
					result = mgz.findField(field.fieldGroup, value);
				}
				if (field.templateOptions.children && !result) {
					result = mgz.findField(field.templateOptions.children, value);
				}
			});
			return result;
		}

		$timeout(function() {
			$rootScope.$broadcast('loadDirectives', true);
		}, 500);


		// function resizeModal() {
		// 	var headerPosition = $('.mgz-builder-element-form .modal-content').offset().top;
		// 	var tabHeight      = $('.mgz-builder-element-form .nav.nav-tabs').outerHeight();
		// 	var headerHeight   = $('.mgz-builder-element-form .mgz-modal-header').outerHeight();
		// 	var footerHeight   = $('.mgz-builder-element-form .mgz-modal-footer').outerHeight();
		// 	var height         = $(window).height() - tabHeight - headerHeight - footerHeight - 30 - 60;
		// 	console.log(height);
		// 	if (height < $('.mgz-builder-element-form .mgz-elements-wrapper > ul').height()) {
		// 		$('.mgz-builder-element-form .modal-content').height(height);
		// 	} else {
		// 		$('.mgz-builder-element-form .mgz-elements-wrapper').css('height', '');
		// 	}
		// }

		// $uibModalInstance.rendered.then(function() {
		// 	resizeModal();
	 //    });

	 //    $(window).resize(function(event) {
	 //    	if ($('.mgz-builder-element-form').is(':visible')) {
	 //    		resizeModal();
	 //    	}
	 //    });
	}]
});