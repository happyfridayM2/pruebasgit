define([
    'uiComponent',
    'jquery',
    'mage/translate'
], function(Component, $) {

    return Component.extend({
        defaults: {
            mobile: [],
            desktop: [],
        },

        audits: {
            'render-blocking-resources': {
                amastyDescription: $.mage.__("Please recheck if JS or CSS causes the problem. If the report shows there's a problem with JS, you can fix it using our extension. Please enable the following features: Stores > Configuration > Amasty Extensions > Google Page Speed Optimizer > Settings > JavaScript > Amasty JS Optimization = Enabled Stores > Configuration > Amasty Extensions > Google Page Speed Optimizer > Settings > JavaScript > Move JavaScript To Page Bottom = Yes In case the problem is about the CSS files, there's no one-size-fits-all solution. Each case needs a tailored approach. So to solve such issues, you need to hire a developer who will analyze the code and possibly adapt the styles especially for your website.")
            },
            'uses-webp-images': {
                amastyDescription: $.mage.__("Our extension solves this problem. You can easily convert PNG and JPEG images in WebP, a new format developed by Google. Please follow the instructions below to convert your file(s). Go to Content > Google Page Speed Optimizer > Image Folder Optimization Settings. Then add new pattern for the problematic image folder. Enable Create WebP Copy\' setting, click on \'Save and Optimize\'\'. That\'s it!")
            },
            'uses-responsive-images': {
                amastyDescription: $.mage.__("Our extension solves this problem. You can create the copies of these files in the sizes that fit tablets and other mobile devices. To do so, please follow the instruction below: First go to Content > Google Page Speed Optimizer > Image Folder Optimization Settings. Now you need to create Image Settings for the problematic image folder and activate the following settings: Create Image for Mobile - set to Yes Create Image for Tablet - set to Yes. Then to choose the suitable Resize Algorithm. Now the images will be displayed correctly on tablets, smartphones and other mobile devices and weight much less.")
            },
            'redirects': {
                amastyDescription: $.mage.__("This issue may occur due to incorrect server settings or bad code, so you might need extra help to deal with it. To have it fixed, you should address your sys admin or developer. The specialists will look into potential sources of the problem and solve it.")
            },
            'offscreen-images': {
                amastyDescription: $.mage.__("You can solve this issue, enabling LazyLoad setting in our module. To do so, please got to Stores > Configuration > Amasty Extensions > Google Page Speed Optimizer > Image Optimization > Lazy Load. Now set the \' Use Lazy Loading Images\' to Yes. The setting above called \'Lazy Load Settings\' allows you to configure lazy Load for every page type individually.")
            },
            'unused-css-rules': {
                amastyDescription: $.mage.__('There is no one-size-fits-all solution to this cases for all Magento sites. Each case needs a tailored approach. So to solve this issue,  you need to hire a developer who will analyze the code and possibly manage to optimize styles especially for your website.')
            },
            'uses-optimized-images': {
                amastyDescription: $.mage.__("Our extension solves this problem using special tools for image compression. Before enabling it, you need to make sure you have server utilities for image optimization installed. Here they are: JPEG optimization tool / PNG optimization tool / GIF optimization tool / WebP tool. If you haven\'t found any tool installed, you\'ll need to choose the one that suits you best and set it up. Your hosting provider or system administrator can help you with the installation. After you verify that the optimization tools are installed, please go to Content > Google Page Speed Optimizer > Image Folder Optimization Settings. You need to create Image Setting for the probematic image folder and enable Jpeg Tool, Png Tool or Gif Tool settings.")
            },
            'uses-text-compression': {
                amastyDescription: $.mage.__('This feature works as a server tool. Please contact your hosting provider or system administrator and they will activate it for you.')
            },
            'efficient-animated-content': {
                amastyDescription: $.mage.__('There is no one-size-fits-all solution to this cases for all Magento sites. Each case needs a tailored approach. So to solve this issue,  you need to hire a developer who will analyze the code and possibly manage to optimize styles especially for your website. ')
            },
            'uses-rel-preload': {
                amastyDescription: $.mage.__('There is no one-size-fits-all solution to this cases for all Magento sites. Each case needs a tailored approach. So to solve this issue,  you need to hire a developer who will analyze the code and possibly manage to optimize styles especially for your website.')
            },
            'unminified-css': {
                amastyDescription: $.mage.__('To Minify CSS, please  go to Stores > Configuration > Amasty Extensions > Google Page Speed Optimizer > Settings > CSS > Minify CSS Files and move the switch of this setting to Yes.')
            },
            'unminified-javascript': {
                amastyDescription: $.mage.__("You can minify Javascript files in 2 ways: 1.Navigate to Stores > Configuration > Amasty Extensions > Google Page Speed Optimizer > Settings > JavaScript and enable \'Amasty JS Optimization\'. Now JS files will be optimized automatically including minifying and merging into smart bundles. Please make a full backup of your Magento instance just to be safe if anything goes wrong. Then hit the \'Start\' button in the \'Run Optimization\' field. It will launch the JS optimization wizard with full what-and-how list. In case you don\'t want to use this setting, please check the 2nd option. 2. Navigate to Stores > Configuration > Amasty Extensions > Google Page Speed Optimizer > Settings > JavaScript > Minify JavaScript Files = Yes. Now JS files will be minified.")
            },
            'total-byte-weight': {
                amastyDescription: $.mage.__('There is no one-size-fits-all solution to this cases for all Magento sites. Each case needs a tailored approach. So to solve this issue,  you need to hire a developer who will analyze the code and possibly manage to optimize styles especially for your website.')
            },
            'dom-size': {
                amastyDescription: $.mage.__('There is no one-size-fits-all solution to this cases for all Magento sites. Each case needs a tailored approach. So to solve this issue,  you need to hire a developer who will analyze the code and possibly manage to optimize styles especially for your website.')
            },
            'time-to-first-byte': {
                amastyDescription: $.mage.__('This error means that there are some problems with your server operation. To have it solved, please consult your hosting provider or system administrator.')
            },
            'uses-rel-preconnect': {},
            'bootup-time': {},
            'third-party-summary': {},
            'uses-long-cache-ttl': {}
        },

        linkText: 'Content > Google Page Speed Optimizer > Image Folder Optimization Settings',

        element: {
            pageUrl: '[data-amoptimizer-js="url"]',
            circle: '[data-amoptimizer-js="circle"]',
            chartContainer: '[data-amoptimizer-js="chart-container"]',
            totalValuation: '[data-amoptimizer-js="valuation"]',
            tab: '[data-amoptimizer-js="tab"]',
            main: '[data-amoptimizer-js="main"]',
            diagnostic: '[data-amoptimizer-js="diagnostic"]'
        },

        css: {
            small: '-small',
            red: '-red',
            orange: '-orange',
            green: '-green'
        },

        score: {
            mobile: null,
            desktop: null
        },

        initialize: function () {
            this._super();

            this.addEvents();
        },

        initObservable: function () {
            this._super().observe(['mobile', 'desktop']);

            return this;
        },

        addEvents: function () {
            $(this.element.diagnostic).on('click', this.startDiagnostic.bind(this));
            $(this.element.tab).on('click', this.changeTotalScore.bind(this));
        },

        changeTotalScore: function (e) {
            var $elem = $(e.currentTarget);

            this.getTotalScore(this.score[$elem.data('amoptimizer-tab')]);
        },

        startDiagnostic: function () {
            this.ajaxCall('desktop');
            this.ajaxCall('mobile');
        },

        /**
         * @param {string} totalValuation
         */
        getTotalScore: function (totalValuation) {
            var percentage = totalValuation * 100;

            const circleLength = 314,
                  low = 50,
                  medium = 90;

            $(this.element.totalValuation).text(Math.ceil(percentage));
            $(this.element.circle).css('stroke-dashoffset', circleLength - circleLength * totalValuation);

            if (percentage < low) {
                if (percentage < 10) $(this.element.chartContainer).addClass(this.css.small);

                $(this.element.chartContainer)
                    .addClass(this.css.red)
                    .removeClass(this.css.orange)
                    .removeClass(this.css.green);
            } else if (percentage < medium) {
                $(this.element.chartContainer)
                    .addClass(this.css.orange)
                    .removeClass(this.css.red)
                    .removeClass(this.css.small)
                    .removeClass(this.css.green);
            } else {
                $(this.element.chartContainer)
                    .removeClass(this.css.orange)
                    .removeClass(this.css.red)
                    .removeClass(this.css.small)
                    .addClass(this.css.green);
            }
        },

        /**
         * @param {string} text
         * @returns {[]|{description: *}}
         */
        parseDescription: function (text) {
            var descriptionArr = [],
                splittedText,
                matches;

            matches = text.match(/\[[-\W\w\s]+\]\(.*?\)/gm);
            if (matches === null) {
                return { description: text }
            }

            splittedText = text.split(/(\[([-\W\w\s]+?)\]\((.*?)\))/gm);

            for (var i = 0; i < Math.floor(splittedText.length / 4); i++) {
                descriptionArr.push(
                    {
                        prevText: splittedText[i * 4],
                        link: splittedText[i * 4 + 3],
                        linkTitle: splittedText[i * 4 + 2]
                    }
                );
            }

            if (splittedText.length % 4 === 1) {
                descriptionArr.push(
                    {
                        prevText: splittedText[splittedText.length - 1],
                        link: ''
                    }
                );
            }

            descriptionArr.hasLink = true;

            return descriptionArr;
        },

        ajaxCall: function (version) {
            $.ajax({
                url: 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
                type: 'GET',
                showLoader: true,
                data: {url: this.baseUrl, strategy: version, locale: this.locale},
                success: function (response) {
                    this.initInfoblock(response, version);
                }.bind(this)
            });
        },

        initInfoblock: function (response, version) {
            var data,
                totalValuation;

            data = response.lighthouseResult.audits;
            totalValuation = response.lighthouseResult.categories.performance.score;
            this.score[version] = totalValuation;

            $(this.element.diagnostic).hide();
            $(this.element.main).show();
            $(this.element.pageUrl).text(response.id);

            if (version === 'mobile') {
                this.getTotalScore(totalValuation);
            }

            this.prepareData(version, data);
        },

        checkAmastyLink: function (text) {
            if (text.indexOf(this.linkText) === -1) return '';

            return {
              url: this.imageFolderOptimization,
              prevText: text.split(this.linkText)[0],
              linkText: this.linkText,
              afterText: text.split(this.linkText)[1]
            };
        },

        /**
         * @param {string} type
         * @param {object} data
         */
        prepareData: function (type, data) {
            var warningArray = [],
                key;

            const hightValue = 0.89;

            for (key in data) {
                if (data[key].score === null || data[key].score > hightValue || !this.audits[key]) continue;

                this.audits[key].title = data[key].title;
                this.audits[key].score = data[key].score;
                this.audits[key].id = data[key].id;
                this.audits[key].description = this.parseDescription(data[key].description);
                this.audits[key].displayValue = data[key].displayValue ? data[key].displayValue : '';
                this.audits[key].amastyDescriptionLink = this.audits[key].amastyDescription ?
                    this.checkAmastyLink(this.audits[key].amastyDescription) : '';

                warningArray.push(this.audits[key]);
            }

            if (type === 'mobile') {
                this.mobile(warningArray);
            } else {
                this.desktop(warningArray);
            }
        }
    });
});
