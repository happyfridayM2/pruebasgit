define([
	'jquery',
	'angular',
	'Magezon_Builder/js/apply/main'
], function ($, angular, mage) {

function diff(obj1, obj2, exclude) {
    var r = {};

    if (!exclude) exclude = [];

    for (var prop in obj1) {
        if (obj1.hasOwnProperty(prop)) {
        	if (prop.startsWith("$") || prop == 'elements') continue;

            if (exclude.indexOf(obj1[prop]) == -1) {

                // check if obj2 has prop
                if (!obj2.hasOwnProperty(prop)) r[prop] = obj1[prop];

                // check if prop is object, if so, recursive diff
                else if (obj1[prop] === Object(obj1[prop])) {
                    if (obj2[prop] == undefined || obj2[prop] == null)
                        r[prop] = obj2[prop];
                    else {
                        var difference = diff(obj1[prop], obj2[prop]);
                        if (Object.keys(difference).length > 0) r[prop] = difference;
                    }
                }

                // check if obj1 and obj2 are equal
                else if (obj1[prop] !== obj2[prop]) {
                    if (obj1[prop] === undefined)
                        r[prop] = 'undefined';
                    if (obj1[prop] === null)
                        r[prop] = null;
                    else if (typeof obj1[prop] === 'function')
                        r[prop] = 'function';
                    else if (typeof obj1[prop] === 'object')
                        r[prop] = 'object';
                    else
                        r[prop] = obj1[prop];
                }
            }
        }

    }

    return r;
}

	var directive = function($interpolate, $sce, magezonBuilderService, $rootScope) {
		return {
			replace: true,
			scope: false,
			templateUrl: function(elem, attrs) {
				var template = attrs.templateUrl ? attrs.templateUrl : magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/builder/component.html');
				return template;
			},
			controller: ['$rootScope', '$scope', 'elementManager', function($rootScope, $scope, elementManager) {

				$scope.controlTemplateUrl = magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/builder/control.html');
				$scope.isActive = false;
				$scope.builderElement = elementManager.elements[$scope.element.type];
				if ($scope.builderElement) {
					$scope.template = $scope.builderElement.templateUrl;
				}

      			$scope.getControlTemplateUrl = function() {
      				return magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/builder/control.html');
      			}

      			$scope.getBuilderElement = function() {
      				return $rootScope.elementManager.getElement($scope.element.type);
      			}

      			$scope.getBuilderElementDescription = function() {
      				var builderElement = $rootScope.elementManager.getElement($scope.element.type);
      				return builderElement.builder_description ? $interpolate(builderElement.builder_description)($scope) : '';
      			}

      			$scope.getTrustedHtml = function(html) {
      				return $sce.trustAsHtml(magezonBuilderService.encodeDirectives(html));
      			}

      			$scope.getTitleHtml = function() {
  					if (!$scope.element.title_tag) $scope.element.title_tag = 'h4';
					var html = '<' + $scope.element.title_tag + ' class="title">';
						if ($scope.element.add_icon && $scope.element.icon_position == 'left') {
							html += '<i class="mgz-icon-element ' + $scope.element.icon + '"></i>';
						}
						if ($scope.element.title) {
							html += '<span>' + $scope.element.title + '</span>';
						}
						if ($scope.element.add_icon && $scope.element.icon_position == 'right') {
							html += '<i class="mgz-icon-element ' + $scope.element.icon + '"></i>';
						}
					html += '</' + $scope.element.title_tag + '>';
					return html;
				}

				$scope.trustAsResourceUrl = function(url) {
					return $sce.trustAsResourceUrl(url);
				}

				$scope.outsideClick = function() {
					$scope.isActive = false;
				}
			}],
			link: function(scope, element) {
				if (!$rootScope.draggingElement) {
					var builderElement = $rootScope.elementManager.getElement(scope.element.type);
					var initializing = true;
					scope.parents = [];
					if (scope.template) element.attr('template-url', scope.template);
					if (builderElement.livePreview) {
						element.siblings('.mgz-element-livepreview-content').addClass(scope.element.id + '-s');
					} else {
						element.addClass(scope.element.id + '-s');	
					}

					var prepareParents = function(_scope, _parents) {
						if (_scope.$parent) {
							if (_scope.element && _scope.element.id && !_parents.hasOwnProperty(_scope.element.id)) {
								_parents[_scope.element.id] = _scope;
							}	
							prepareParents(_scope.$parent, _parents);
						}
					}

					$(element).parent().hover(function() {
						var currentElem = $(this);
						// scope.element.component.hovered = true;
						// scope.element.component.hover = true;
						$(this).addClass('mgz-element-hover');
							var elements = [];
							var parents  = {};
							prepareParents(scope, parents);
							angular.forEach(parents, function(_scope) {
								elements.push(_scope.element);
								$('.' + _scope.element.id).children('.mgz-element-controls').addClass('mgz-hide');
							});
							elements.reverse();
							scope.parents = elements;
							scope.$digest();
							$('.mgz-element').hover(function() {
								$('.mgz-builder').removeClass('mgz-element-controls-hover');
							});
							$('.mgz-element-controls').hover(function() {
								$('.mgz-builder').addClass('mgz-element-controls-hover');
							}, function() {
								$('.mgz-builder').removeClass('mgz-element-controls-hover');
							});
							if ($(this).find('.mgz-element').length && $(this).find('.mgz-element-controls').length
								&& $(this).find('.mgz-element-hover').length
								) {
								$(this).children('.mgz-element-controls').addClass('mgz-hide');
							} else {
								$(this).children('.mgz-element-controls').removeClass('mgz-hide');
								var controlSelector = $(this).children('.mgz-element-controls');
								if (controlSelector.length && controlSelector.is(':visible')) {
									var navSelector    = $('#' + $rootScope.builderConfig.htmlId + ' .mgz-navbar');
									if (navSelector.length) {
										var navOffsetRight = navSelector.offset().left + navSelector.width();
										var controlSelectorOffsetRight = controlSelector.offset().left + controlSelector.width() + 2;
										if (controlSelectorOffsetRight > navOffsetRight) {
											controlSelector.css('right', '0');
											controlSelector.css('left', 'auto');
										} else {
											controlSelector.css('right', 'auto');
											if (currentElem.css('position') == 'static') {
												controlSelector.css('left', currentElem.position().left);
											} else {
												controlSelector.css('left', '0');
											}
										}
									}
								}
							}
					}, function() {
						$(this).removeClass('mgz-element-hover');
						scope.parents = [];
						//scope.element.component.hover = false;
						scope.$digest();
					});

					var innerSelector  = $(element).next('.mgz-element-livepreview-content');
					if (builderElement.livePreview) {

						scope.$watch('element', function(newValue, oldValue) {
							var difference = diff(newValue, oldValue,
								[ newValue.component
								]);

							if (!initializing && Object.keys(difference).length === 0) return;

							scope.element.component.loading = true;
							$.ajax({
								url: magezonBuilderService.getUrl($rootScope.builderConfig.loadElementUrl),
								type:'POST',
								data: {
									element: scope.element
								},
								success: function(res) {
									if (res) {
										$(element).hide();
										innerSelector.html(res);
										$(mage.apply);
										innerSelector.on('click', '.action.tocart,.action.towishlist,.action.tocompare', function (e) {
											return false;
										});
										innerSelector.find('a').attr('target', '_blank');
										innerSelector.find('form[data-role=tocart-form]').removeAttr('action');
										innerSelector.find('form[data-role=tocart-form]').removeAttr('method');
										innerSelector.find('*').addClass('mgz-builder-dnd-disable');
										$('body').trigger('magezonPageBuilderUpdated');
										setTimeout(function() {
											element.parent().find('.mgz-waypoint').trigger('mgz:animation:run');
										}, 1000);
									}
									scope.element.component.loading = false;
								},
								error: function(jq, status, message) {
									scope.element.component.loading = false;
								}
							});

							initializing = false;
						}, true);
					}
				}
			}
		}
	};
	return directive;
});