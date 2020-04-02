define([
	'angular'
], function(angular) {

	var directive = function(magezonBuilderService, $timeout) {
		return {
			replace: true,
			templateUrl: function(elem, attrs) {
				return attrs.templateUrl ? attrs.templateUrl : magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/builder/navbar/redo.html')
			},
			controller: ['$rootScope', '$scope', function($rootScope, $scope) {
				$scope.redoProcessing = false;
				$scope.redo = function() {
			    	if (!$scope.redoProcessing && $rootScope.canRedo) {
			        	$scope.redoProcessing = true;
			        	$timeout(function() {
			        		$rootScope.numChronicle.redo();
			        		$scope.redoProcessing = false;
			        		$rootScope.draggingElement = '';
			        	}, 500);
			    	}
			    };
			}]
		}
	}

	return directive;
});