define([
	'jquery',
	'angular'
], function ($, angular) {

	var profileService = function($rootScope, magezonBuilderService, elementManager) {

		this.getJsonElements = function() {
			return this.processElements(angular.copy($rootScope.profile.elements));
		}

		this.toString = function(incluedKey) {
			if (incluedKey === undefined) {
				incluedKey = true;
			}
			var profile     = {};
			angular.forEach($rootScope.currentProfile, function(value, key) {
				profile[key] = value;
			});
			var newElements = this.processElements(angular.copy($rootScope.profile.elements));
			profile.elements = newElements;
			var profileKey = $rootScope.builderConfig.profile.key;
			var result     = '';
			if (profileKey && incluedKey) {
				result += '[' + profileKey + ']';
			}
			result += angular.toJson(profile);
			if (profileKey && incluedKey) {
				result += '[/' + profileKey + ']';
			}
			return result;
		}

		this.getTargetContent = function() {
			var content      = $('#' + $rootScope.profile.targetId).val();
			var result       = content;
			var profileClass = $rootScope.builderConfig.profile.class;

			// OLD PAGE BUILDER BASE64
			if (profileClass && content.indexOf(profileClass)!==-1) {
				var profileKey     = $rootScope.builderConfig.profile.key;
				var target;
				var currentProfile = {};
				content.gsub(/\{\{block(.*?)\}\}/i, function (match, index) {
					var attributes = magezonBuilderService.parseAttributesString(match[1]);
					if (attributes['class'] == profileClass && _.isEmpty(currentProfile)) {
						currentProfile = attributes;
					}
				});
				if (currentProfile) {
					var profile = {};
					profile['elements'] = JSON.parse(magezonBuilderService.atou(currentProfile.elements));
					result = '[' + profileKey + ']' + angular.toJson(profile) + '[/' + profileKey + ']';
				}
			}
			if (result) result = result.trim();
            return result;
		}

		this.setTargetContent = function(content) {
			var profileKey    = $rootScope.builderConfig.profile.key;
			var targetContent = this.getTargetContent();
			if (profileKey && targetContent) {
				var startKey = '[' + profileKey + ']';
				var endKey   = '[/' + profileKey + ']';
				var start    = targetContent.indexOf(startKey);
				var end      = targetContent.indexOf(endKey, start + endKey.length);
				var code     = targetContent.substring(start, end + endKey.length);
				if (end > start) {
					content = targetContent.replace(code, content);
				}
			}
			$('#' + $rootScope.profile.targetId).val(content);
            $('#' + $rootScope.profile.targetId).trigger('change');	
		}

		this.updateTargetContent = function() {
			var string = this.toString();
			this.setTargetContent(string);
		}

		this.processElements = function(elements) {
			var self = this;
			var newElements = [];
			angular.forEach(elements, function(element) {
				delete element.component;
				for (var k in element) {
					if (typeof(element[k]) === 'string') {
						element[k] = magezonBuilderService.decodeDirectives(element[k]);
					}
				}
				var builderElement = elementManager.elements[element.type];
				if (builderElement) {
					if (element.elements) {
						element.elements = self.processElements(element.elements);
					}
					newElements.push(element);
				}
			});
			return newElements;
		}

		this.getElementShortCode = function(element) {
			var newElement = angular.copy(element);
			delete newElement['component'];
			newElement['element'] = profileManager.processElements(newElement.elements);
			return newElement;
		}
	}

	return profileService;
});