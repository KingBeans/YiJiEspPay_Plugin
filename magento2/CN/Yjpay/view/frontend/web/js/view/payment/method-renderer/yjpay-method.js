/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/view/payment/default',
        'jquery'
    ],
    function (storage,urlBuilder,Component, $) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Yjpay/payment/yjpay'
            },
            getCode: function () {
                return 'yjpay';
            },
            isActive: function () {
                return true;
            },
            validate: function() {
                return true;
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                window.checkoutConfig;//console.log()
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            // placeOrder:function(){
            //     location.href = urlBuilder.build("yjpay/payment/checkout");
            // },
            afterPlaceOrder: function () {
                //location.href = urlBuilder.build("yjpay/payment/checkout");
                // var self = this;
                 $.post(urlBuilder.build("yjpay/payment/checkout"),{},function(data){
                     //console.log(data);
                     window.location.replace(data.url);
                 },'json');
            }
        });
    }
);
