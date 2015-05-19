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

class Ho_Recurring_Block_Adminhtml_Profile_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ho_recurring';
        $this->_controller = 'adminhtml_profile';
        $this->_mode = 'view';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');

        if ($this->getProfile()->canCancel()) {
            $url = $this->getUrl('*/*/cancelProfile', ['id' => $this->getProfile()->getIncrementId(), 'reason' => '%s']);
            $js = <<<JS
                var reason = prompt('{$this->helper('ho_recurring')->__("Please provide a reason why the profile is stopped")}');
                if (reason) {
                    var url = '{$url}';
                    url = url.replace('%s', encodeURIComponent(reason));
                    setLocation(url);
                }
JS;

            $this->_addButton('stop_profile', [
                'class'     => 'delete',
                'label' => Mage::helper('ho_recurring')->__('Stop Profile'),
                'onclick' => $js,
            ], 10);
        }


        if ($this->getProfile()->canCreateQuote()) {
            $this->_addButton('create_quote', [
                'label' => Mage::helper('ho_recurring')->__('Create Quote'),
                'onclick' => "setLocation('{$this->getUrl('*/*/createQuote',
                    ['id' => $this->getProfile()->getId()])}')",
            ], 20);
        }
    }

    public function getHeaderText()
    {
        $profile = $this->getProfile();

        if ($profile->getId()) {
            return Mage::helper('ho_recurring')->__('Recurring Profile %s for %s',
                sprintf('<i>#%s</i>', $profile->getIncrementId()),
                sprintf('<i>%s</i>', $profile->getCustomerName())
            );
        }
        else {
            return Mage::helper('ho_recurring')->__('New Profile');
        }
    }

    /**
     * @return Ho_Recurring_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('ho_recurring');
    }
}