Current version of tests is for Magento CE 2.3.4 (with Default Magento Sample Data) with Amasty Improved Layered Navigation 2.13.7 only.
Prior to tests running it is required to launch PreConfigureAttributesTest.xml. This test works around Magento bug with Attribute Options duplication.
In order to receive correct run of image checkings it is necessary to store a couple of images (all required images are stored with ReadMe.txt file in the same folder of ShopbyBase module) in magento_root/dev/tests/acceptance/tests/_data folder.
In order to avoid timeout error while tests are running we highly recommend to increase "pageload_timeout:" in magento_root/dev/tests/acceptance/tests/functional.suite.yml
The tests are divided into following groups:
- ILN (is used for running of all tests. E.g. vendor/bin/mftf run:group ILN -r)
- ILNsmoke (is used for running of all smoke tests)
- ILNregression (is used for running of all regression tests)
- catfilter (is used for running of all tests which check Category Filter functionality)
- childcatblock (is used for running of all tests which check Children Categories Block functionality)
- catmetatags (is used for running of all tests which check Category Meta Tags functionality)
- cattitledesc (is used for running of all tests which check Category Title and Description functionality)
- newfilter (is used for running of all tests which check New Filter functionality)
- onsalefilter (is used for running of all tests which check On Sale Filter functionality)
- ratingfilter (is used for running of all tests which check Rating Filter functionality)
- stockfilter (is used for running of all tests which check Stock Filter functionality)
