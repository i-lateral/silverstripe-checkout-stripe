# Silverstripe Checkout Stripe

Adds Stripe payment option to the checkout module.

## Dependancies

* [SilverStripe Framework 3.1.x](https://github.com/silverstripe/silverstripe-framework)
* [SilverStripe Checkout](https://github.com/i-lateral/silverstripe-checkout-sofort)

## Installation

Either install via composer:

    i-lateral/silverstripe-checkout-stripe: *
    
Or download and install to the "checkout-stripe" directory in your
project root.

## Setup

Once installed you will need to add your API Keys (found in Stripe under
"Account Settings").

Add all four these via Silverstripe config:

### YML

    StripPayment:
      test_secret_key: test_xyz
      test_publish_key: test_xyz
      live_secret_key: live_xyz
      live_publish_key: live_xyz
      
### PHP

    StripPayment::config()->test_secret_key = "test_xyz";
    StripPayment::config()->test_publish_key = "test_xyz";
    StripPayment::config()->live_secret_key = "live_xyz";
    StripPayment::config()->live_publish_key = "live_xyz";

Now enable the stripe payment by logging into the CMS then visiting
global site settings.

Now click the "Checkout" tab and under payment methods click "Add".

Now then select "Stripe" from the dropdown.
