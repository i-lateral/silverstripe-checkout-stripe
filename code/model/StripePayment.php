<?php

class StripePayment extends PaymentMethod
{
    
    public static $handler = "StripePaymentHandler";

    public $Title = 'Stripe';

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->CallBackSlug = (!$this->CallBackSlug) ? 'Stripe' : $this->CallBackSlug;

        $this->Summary = (!$this->Summary) ? "Pay with Stripe" : $this->Summary;
    }
}
