define([
	'jquery',
	'angular',
	'underscore',
	'Magezon_Builder/js/factories/FormlyUtils'
], function ($, angular, _, FormlyUtils) {

	var componentListDir = function(magezonBuilderService, $timeout) {
		return {
			replace: true,
			scope: {
				element: '=',
				rootList: '=?',
        		allowedTypes: '=?'
			},
			templateUrl: function(elem, attrs) {
				return attrs.templateUrl ? magezonBuilderService.getViewFileUrl(attrs.templateUrl) : magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/builder/list.html')
			},
			controller: ['$rootScope', '$scope', 'elementManager', '$uibModal', function($rootScope, $scope, elementManager, $uibModal) {

				if (!$scope.element.elements) $scope.element.elements = [];

				if (!$scope.allowedTypes) {
					$scope.allowedTypes = $scope.element.component ? $scope.element.component.allowedTypes : [];
				}

				$scope.parentElement = $scope.element;

				$scope.elementCache = {};

				$scope.$on('rootAddComponent', function(event, builderElement) {
					var openModal      = builderElement.hasOwnProperty('openModal') ? builderElement.openModal : true;
					var additionalData = builderElement.additionalData;
					if ($scope.rootList) {
						if (builderElement.type === $rootScope.builderConfig.mainElement) {
							$scope.addComponent($rootScope.builderConfig.mainElement, null, openModal, additionalData);
						} else {
							var row = $scope.addComponent($rootScope.builderConfig.mainElement, null, false);
							$scope.addComponent(builderElement.type, row['elements'][0], openModal, additionalData);
						}
					}
				});

				$scope.$on('rootAddRowComponent', function(event, columns) {
					if ($scope.rootList) {
						var row            = $scope.addComponent($rootScope.builderConfig.mainElement, null, false);
						var builderElement = $rootScope.elementManager.getElement($rootScope.builderConfig.mainElement);
						var viewMode       = magezonBuilderService.getViewMode();
						var types          = columns.split('_');
						for (var i = 0; i < types.length - 1; i++) {
							var _type   = types[i].split('');
							var element = $scope.addComponent(builderElement.children, row);
							var width;
							if (_type[1]==5) {
								width = 15
							} else {
								width = 12 * _type[0] / _type[1];
							}
							element['xl_size'] = '';
							element['lg_size'] = '';
							element['md_size'] = width;
							element['sm_size'] = '';
							element['xs_size'] = '';

						}
						// First Column
						row['elements'][0]['xl_size'] = '';
						row['elements'][0]['lg_size'] = '';
						row['elements'][0]['md_size'] = width;
						row['elements'][0]['sm_size'] = '';
						row['elements'][0]['xs_size'] = '';
						$rootScope.$broadcast('setViewMode', 'xl');
					}
				});

				$scope.$on('addChildrenComponent', function(event, data) {
					if ($scope.element === data.targetElement) {
						var type = data.type;
						if (!type) {
							type = $rootScope.elementManager.getElement(data.targetElement).children;
						}
						$scope.addComponent(type, data.targetElement, true);
					}
				});

				// Remove Component
				$scope.removeComponent = function(element) {
					$rootScope.$broadcast('removeElement', element);
				}

				$scope.$on('removeElement', function(event, element) {
					$rootScope.draggingElement = '';
					angular.forEach($scope.element.elements, function(_element, index) {
						if (_element.id === element.id && !_element.component.isNew) {
							$scope.element.elements.splice(index, 1);
							$rootScope.$broadcast('removeParentElement', $scope.element);
							$rootScope.$broadcast('afterRemoveComponent', element);
							return true;
						}
					});
				});

				$scope.$on('removeParentElement', function(event, parentElement) {
					var index = $scope.element.elements.indexOf(parentElement);
					if (index !== -1) {
						var builderElement = $rootScope.elementManager.getElement(parentElement);
						if (builderElement.children && !parentElement.elements.length) {
							$scope.element.elements.splice(index, 1);
						}
					}
				});

				// Add Component
				$scope.addComponent = function(type, parent, openModal, additionalData) {
					var builderElement = elementManager.elements[type];
					if (!builderElement) return;
					var element = elementManager.prepareElement(builderElement.type);
					if (additionalData) {
						element = angular.extend(element, additionalData);
					}
					if (builderElement.control) element['elem_name'] = $scope.getElemName(element.type);

					if (builderElement.children) {
						if (!element.elements.length) {
							var childrenCount  = builderElement.childrenCount ? builderElement.childrenCount : 1;
							for (var i = 0; i < childrenCount; i++) {
								var newElement = $scope.addComponent(builderElement.children, element);
								if (newElement && newElement.hasOwnProperty('title')) {
									newElement['title'] = newElement['title'] + ' ' + (i+1);
								}
							}
						}
					}

					var isParent = _.find(elementManager.elements, function (elem) {
						return elem.children == element.type;
					});

					element.component.append = isParent ? true : false;

					if (parent) {
						parent.elements.push(element);
					} else {
						$scope.element.elements.push(element);
					}

					if (openModal && (builderElement.hasOwnProperty('newOpenModal') && builderElement.newOpenModal || !builderElement.hasOwnProperty('newOpenModal'))) {
						$scope.editComponent(element);	
					}

					$rootScope.$broadcast('afterAddComponent', element);

					return element;
				}

				$scope.addChildrenComponent = function(parentElement) {
					var builderElement = $rootScope.elementManager.getElement(parentElement);
					var newElement     = $scope.addComponent(builderElement.children, parentElement);
				}

				// Edit Component
				$scope.editComponent = function(element, activeTab) {
					var result = $uibModal.open({
						templateUrl: magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/modal/form.html'),
						controller: 'magezonBuilderModalElement',
						controllerAs: 'mgz',
						openedClass: 'mgz-modal-open',
						windowClass: 'mgz-modal mgz-builder-element-form mgz-builder-' + element.type + '-modal',
						resolve: {
							formData: {
								element: element,
								activeTab: activeTab
							}
						}
					}).result.then(function() {}, function() {});
					$rootScope.$broadcast('editComponent', element);

				}

				// Clone Component
				$scope.cloneComponent = function(element) {
					$rootScope.$broadcast('cloneComponent', element);
				}

				$scope.$on('cloneComponent', function(event, element) {
					$scope.cloneElement(element);
				});

				$scope.cloneElement = function(element) {
					var index = $scope.element.elements.indexOf(element);
					if (index !== -1) {
						var newComponent = angular.copy(element);
						var index        = $scope.element.elements.indexOf(element);
						var generateId = function(elements) {
							angular.forEach(elements, function(_element) {
								var _builderElement = $rootScope.elementManager.getElement(_element);
								if (_builderElement.control) _element['elem_name'] = $scope.getElemName(_element.type);
								_element['id']        = elementManager.getUniqueId();
								_element['component'] = elementManager.getComponentDefault(_element.type);
								_element['elements']  = generateId(_element.elements);
							});
							return elements;
						}
						var builderElement = $rootScope.elementManager.getElement(element);
						if (builderElement.control) newComponent['elem_name'] = $scope.getElemName(element.type);
						newComponent['id']        = elementManager.getUniqueId();
						newComponent['component'] = elementManager.getComponentDefault(element.type);
						newComponent.elements     = generateId(newComponent.elements);
						$scope.element.elements.splice(index + 1, 0, newComponent);
						$rootScope.$broadcast('afterCloneComponent', element);
					}
				}

				// Drop Component
				$scope.dropComponent = function(element, index, parentElement) {
					element.component.isNew = true;
					$rootScope.$broadcast('removeElement', element);
					parentElement.elements.splice(index, 0, element);
					$rootScope.$broadcast('afterDropComponent', element);
					$timeout(function() {
						element.component.isNew = false;	
					})
					return true;
				}

				$scope.onDragstart = function(element) {
					$rootScope.$broadcast('onDragstart', element);
				}

				$scope.onDragend = function(element) {
					$rootScope.$broadcast('onDragend', element);
				}

				$scope.onCanceled = function(element) {
					$rootScope.$broadcast('onCanceled', element);
				}

      			$scope.getPlaceHolderClassess = function() {
      				if ($rootScope.draggingElement) {
      					var element = $rootScope.draggingElement;
      					var classes = $scope.getViewModeClass(element);
      					if (element.el_float) classes += ' f-' + element.el_float;
      					return classes;
      				}
      			}

				$scope.getElementClasess = function(element) {
					var classes = element.id + ' mgz-element mgz-element-' + element.type + ' ';
					var viewModeClasses = $scope.getViewModeClass(element);
					if (viewModeClasses) classes += viewModeClasses;
					var index          = $scope.element.elements.indexOf(element);
					var builderElement = $rootScope.elementManager.getElement(element);

					if (element.disable_element) {
						classes += ' mgz-element-disabled';
					}

					if (index === 0) {
						classes += ' mgz-element-first';
					}

					if (index === $scope.element.elements.length-1) {
						classes += ' mgz-element-last';
					}

					if (builderElement && builderElement.is_collection) {
						classes += ' mgz-element-collection';
					}

					if (builderElement.livePreview) {
						classes += ' mgz-element-livepreview';
					}

					if (element.hidden_default) {
						classes += ' mgz-element-hide-default';
					}

					if (element.title_align) {
						classes += ' mgz-element-title-align-' + element.title_align;
					}

					if (element.equal_height) {
						classes += ' mgz-row-equal-height';

						if (element.content_position) {
							classes += ' content-' + element.content_position;
						}
					}

					if (element.full_height) {
						classes += ' mgz-row-full-height';
					}

					if (element.component.active) {
						classes += ' _active';
					}

					if (element.elements.length) {
						classes += ' has-children';
					}

					if (element.submenu_position) {
						classes += ' item-submenu-position-' + element.submenu_position;
					}

					if (element.submenu_type) {
						classes += ' submenu-' + element.submenu_type;
					}

					if (element.el_float) {
						classes += ' f-' + element.el_float;
					}

					return classes;
				}

				$scope.getViewModeClass = function(element) {
					var classes    = '';
					var viewMode   = magezonBuilderService.getViewMode();
					var modeSize   = viewMode + '_size';
					var modeOffset = viewMode + '_offset_size';
					var modeHide   = viewMode + '_hide';

					// Inherit from smaller
					var responsiveClass = element[modeSize];
					if (!responsiveClass) {
						var start = false;
						var sizes = ['xl', 'lg', 'md', 'sm', 'xs'];
						angular.forEach(sizes, function(v, k) {
							if (v == viewMode) {
								start = true;
							}
							if (start && element[v + '_size']) {
								responsiveClass = element[v + '_size'];
								start = false;
							}
						});
					}
					if (responsiveClass) {
						classes = ' mgz-col-xs-' + responsiveClass;
					}

					if (element[modeOffset]) classes += ' mgz-col-xs-offset-' + element[modeOffset];
					if (element[modeHide]) classes += ' mgz-hide';

					if (!classes) {
						classes = 'mgz-col-xs-12';
					}
					return classes;
				}

				$scope.$on('changeElementLayout', function(event, data) {
					if ($scope.element === data.parent) {
						var viewMode = magezonBuilderService.getViewMode();
						var types    = data.type.split('_');
						for (var i = 0; i < types.length; i++) {
							var _type   = types[i].split('');
							var element = $scope.element.elements[i];
							if (!element) {
								element = $scope.addComponent('column', $scope.element);
							}
							var width;
							if (_type[1]==5) {
								width = 15
							} else {
								width = 12 * _type[0] / _type[1];
							}
							element['xl_size'] = '';
							element['lg_size'] = '';
							element['md_size'] = width;
							element['sm_size'] = '';
							element['xs_size'] = '';
						}
						$rootScope.$broadcast('setViewMode', 'xl');
					}
				});

				$scope.changeElementLayout = function(parent, type) {
					$rootScope.$broadcast('changeElementLayout', {
						parent: parent,
						type: type
					});
				}

				$scope.addChildComponent = function(element) {
					magezonBuilderService.setTargetElement(element);
					magezonBuilderService.setTargetAction('addChildren');
      				$rootScope.$broadcast('openElementsModal', element);
      			}

				$scope.replaceComponent = function(element) {
					if (magezonBuilderService.canReplace(element)) {
						magezonBuilderService.setTargetElement(element);
						magezonBuilderService.setTargetAction('replace');
      					$rootScope.$broadcast('openElementsModal', element);
      				}
      			}

				$scope.$on('replaceComponent', function(event, data) {
					var index = $scope.element.elements.indexOf(data.targetElement);
					if (index !== -1) {
						$scope.removeComponent(data.targetElement);
						var newElement = $scope.addComponent(data.type);
						var newIndex   = $scope.element.elements.indexOf(newElement);
						$scope.element.elements.splice(newIndex, 1);
						$scope.element.elements.splice(index, 0, newElement);
						$scope.editComponent(newElement);
					}
				});

				$scope.addNextComponent = function(element) {
					magezonBuilderService.setTargetElement(element);
					magezonBuilderService.setTargetAction('addNext');
      				$rootScope.$broadcast('openElementsModal', element);
      			}

				$scope.$on('addNextComponent', function(event, data) {
					var index = $scope.element.elements.indexOf(data.targetElement);
					if (index !== -1) {
						var component = $scope.addComponent(data.type, null, true);
						$scope.element.elements.splice($scope.element.elements.length-1, 1);
						$scope.element.elements.splice(index + 1, 0, component);
					}
				});

				$scope.copyComponent = function(element) {
					$rootScope.copyElement = element;
				}

				$scope.pasteComponent = function(elemt) {
					var builderElement = $rootScope.elementManager.getElement(elemt);
					if ($rootScope.copyElement && $rootScope.copyElement!=elemt && builderElement.is_collection) {
						var newComponent = angular.copy($rootScope.copyElement);
						var generateId = function(elements) {
							angular.forEach(elements, function(_element) {
								var _builderElement = $rootScope.elementManager.getElement(_element);
								if (_builderElement.control) _element['elem_name'] = $scope.getElemName(_element.type);
								_element['id']       = elementManager.getUniqueId();
								_element['elements'] = generateId(_element.elements);
							});
							return elements;
						}
						if (builderElement.control) newComponent['elem_name'] = $scope.getElemName(elemt.type);
						newComponent['id'] = elementManager.getUniqueId();
						newComponent.elements = generateId(newComponent.elements);
						elemt.elements.push(newComponent);
					}
				}

				$scope.canPasteComponent = function(elemt) {
					if ($rootScope.copyElement && $rootScope.copyElement!=elemt) {
						var builderElement = $rootScope.elementManager.getElement(elemt);
						var allowedTypes   = builderElement.hasOwnProperty('allowed_types') ? builderElement.allowed_types : [];
						if (allowedTypes && allowedTypes.indexOf($rootScope.copyElement.type)!==-1) {
							return true;
						}
					}
					return false;
				}

				$scope.$watch('element', function() {
					$scope.$broadcast('parentUpdate', $scope.element);	
				}, true);

				$scope.getElemName = function(type) {
		            if (!$.isArray($scope.elementCache[type])) {
		                $scope.elementCache[type] = [];
		            }

		            var elemName = type + '-' + $scope.getRandomInt();
		            elemName = elemName.replace('bfb_', '');

		            while (($.inArray(elemName, $scope.elementCache[type]) !== -1)) {
		                elemName = type + '-' + $scope.getRandomInt();
		            }
		            $scope.elementCache[type].push(elemName);

		            return elemName;
		        }

		        $scope.getRandomInt = function() {
		            var min = 100;
		            var max = 1000;
		            return Math.floor(Math.random() * (max - min + 1) + min);
		        }

		        $scope.openViewShortcodeModal = function(element) {
					var result = $uibModal.open({
						templateUrl: magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/modal/form.html'),
						controller: 'shortcodeModal',
						controllerAs: 'mgz',
						openedClass: 'mgz-modal-open',
						windowClass: 'mgz-modal mgz-builder-element-form',
						resolve: {
							formData: {
								element: element
							}
						}
					}).result.then(function() {}, function() {});
				}

				$scope.outsideClick = function(element) {
					if (!$('.mgz-modal').length) {
						element.component.active = false;
					}
				}
			}]
		}
	};

	return componentListDir;
});