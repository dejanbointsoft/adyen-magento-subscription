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

class Ho_Recurring_Helper_Quote extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve product profile, if product is recurring, else false
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Ho_Recurring_Model_Product_Profile|false
     */
    public function getProductProfile($product)
    {
        if (isset($product->getAttributes()['ho_recurring_type'])) {
            if ($product->getData('ho_recurring_type') != Ho_Recurring_Model_Product_Profile::TYPE_DISABLED) {
                $option = $product->getCustomOption('additional_options');

                if ($option) {
                    $additionalOptions = unserialize($option->getValue());
                    foreach ($additionalOptions as $additional) {
                        if ($additional['code'] == 'ho_recurring_profile') {
                            if ($additional['option_value'] != 'none') {
                                $profile = Mage::getModel('ho_recurring/product_profile')->load($additional['option_value']);
                                if (! $profile->getId()) {
                                    return false;
                                }

                                return $profile;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Ho_Recurring_Model_Profile_Item|false
     */
    public function getProfileItem($product)
    {
        if ($profileItemId = $product->getData('is_created_from_profile_item')) {
            $profileItem = Mage::getModel('ho_recurring/profile_item')->load($profileItemId);

            if ($profileItem->getId()) {
                return $profileItem;
            }
        }

        return false;
    }
}