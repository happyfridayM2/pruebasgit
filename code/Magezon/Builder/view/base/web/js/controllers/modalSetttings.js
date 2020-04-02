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
	'elementManager',
	'magezonBuilderService',
	function(
		$rootScope,
		$scope,
		$timeout,
		$uibModalInstance,
		elementManager,
		magezonBuilderService
	) {
		var mgz   = this;
		mgz.title = 'Profile Settings';
		mgz.tabs  = [];

		magezonBuilderService.getBuilderConfig('profile.settings', function(result) {
			var newFields = angular.copy(result.form);
			var fields = FormlyUtils.processFields(newFields, 'children');
			magezonBuilderService.registerTypes(fields);
			magezonBuilderService.processFormFields(fields, function(tabs) {
				$timeout(function () {
					mgz.tabs = tabs;
					$scope.loadData();
				});
			});
		});

		$scope.loadData = function() {
			mgz.model = angular.copy($rootScope.currentProfile);
		}

		mgz.cancel = function() {
			$uibModalInstance.dismiss('cancel');
		}

		mgz.onSubmit = function() {
			angular.merge($rootScope.currentProfile, mgz.model);
			$rootScope.$broadcast('exportShortcode');
			$rootScope.$broadcast('loadStyles');
			$uibModalInstance.dismiss('cancel');
	    }
	}]
});