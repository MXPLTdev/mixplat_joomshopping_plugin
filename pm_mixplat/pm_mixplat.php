<?php
defined('_JEXEC') or die('Restricted access');
if (!class_exists("MixplatLib")) {
	include_once __DIR__ . '/mixplat/lib.php';
}

class pm_mixplat extends PaymentRoot
{

	public function __construct()
	{
		JLog::addLogger(
			array(
				'text_file' => 'mixplat.log.php',
			),
			JLog::ALL,
			array('pm_mixplat')
		);

	}

	public function showPaymentForm($params, $pmconfigs)
	{
		include dirname(__FILE__) . "/paymentform.php";
	}

	//function call in admin
	public function showAdminFormParams($params)
	{
		if ($params == "") {
			$params = array();
		}


		$data                 = [];
		$data['vat_catalog']  = array(array('id' => '0', 'name' => 'Брать из настроек товара'));
		$data['vat_delivery'] = array(array('id' => '0', 'name' => 'Брать из настроек доставки'));
		$data['vat_list']     = array(
			array('id' => 'none', 'name' => 'Без НДС'),
			array('id' => 'vat0', 'name' => 'НДС по ставке 0%'),
			array('id' => 'vat10', 'name' => 'НДС чека по ставке 10%'),
			array('id' => 'vat20', 'name' => 'НДС чека по ставке 20%'),
			array('id' => 'vat110', 'name' => 'НДС чека по расчетной ставке 10%'),
			array('id' => 'vat120', 'name' => 'НДС чека по расчетной ставке 20%'),
		);

		$data['sno_list'] = array(
			array('id' => 'osn', 'name' => 'Общая СН'),
			array('id' => 'usn_income', 'name' => 'Упрощенная СН (доходы)'),
			array('id' => 'usn_income_outcome', 'name' => 'Упрощенная СН (доходы минус расходы)'),
			array('id' => 'envd', 'name' => 'Единый налог на вмененный доход'),
			array('id' => 'esn', 'name' => 'Единый сельскохозяйственный налог'),
			array('id' => 'patent', 'name' => 'Патентная СН'),
		);

		$data['payment_scheme_list'] = array(
			array('id' => 'sms', 'name' => 'Одностадийная'),
			array('id' => 'dms', 'name' => 'Двухстадийная'),
		);
		$data['test_list'] = array(
			array('id' => '0', 'name' => 'Рабочий'),
			array('id' => '1', 'name' => 'Тестовый'),
		);
		$data['allow_ip_list'] = array(
			array('id' => '0', 'name' => 'Только с IP Mixplat'),
			array('id' => '1', 'name' => 'С любых IP'),
		);

		$data['send_receipt_list'] = array(
			array('id' => '0', 'name' => 'Нет'),
			array('id' => '1', 'name' => 'Да'),
		);

		$data['payment_method_list'] = array(
			array('id' => 'full_prepayment', 'name' => 'Предоплата 100%'),
			array('id' => 'prepayment', 'name' => 'Предоплата'),
			array('id' => 'advance', 'name' => 'Аванс'),
			array('id' => 'full_payment', 'name' => 'Полный расчет'),
			array('id' => 'partial_payment', 'name' => 'Частичный расчет и кредит'),
			array('id' => 'credit', 'name' => 'Передача в кредит'),
			array('id' => 'credit_payment', 'name' => 'Оплата кредита'),
		);

		$data['payment_object_list'] = array(
			array('id' => 'commodity', 'name' => 'Товар'),
			array('id' => 'excise', 'name' => 'Подакцизный товар'),
			array('id' => 'job', 'name' => 'Работа'),
			array('id' => 'service', 'name' => 'Услуга'),
			array('id' => 'payment', 'name' => 'Платеж'),
			array('id' => 'property_right', 'name' => 'Передача имущественных прав'),
			array('id' => 'composite', 'name' => 'Составной предмет расчета'),
			array('id' => 'another', 'name' => 'Другое'),
		);

		$settings = array(
			'api_key'                    => '',
			'project_id'                 => '',
			'form_id'                    => '',
			'description'                => 'Оплата заказа №%order_number%',
			'test'                       => 1,
			'send_receipt'               => 1,
			'sno'                        => 'usn_income_outcome',
			'product_vat'                => 'none',
			'delivery_vat'               => 'none',
			'payment_method'             => 'full_prepayment',
			'payment_object'             => 'commodity',
			'payment_object_delivery'    => 'service',
			'transaction_end_status'     => 6, //Paid
			'transaction_pending_status' => 1, //Pending
			'transaction_refund_status'  => 4, //Refunded
			'transaction_capture_status' => 2, //Refunded
			'payment_scheme'             => 'sms',
			'allow_ip'                  => 1,
			'mixplat_ip_list'            => "185.77.233.27,185.77.233.29",
		);
		foreach ($settings as $key => $value) {
			if (!isset($params[$key])) {
				$params[$key] = $value;
			}

		}

		$orders = JModelLegacy::getInstance('orders', 'JshoppingModel'); //admin model
		include dirname(__FILE__) . "/adminparamsform.php";
	}

	public function checkTransaction($pmconfigs, $order, $act)
	{
		$content = file_get_contents('php://input');
		$data = json_decode($content, true);
		if (!$data) {
			return false;
		}
		$this->log($data, 'callback');
		if (!$this->isValidRequest($pmconfigs)) {
			return false;
		}

		$sign = MixplatLib::calcActionSignature($data, $pmconfigs['api_key']);
		if (strcmp($sign, $data['signature']) !== 0) {
			return false;
		}
		echo json_encode(['result'=>'ok']);
		$this->updateTransaction($data);
		if (
			$data['status'] !== 'success'
			&& $data['status_extended'] !== 'pending_authorized') {
			return false;
		}

		$status   = $pmconfigs['transaction_end_status'];
		$checkout = JModelLegacy::getInstance('checkout', 'jshop');
		if (!$order->order_created) {
			$order->order_created = 1;
			$order->order_status  = $status;
			$order->store();
			$checkout->sendOrderEmail($order->order_id);
			$order->changeProductQTYinStock("-");
			$checkout->changeStatusOrder($order->order_id, $status, 0);
		} else {
			$checkout->changeStatusOrder($order->order_id, $status, 1);
		}
		die();
	}

	public function showEndForm($pmconfigs, $order)
	{
		$jshopConfig = JSFactory::getConfig();
		$amount      = intval($order->order_total * 100);

		$db = JFactory::getDBO();

		$notifyUrl = JURI::root() . "index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_mixplat&no_lang=1&order_id=" . $order->order_id;

		$data = array(
			'amount'              => $amount,
			'test'                => $pmconfigs['test'],
			'project_id'          => $pmconfigs['project_id'],
			'payment_form_id'     => $pmconfigs['form_id'],
			'request_id'          => MixplatLib::getIdempotenceKey(),
			'merchant_payment_id' => $order->order_id,
			'user_email'          => $order->email,
			'url_success'         => JURI::root() . 'index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_mixplat',
			'url_failure'         => JURI::root() . 'index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=pm_mixplat',
			'notify_url'          => $notifyUrl,
			'payment_scheme'      => $pmconfigs['payment_scheme'],
			'description'         => $this->getPaymentDescription($pmconfigs, $order),
		);
		if ($pmconfigs['send_receipt']) {
			$data['items'] = $this->getReceiptItems($pmconfigs, $order);
		}

		$data['signature'] = MixplatLib::calcPaymentSignature($data, $pmconfigs['api_key']);
		$this->log($data, 'paymentForm');
		try {
			$result = MixplatLib::createPayment($data);
			$this->insertTransaction($result->payment_id, $amount, $order->order_id);
			$html = 'Сейчас вы будете перемещены на страницу оплаты, если этого не произошло, нажмите кнопку "перейти к оплате".<form method="get" action="' . $result->redirect_url . '"  name="paymentform" id="paymentform" >';
			$html .= "<input type='submit' name='' value='Перейти к оплате'>";
			$html .= '</form>';

		} catch (Exception $e) {
			$this->log($e->getMessage(), 'error');
			$session = JFactory::getSession();
			$session->set('jshop_send_end_form', 0);
			JFactory::getApplication()->redirect(SEFLink('index.php?option=com_jshopping&controller=checkout&task=step3', 0, 1, $jshopConfig->use_ssl), 'Ошибка при оплате: ' . $e->getMessage());
			return false;
		}

		$order->order_created = 1;
		$order->order_status  = $pmconfigs['transaction_pending_status'];
		$order->store();
		$checkout = JModelLegacy::getInstance('checkout', 'jshop');
		$checkout->sendOrderEmail($order->order_id);
		?>
		<html>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		</head>
		<body>
		<?php echo $html; ?>
		<script type="text/javascript">document.getElementById('paymentform').submit();</script>
		</body>
		</html>
<?php

		die();
	}

	public function capture($pmconfigs, $order_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id, amount FROM #__jshopping_mixplat WHERE order_id=$order_id and status_extended='pending_authorized'");
		$transaction = $db->loadObject();
		if ($transaction) {
			$this->confirmPayment($pmconfigs, $transaction->id, $transaction->amount);
		}
	}

	public function refund($pmconfigs, $order_id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id, amount, status, status_extended FROM #__jshopping_mixplat WHERE order_id=$order_id and (status='success' or status_extended='pending_authorized')");
		$transaction = $db->loadObject();
		if (!$transaction) {
			return;
		}
		if ($transaction->status == 'success') {
			$this->returnPayment($pmconfigs, $transaction->id, $transaction->amount);
		}

		if ($transaction->status_extended == 'pending_authorized') {
			$this->cancelPayment($pmconfigs, $transaction->id);
		}
	}

	private function confirmPayment($pmconfigs, $paymentId, $amount)
	{
		$this->log([$paymentId, $amount], 'confirm');
		$query  = array(
			'payment_id' => $paymentId,
			'amount'     => $amount,
		);
		$query['signature'] = MixplatLib::calcActionSignature($query, $pmconfigs['api_key']);
		$result = MixplatLib::confirmPayment($query);
		$this->log($result, 'confirmResult');
	}

	private function cancelPayment($pmconfigs, $paymentId)
	{
		$this->log($paymentId, 'cancel');
		$query = array(
			'payment_id' => $paymentId,
		);
		$query['signature'] = MixplatLib::calcActionSignature($query, $pmconfigs['api_key']);
		MixplatLib::cancelPayment($query);
		$result = $this->log($result, 'cancleResult');
	}

	private function returnPayment($pmconfigs, $paymentId, $amount)
	{
		$this->log([$paymentId, $amount], 'return');
		$query  = array(
			'payment_id' => $paymentId,
			'amount'     => $amount,
		);
		$query['signature'] = MixplatLib::calcActionSignature($query, $pmconfigs['api_key']);
		$return = MixplatLib::refundPayment($query);
		$this->log($result, 'returnResult');
	}

	private function getReceiptItems($pmconfigs, $order_id)
	{
		$jshopConfig = JSFactory::getConfig();
		$order = JSFactory::getTable('order', 'jshop');
        $order->load($order_id);

		$order->prepareOrderPrint('order_show');
		$total      = intval($order->order_total * 100);
		$items       = [];

		$order->loadItemsNewDigitalProducts();
        $order_items = $order->getAllItems();

		foreach ($order_items as $product) {
			if ($pmconfigs['product_vat']) {
				$vat = $pmconfigs['product_vat'];
			} else {
				switch (intval($product->product_tax)) {
					case 20:$vat = 'vat20';
						break;
					case 10:$vat = 'vat10';
						break;
					default:$vat = 'none';
						break;
				}
			}
			$items[] = array(
				"name"     => $product->product_name,
				"quantity" => $item->product_quantity,
				"sum"      => round($product->product_item_price * $product->product_quantity * 100),
				"vat"      => $vat,
				"method"   => $pmconfigs['payment_method'],
				"object"   => $pmconfigs['payment_object'],
			);
		}

		$shipping        = false;
		$shippingModel   = JSFactory::getTable('shippingMethod', 'jshop');
		$shippingMethods = $shippingModel->getAllShippingMethodsCountry($order->d_country, $order->payment_method_id);
		foreach ($shippingMethods as $tmp) {
			if ($tmp->shipping_id == $order->shipping_method_id) {
				$shipping = $tmp;
			}
		}

		if ($order->shipping_method_id && $shipping) {
			if ($pmconfigs['delivery_vat']) {
				$vat = $pmconfigs['delivery_vat'];
			} else {
				switch ($taxes[$shipping->shipping_tax_id]->tax_value) {
					case 20:$vat = 'vat20';
						break;
					case 10:$vat = 'vat10';
						break;
					default:$vat = 'none';
						break;
				}
			}
			$items[] = array(
				"name"     => $shipping->name,
				"quantity" => $item->product_quantity,
				"sum"      => round($shipping->shipping_stand_price * 100),
				"vat"      => $vat,
				"method"   => $pmconfigs['payment_method'],
				"object"   => $pmconfigs['payment_object_delivery'],
			);
		}

		$items = MixplatLib::normalizeReceiptItems($items, $total);
		return $items;
	}

	private function insertTransaction($paymentId, $amount, $orderId)
	{
		$db = JFactory::getDBO();
		$db->setQuery("INSERT INTO #__jshopping_mixplat
			(id, amount, order_id, status) VALUES
			(".$db->Quote($paymentId).",".$db->Quote($amount).",".$db->Quote($orderId).",'new')");
		$db->query();
	}

	private function updateTransaction($data)
	{
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__jshopping_mixplat SET
			status=" . $db->Quote($data['status']) . ",
			status_extended=" . $db->Quote($data['status_extended']) . ",
			extra = " . $db->Quote(json_encode($data)) . "
			WHERE id=" . $db->Quote($data['payment_id']));
		$db->query();

	}

	private function getPaymentDescription($pmconfigs, $order)
	{
		$description = str_replace(
			array('%order_number%', '%email%'),
			array($order->order_number, $order->email),
			$pmconfigs['description']);
		return $description;
	}

	private function isValidRequest($pmconfigs)
	{
		if (!$pmconfigs['allow_ip']) {
			$mixplatIpList = explode(",", $pmconfigs['mixplat_ip_list']);
			$mixplatIpList = array_map(function ($item) {return trim($item);}, $mixplatIpList);
			$ip = MixplatLib::getClientIp();
			if (!in_array($ip, $mixplatIpList)) {
				return false;
			}
		}
		return true;
	}

	public function log($data, $category) {
		if (!is_scalar($data)) {
			$data = var_export($data, true);
		}
		JLog::add($category.':'.$data, JLog::INFO, 'pm_mixplat');
	}

	public function getUrlParams($pmconfigs)
	{
		$params                      = array();
		$content = file_get_contents('php://input');
		$data = json_decode($content, true);
		if (!$data) {
			return $params;
		}
		$params['order_id']          = $data['merchant_payment_id'];
		$params['hash']              = "";
		$params['checkHash']         = 0;
		$params['checkReturnParams'] = $pmconfigs['checkdatareturn'];
		return $params;
	}

}
