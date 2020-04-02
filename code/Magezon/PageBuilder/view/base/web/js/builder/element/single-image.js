define([
	'angular'
], function(angular) {

	var directive = function(magezonBuilderService) {
		return {
			templateUrl: function(elem, attrs) {
				var templateUrl = elem.parent().attr('template-url');
				if (!templateUrl) templateUrl = 'Magezon_PageBuilder/js/templates/builder/element/single-image.html';
				return magezonBuilderService.getViewFileUrl(templateUrl);
			},
			controller: ['$scope', function($scope) {

				$scope.getSrc = function() {
					var src;

					switch($scope.element.source) {
						case 'media_library':
							if ($scope.element.image)
							src = magezonBuilderService.getImageUrl($scope.element.image);
							break;

						case 'external_link':
							if ($scope.element.custom_src)
							src = $scope.element.custom_src;
							break;
					}

					return src;
				}
			}]
		}
	}

	return directive;
});