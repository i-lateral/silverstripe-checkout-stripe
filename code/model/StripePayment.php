<?php

class StripePayment extends PaymentMethod
{
    
    /**
     * The test secret Stripe API key
     * 
     * @config
     */
    private static $test_secret_key;
    
    /**
     * The test publish API key
     * 
     * @config
     */
    private static $test_publish_key;
    
    /**
     * The live secret Stripe API key
     * 
     * @config
     */
    private static $live_secret_key;
    
    /**
     * The live publish API key
     * 
     * @config
     */
    private static $live_publish_key;
    
    /**
     * Allow "remember me" functionality (that will save user
     * payment details)
     * 
     * @config
     */
    private static $remember_me = true;
    
    public static $handler = "StripePaymentHandler";

    public $Title = 'Stripe';
    
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        $fields->removeByName("PaymentInfo");
        $fields->removeByName("PaymentURLS");
        
        return $fields;
    } 

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->CallBackSlug = (!$this->CallBackSlug) ? 'Stripe' : $this->CallBackSlug;

        $this->Summary = (!$this->Summary) ? _t("CheckoutStripe.PayWithCard","Pay with credit/debit card via Stripe") : $this->Summary;
    }
}
