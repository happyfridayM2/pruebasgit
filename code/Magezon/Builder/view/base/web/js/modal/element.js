define([
	'jquery',
	'angular'
], function($, angular) {

	return {
		templateUrl: 'Magezon_Builder/js/templates/modal/form.html',
		controller: function(
			$rootScope, 
			$scope, 
			$uibModalInstance, 
			$controller, 
			modal,
			form,
			magezonBuilderForm,
			magezonBuilderFilter
		) {
			var parent = $controller('modalBaseController', {$scope: $scope, $uibModalInstance: $uibModalInstance, modal: modal, form: form});
			angular.extend(this, parent);

			var self     = this;
			var element  = form.element;
			var builder  = form.element.builder;
			$scope.title = element.builder.name;
			self.model   = angular.copy(form.element);
			self.element = form.element;

			$scope.$emit('enableModalSpinner');
			magezonBuilderForm.getForm('elements.' + element.type, function(tabs, result) {
				self.tabs = tabs;
				$scope.activeTab();
				builder.fields = result.fields;
				$scope.spinner = false;
				$scope.$emit('disableModalSpinner');
			});

			$scope.activeTab = function() {
				var activeIndex = 0;
				if (form.activeTab) {
					angular.forEach(self.tabs[0]['templateOptions']['children'], function(tab, index) {
						if (tab.templateOptions.elementId == form.activeTab) {
							activeIndex = index;
							return;
						}
					});
					self.tabs[0]['templateOptions']['activeTab'] = activeIndex;
				} else {
					self.tabs[0]['templateOptions']['activeTab'] = activeIndex;
				}
			}

			self.onSubmit = function() {
				var excludeFields = ['builder', 'elements'];
				var newData = self.model;
				angular.forEach(newData, function(value, key) {
					if ($.inArray(key, excludeFields)===-1) {
						if (angular.isString(value)) {
							value = magezonBuilderFilter.processContent(value);
						}
						element[key] = value;
					}
				});
				$uibModalInstance.close(element);
				$rootScope.$broadcast('addHistory', {
					type: 'edited',
					title: element.builder.name
				});
				$rootScope.$broadcast('editedElement', element);
		    }
		}
	}
});