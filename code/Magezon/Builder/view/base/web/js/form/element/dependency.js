define([
	'angular',
	'jquery'
], function(angular, $) {

	return {
		controller: function($scope, $timeout) {
			var initializing = true;
			$scope.$watch('model.' + $scope.options.key, function(value) {
				if (initializing) {
					$timeout(function() { initializing = false; });
				} else {
					if ($scope.to.values && $scope.to.values.hasOwnProperty(value)) {
						angular.forEach($scope.to.values[value], function(value, key) {
							$scope.model[key] = value;
						});
					}
				}
			});
		}
	}
});