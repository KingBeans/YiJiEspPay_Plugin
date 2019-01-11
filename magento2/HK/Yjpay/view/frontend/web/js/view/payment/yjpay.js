/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'yjpay',
                component: 'Magento_Yjpay/js/view/payment/method-renderer/yjpay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);