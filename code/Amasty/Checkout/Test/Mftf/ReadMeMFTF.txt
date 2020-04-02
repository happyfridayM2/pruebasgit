ReadeMeMFTF (recommendations for running tests related to One Step Checkout extension).

     42 One Step Checkout specific tests, grouped by purpose, for greater convenience.

            Tests group: OSC
            Runs all tests except OSCCheckExternalPayments (online payment methods) group.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSC -r

            Tests group: OSCConfiguration
            Runs tests related to extension configuration.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCConfiguration -r

            Tests group: OSCFunctional
            Runs tests related to extension's core functions.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCFunctional -r

            Tests group: OSCPaymentMethods
            Runs tests related to offline payment methods' work with One Step Checkout.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCPaymentMethods -r
            Included payment method tests:
            Bank Transfer, Cash On Delivery, Purchase Order
                SSH command to run tests for particular payment method:
                vendor/bin/mftf run:group OSCPaymentPurchaseOrder -r
                vendor/bin/mftf run:group OSCPaymentCashOnDelivery -r
                vendor/bin/mftf run:group OSCPaymentBankTransfer -r

            ---

    (!) Please note that Sandbox mode (and/or Test mode, as in most Stripe implementations) should be enabled and configured instead of Live mode for online payment methods to be tested.

            Here and below:
            to run groups of tests related to online payment methods, it is necessary to add PayPal Sandbox customer account details at (for Composer based installs)
            vendor/amasty/module-common-tests/Test/Mftf/Data/CreditCardsData
            or (for install-by-upload)
            app/code/Amasty/CommonTests/Test/Mftf/Data/CreditCardsData

            Tests group: OSCCheckExternalPayments
            Runs tests related to external payment methods' work with One Step Checkout.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group OSCCheckExternalPayments -r
            Included payment method tests:
            Amazon, Authorise, Braintree, EWay, Klarna, PayPal, PayflowPro, Stripe
                SSH command to run tests for particular payment method:
                vendor/bin/mftf run:group OSCCheckExternalPaymentsAmazon -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsAuthorise -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsBraintree -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsEWay -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsKlarna -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsPayPal -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsPayflowPro -r
                vendor/bin/mftf run:group OSCCheckExternalPaymentsStripe -r