define([
    'Magento_Weee/js/view/checkout/summary/item/price/weee',
    'underscore'
], function (weee, _) {
    'use strict';

    var quoteItemData = window.checkoutConfig.quoteItemData;

    return weee.extend({
        defaults: {
            template: 'RltSquare_DiscountShower/checkout/summary/item/price/row_excl_tax'
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        quoteItemData: quoteItemData,

        getDiscountPrice: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            return item.row_total_with_discount;
        },
        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        },
        getRowDisplayPriceExclTax: function (item) {
            var rowTotalExclTax = parseFloat(item['row_total']);
            if (window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalExclTax += this.getRowWeeeTaxExclTax(item);
            }

            return rowTotalExclTax;
        },
        hasPositiveDiscount: function (item) {
            var discountAmount = parseFloat(item['discount_amount']);
            return discountAmount > 0;
        },


        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowWeeeTaxExclTax: function (item) {
            var totalWeeeTaxExclTaxApplied = 0,
                weeeTaxAppliedAmounts;

            if (item['weee_tax_applied']) {
                weeeTaxAppliedAmounts = JSON.parse(item['weee_tax_applied']);
                weeeTaxAppliedAmounts.forEach(function (weeeTaxAppliedAmount) {
                    totalWeeeTaxExclTaxApplied += parseFloat(Math.max(weeeTaxAppliedAmount['row_amount'], 0));
                });
            }

            return totalWeeeTaxExclTaxApplied;
        }
    });
});
