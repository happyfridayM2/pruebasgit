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
	'formlyConfig',
	'profileManager',
	function(
		$rootScope,
		$scope,
		$timeout,
		$uibModalInstance,
		magezonBuilderService,
		formlyConfig,
		profileManager
	) {
		var mgz   = this;
		mgz.title = 'View Shortcode';
		mgz.tabs  = [];
		mgz.model = {};
		mgz.model['shortcode'] = angular.toJson(profileManager.getJsonElements());

		magezonBuilderService.getBuilderConfig('shortcode', function(result) {
			var newFields = angular.copy(result.form);
			var fields = FormlyUtils.processFields(newFields, 'children');
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
			try {
				var elements = angular.fromJson(mgz.model.shortcode);
				$rootScope.profile.elements = [];
				angular.forEach(elements, function(element) {
					$rootScope.profile.elements.push(element);
				});
				$timeout(function() {
					$rootScope.$broadcast('importShortcode');
					$uibModalInstance.dismiss('cancel');
				}, 100);
			} catch (e) {
				alert('Invalid JSON string');
			}
	    }
	}]
});