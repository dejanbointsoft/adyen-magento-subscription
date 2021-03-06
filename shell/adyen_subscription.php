<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

require_once 'abstract.php';

class Adyen_Subscription_Shell extends Mage_Shell_Abstract
{

    /**
   	 * Run script
   	 *
   	 * @return void
   	 */
   	public function run() {
   		$action = $this->getArg('action');
   		if (empty($action)) {
   			echo $this->usageHelp();
   		} else {
   			$actionMethodName = $action.'Action';
   			if (method_exists($this, $actionMethodName)) {
   				$this->$actionMethodName();
   			} else {
   				echo "Action $action not found!\n";
   				echo $this->usageHelp();
   				exit(1);
   			}
   		}
   	}

    /**
   	 * Retrieve Usage Help Message
   	 *
   	 * @return string
   	 */
   	public function usageHelp() {
   		$help = 'Available actions: ' . "\n";
   		$methods = get_class_methods($this);
   		foreach ($methods as $method) {
   			if (substr($method, -6) == 'Action') {
   				$help .= '    -action ' . substr($method, 0, -6);
   				$helpMethod = $method.'Help';
   				if (method_exists($this, $helpMethod)) {
   					$help .= $this->$helpMethod();
   				}
   				$help .= "\n";
   			}
   		}
   		return $help;
   	}


    /**
   	 * @return void
   	 */
   	public function convertOrderToSubscriptionAction() {
		if (! $order = $this->getArg('order')) {
			exit("Please specifity an --order 12345\n");
		}

		$order = Mage::getModel('sales/order')->loadByIncrementId($order);
		if (! $order->getId()) {
			exit("Could not load order\n");
		}


		Mage::getSingleton('adyen_subscription/service_order')->createSubscription($order);
   	}


	public function createQuotesAction()
	{
		$message = Mage::getSingleton('adyen_subscription/cron')->createQuotes();
		echo $message."\n";
	}

	public function createOrdersAction()
	{
		$message = Mage::getSingleton('adyen_subscription/cron')->createOrders();
		echo $message."\n";
	}
}


$shell = new Adyen_Subscription_Shell();
$shell->run();