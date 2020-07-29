define(
    [
        'uiComponent',
    ],
    function (
        Component,
    ) {
        'use strict';

        return Component.extend({
            /**
             * Button Config
             */
            config: {
                containerId: null,
                checkoutPagePath: null,
                grandTotalAmount: 0,
                btnShape: 'rect',
                btnColor: 'gold'
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                let self = this;

                self._super();

                if (window.paypal) {
                    let paypal = window.paypal;

                    paypal.Buttons({
                        style: {
                            layout: 'horizontal',
                            size: 'responsive',
                            shape: self.btnShape,
                            color: self.btnColor,
                            height: 43,
                            fundingicons: false,
                            tagline: false,
                        },

                        createOrder: function(data, actions) {
                            return actions.order.create({
                                purchase_units: [{
                                    amount: {
                                        value: self.grandTotalAmount
                                    }
                                }]
                            });
                        },

                        onApprove: function(data) {
                            window.location.href = '/' + self.checkoutPagePath + '?paypal_order_id=' + data.orderID;
                        }
                    }).render('#' + self.containerId);
                }

                return self;
            },
        });
    }
);