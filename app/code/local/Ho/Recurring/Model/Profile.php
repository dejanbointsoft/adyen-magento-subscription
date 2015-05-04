<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

/**
 * Class Ho_Recurring_Model_Profile
 *
 * @method string getStatus()
 * @method $this setStatus(string $value)
 * @method int getCustomerId()
 * @method $this setCustomerId(int $value)
 * @method string getCustomerName()
 * @method $this setCustomerName(string $value)
 * @method int getOrderId()
 * @method $this setOrderId(int $value)
 * @method int getBillingAgreementId()
 * @method $this setBillingAgreementId(int $value)
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method datetime getCreatedAt()
 * @method $this setCreatedAt(datetime $value)
 * @method datetime getEndsAt()
 * @method $this setEndsAt(datetime $value)
 * @method string getTerm()
 * @method $this setTerm(string $value)
 * @method datetime getNextOrderAt()
 * @method $this setNextOrderAt(datetime $value)
 * @method string getPaymentMethod()
 * @method $this setPaymentMethod(string $value)
 * @method string getShippingMethod()
 * @method $this setShippingMethod(string $value)
 */
class Ho_Recurring_Model_Profile extends Mage_Core_Model_Abstract
{
    const STATUS_INACTIVE           = 'inactive';
    const STATUS_ACTIVE             = 'active';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_EXPIRED            = 'expired';
    const STATUS_AWAITING_PAYMENT   = 'awaiting_payment';
    const STATUS_AGREEMENT_EXPIRED  = 'agreement_expired';

    const TERM_3_MONTHS             = '3_month';
    const TERM_6_MONTHS             = '6_month';

    protected function _construct ()
    {
        $this->_init('ho_recurring/profile');
    }

    public function getUpcomingShippingDate()
    {

    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function createQuote()
    {
        $quote = Mage::getModel('ho_recurring/service_profile')->createQuote($this);

        $this->saveQuoteAtProfile($quote);

        return $quote;
    }

    /**
     * @param Mage_Sales_Model_Quote|null $quote
     * @return Mage_Sales_Model_Order
     */
    public function createOrder(Mage_Sales_Model_Quote $quote = null)
    {
        if (!$quote) {
            $quote = $this->createQuote();
        }
        else {
            $this->saveQuoteAtProfile($quote);

            $quote->collectTotals();
            $quote->save();
        }

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();

        Mage::getModel('ho_recurring/profile_order')
            ->setProfileId($this->getId())
            ->setOrderId($order->getId())
            ->save();

        return $order;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @throws Exception
     */
    public function saveQuoteAtProfile(Mage_Sales_Model_Quote $quote)
    {
        $profileQuote = Mage::getModel('ho_recurring/profile_quote')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId())
            ->addFieldToFilter('quote_id', $quote->getId())
            ->getFirstItem();

        if (!$profileQuote->getId()) {
            Mage::getModel('ho_recurring/profile_quote')
                ->setProfileId($this->getId())
                ->setQuoteId($quote->getId())
                ->save();
        }
    }

    /**
     * @param bool $active
     * @return Ho_Recurring_Model_Resource_Profile_Item_Collection
     */
    public function getItems($active = true)
    {
        $items = Mage::getModel('ho_recurring/profile_item')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId());

        if ($active) {
            $items->addFieldToFilter('status', Ho_Recurring_Model_Profile_Item::STATUS_ACTIVE);
        }

        return $items;
    }

    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getOrders()
    {
        return Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $this->getOrderIds()));
    }

    /**
     * @return array
     */
    public function getOrderIds()
    {
        $profileOrders = Mage::getModel('ho_recurring/profile_order')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId());

        $orderIds = array();
        foreach ($profileOrders as $profileOrder) {
            $orderIds[] = $profileOrder->getOrderId();
        }

        return $orderIds;
    }

    /**
     * @param bool $withoutOrder When true, only return quote IDs that are not (yet) an order
     * @return array
     */
    public function getQuoteIds($withoutOrder = true)
    {
        $profileQuotes = Mage::getModel('ho_recurring/profile_quote')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId());

        $quoteIds = array();
        foreach ($profileQuotes as $profileQuote) {
            $quoteIds[] = $profileQuote->getQuoteId();
        }

        if ($withoutOrder) {
            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('quote_id', array('in' => $quoteIds));
            foreach ($orders as $order) {
                $quoteIds = array_diff($quoteIds, array($order->getQuoteId()));
            }
        }

        return $quoteIds;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOriginalOrder()
    {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        $helper = Mage::helper('ho_recurring');

        return array(
            self::STATUS_INACTIVE           => $helper->__('Inactive'),
            self::STATUS_ACTIVE             => $helper->__('Active'),
            self::STATUS_CANCELED           => $helper->__('Canceled'),
            self::STATUS_EXPIRED            => $helper->__('Expired'),
            self::STATUS_AWAITING_PAYMENT   => $helper->__('Awaiting Payment'),
            self::STATUS_AGREEMENT_EXPIRED  => $helper->__('Agreement Expired'),
        );
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->getStatuses()[$this->getStatus()];
    }

    /**
     * @return array
     */
    public function getTerms()
    {
        $helper = Mage::helper('ho_recurring');

        return array(
            self::TERM_3_MONTHS     => $helper->__('3 months'),
            self::TERM_6_MONTHS     => $helper->__('6 months'),
        );
    }

    /**
     * @return string
     */
    public function getTermLabel()
    {
        return $this->getTerms()[$this->getTerm()];
    }
}
