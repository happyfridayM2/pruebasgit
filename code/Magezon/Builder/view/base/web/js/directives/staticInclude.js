define([
	'angular'
], function (angular) {
	var directive = function() {
		return {
			restrict: 'AE',
			templateUrl: function(elem, attrs){
				return attrs.source
			}
		}
	};
	return directive;
});