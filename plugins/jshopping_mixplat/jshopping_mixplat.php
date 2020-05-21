<?php

defined('_JEXEC') or die;

class plgJshoppingorderjshopping_mixplat extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onAfterChangeOrderStatus($order_id, $status, $message)
	{
		$order = JTable::getInstance('order', 'jshop');
		$order->load($order_id);
		$this->onUpdateStatus($order);
		return true;
	}
	public function onAfterChangeOrderStatusAdmin($order_id, $order_status, $status_id, $notify, $comments, $include, $view_order)
	{
		$order = JTable::getInstance('order', 'jshop');
		$order->load($order_id);
		$this->onUpdateStatus($order);
		return true;
	}


	private function onUpdateStatus($order)
	{
		$pm_method = $order->getPayment();
		$paymentsysdata = $pm_method->getPaymentSystemData();
		$payment_system = $paymentsysdata->paymentSystem;
		if ($payment_system && get_class($payment_system) == 'pm_mixplat'){
			$pmconfigs = $pm_method->getConfigs();
			try {
				if ($pmconfigs['transaction_refund_status'] == $order->order_status) {
					$payment_system->refund($pmconfigs, $order->order_id);
				}
				if ($pmconfigs['payment_scheme'] === 'dms' && $pmconfigs['transaction_capture_status'] == $order->order_status) {
					$payment_system->capture($pmconfigs, $order->order_id);
				}
			} catch (Exception $e) {
				$msg = $e->getMessage();
				$app = JFactory::getApplication();
				$app->enqueueMessage($msg, 'error');
			}
		}
		return true;

	}

}
