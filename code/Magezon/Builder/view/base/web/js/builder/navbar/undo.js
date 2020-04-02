define([
	'angular'
], function(angular) {

	var directive = function(magezonBuilderService, $timeout) {
		return {
			replace: true,
			templateUrl: function(elem, attrs) {
				return attrs.templateUrl ? attrs.templateUrl : magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/builder/navbar/undo.html')
			},
			controller: ['$rootScope', '$scope', function($rootScope, $scope) {
				$scope.undoProcessing = false;
				$scope.undo = function() {
			    	if (!$scope.undoProcessing && $rootScope.canUndo) {
			        	$scope.undoProcessing = true;
			        	$timeout(function() {
			        		$rootScope.numChronicle.undo();
							$scope.undoProcessing = false;
							$rootScope.draggingElement = '';
			        	}, 500);
			    	}
			    };
			}]
		}
	}

	return directive;
});