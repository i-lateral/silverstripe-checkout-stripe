<?php

use \Stripe\Stripe as Stripe;
use \Stripe\Charge as StripeCharge;

class StripePaymentHandler extends PaymentHandler
{

    public function index($request)
    {
        $site = SiteConfig::current_site_config();
        $order = $this->getOrderData();
        $cart = ShoppingCart::get();
        
        // Setup stripe keys
        if(Director::isDev()) {
            $secret_key = StripePayment::config()->test_secret_key;
            $publish_key = StripePayment::config()->test_publish_key;
        } else {
            $secret_key = StripePayment::config()->live_secret_key;
            $publish_key = StripePayment::config()->live_publish_key;
        }
        
        $callback_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            "callback",
            $this->payment_gateway->ID
        );
        
        Stripe::setApiKey($secret_key);
        
        $this->extend('onBeforeIndex');

        // Setup stripe JS call for checkout
        $html = '<script ';
        $html .= 'src="https://checkout.stripe.com/checkout.js" ';
        $html .= 'class="stripe-button" ';
        $html .= 'data-key="' . $publish_key . '" ';
        $html .= 'data-name="' . $site->Title . '" ';
        $html .= 'data-email="' . $order->Email . '" ';
        
        if(StripePayment::config()->remember_me) {
            $html .= 'data-allow-remember-me="true" ';
        } else {
            $html .= 'data-allow-remember-me="false" ';
        }
        
        $html .= 'data-amount="' . (string)(round($cart->TotalCost * 100)) . '" ';
        $html .= 'data-currency="' . strtolower(Checkout::config()->currency_code) . '" ';
        $html .= 'data-locale="auto" ';
        $html .= '></script>';
        
        $form = Form::create(
            $this,
            "Form",
            FieldList::create(
                LiteralField::create("StripeJS", $html)
            ),
            FieldList::create()
        );
        
        $form
            ->addExtraClass('forms')
            ->setFormMethod('POST')
            ->setFormAction($callback_url)
            ->disableSecurityToken();
            
        Session::set("StripePayment.OrderNumber", $order->OrderNumber);
        
        $this->customise(array(
            "Title"     => _t('Checkout.Summary', "Summary"),
            "MetaTitle" => _t('Checkout.Summary', "Summary"),
            "Form"      => $form,
            "Order"     => $order
        ));
        
        $this->extend("onAfterIndex");
        
        return $this->renderWith(array(
            "StripePayment",
            "Payment",
            "Checkout",
            "Page"
        ));
    }

    /**
     * Process the callback data from the payment provider
     */
    public function callback($request)
    {
        
        if(Director::isDev()) {
            $secret_key = StripePayment::config()->test_secret_key;
            $publish_key = StripePayment::config()->test_publish_key;
        } else {
            $secret_key = StripePayment::config()->live_secret_key;
            $publish_key = StripePayment::config()->live_publish_key;
        }
        
        Stripe::setApiKey($secret_key);
        
        $site = SiteConfig::current_site_config();
        $order = $this->getOrderData();
        $cart = ShoppingCart::get();
        
        $this->extend('onBeforeCallback');
        
        $error_url = Controller::join_links(
            Director::absoluteBaseURL(),
            Payment_Controller::config()->url_segment,
            'complete',
            'error'
        );
        
        $data = $this->request->postVars();
        $status = "error";

        // Get the credit card details submitted by the form
        $token = $data['stripeToken'];
        
        $order_no = Session::get("StripePayment.OrderNumber");
        
        $order = Order::get()
            ->filter("OrderNumber", $order_no)
            ->first();
        
        if($order_no && $order) {
            // Create the charge on Stripe's servers - this will charge
            // the user's card
            try {
                $success_url = Controller::join_links(
                    Director::absoluteBaseURL(),
                    Payment_Controller::config()->url_segment,
                    'complete',
                    $order_no
                );
                
                $charge = StripeCharge::create(array(
                    "amount" => round($cart->TotalCost * 100),
                    "currency" => strtolower(Checkout::config()->currency_code),
                    "source" => $token,
                    "metadata" => array("Order" => $order_no)
                ));
                
                $order->Status = "paid";
                $order->PaymentProvider = "Stripe";
                $order->PaymentNo = $charge->id;
                
                $order->write();
                
                return $this->redirect($success_url);
                
            } catch(\Stripe\Error\Card $e) {
                $order->Status = "failed";
                $order->write();
                
                return $this->redirect($error_url);
            } finally {
                return $this->redirect($error_url);
            }
        } else {
            return $this->redirect($error_url);
        }
    }
}
