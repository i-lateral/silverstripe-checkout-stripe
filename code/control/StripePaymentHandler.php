<?php

class StripePaymentHandler extends PaymentHandler
{

    public function index($request)
    {
        $this->extend('onBeforeIndex');
        
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
        $this->extend('onBeforeCallback');
        
        $data = $this->request->postVars();
        $status = "error";
        $content = file_get_contents('php://input');
        
        return $this->httpError(500);
    }
}
