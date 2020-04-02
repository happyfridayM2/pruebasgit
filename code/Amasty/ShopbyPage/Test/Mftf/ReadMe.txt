Current version of tests is for Magento CE 2.3.4 (with Default Magento Sample Data) with Amasty Improved Layered Navigation 2.13.7 only.
Prior to tests running it is required to launch PreConfigureAttributesTest.xml. This test works around Magento bug with Attribute Options duplication.
In order to receive correct run of image checkings it is necessary to store a couple of images (all required images are stored with ReadMe.txt file in the same folder of ShopbyBase module) in magento_root/dev/tests/acceptance/tests/_data folder.
In order to avoid timeout error while tests are running we highly recommend to increase "pageload_timeout:" in magento_root/dev/tests/acceptance/tests/functional.suite.yml
