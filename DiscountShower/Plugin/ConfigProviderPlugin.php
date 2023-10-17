<?php
declare(strict_types=1);

namespace RltSquare\DiscountShower\Plugin;

use Exception;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use RltSquare\DiscountShower\Logger\Logger;

/**
 * @class ConfigProviderPluginConfigProviderPlugin
 */
class ConfigProviderPlugin
{

    protected checkoutSession $checkoutSession;
    private Logger $logger;

    /**
     *Constructor
     * @param CheckoutSession $checkoutSession
     * @param Logger $logger
     */
    public function __construct(CheckoutSession $checkoutSession, Logger $logger)
    {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result): array
    {
        try {
            $items = $result['totalsData']['items'];

            foreach ($items as $index => $item) {
                $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);

                if ($quoteItem->getBaseDiscountAmount() > 0) {
                    if ($quoteItem->getDiscountPercent() > 0) {
                        // Percentage discount
                        $originalPrice = $quoteItem->getRowTotal();
                        $discountAmount = $quoteItem->getDiscountPercent() / 100 * $originalPrice;
                        $discountedPrice = $originalPrice - $discountAmount;
                        $this->logger->info('Percent Cart Discount Price is' . $discountedPrice);
                        $result['quoteItemData'][$index]['row_total_with_discount'] = $discountedPrice;
                    } elseif ($quoteItem->getDiscountAmount() > 0) {
                        // Fixed amount discount
                        $discountAmount = $quoteItem->getDiscountAmount();
                        $discountedPrice = $quoteItem->getRowTotal() - $discountAmount;
                        $this->logger->info('Fix Cart Discount Price is' . $discountedPrice);
                        $result['quoteItemData'][$index]['row_total_with_discount'] = $discountedPrice;
                    } else {

                        $this->logger->notice('Not have any Discount');
                        continue;
                    }
                    $this->logger->info('Cart Discount Price is greater then 0.');
                } else {
                    $this->logger->notice('Cart Discount Price Apply Successfully.');
                }
            }

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }


        return $result;
    }
}
