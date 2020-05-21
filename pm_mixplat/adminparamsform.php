<?php
?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
 <tr>
   <td style="width:250px;" class="key">
     API key
   </td>
   <td>
<input type = "text" class = "inputbox" name = "pm_params[api_key]" size="45" value = "<?php echo $params['api_key']?>" />
   </td>
 </tr>
  <tr>
   <td  class="key">
     Id проекта
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[project_id]" size="45" value = "<?php echo $params['project_id']?>" />
   </td>
 </tr>
 <tr>
   <td  class="key">
     Id платежной формы
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[form_id]" size="45" value = "<?php echo $params['form_id']?>" />
   </td>
 </tr>
 <tr>
   <td  class="key">
     Шаблон описания платежа
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[description]" size="45" value = "<?php echo $params['description']?>" />
   </td>
 </tr>
 <tr>
   <td  class="key">
     Режим
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['test_list'], 'pm_params[test]', 'class = "inputbox" size = "1"', 'id', 'name', $params['test'] );
    ?>
   </td>
 </tr>
 <tr>
   <td  class="key">
     Печать чека
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['send_receipt_list'], 'pm_params[send_receipt]', 'class = "inputbox" size = "1"', 'id', 'name', $params['send_receipt'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     НДС на товары
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', array_merge($data['vat_catalog'],$data['vat_list']), 'pm_params[product_vat]', 'class = "inputbox" size = "1"', 'id', 'name', $params['product_vat'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     НДС на доставку
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', array_merge($data['vat_delivery'],$data['vat_list']), 'pm_params[delivery_vat]', 'class = "inputbox" size = "1"', 'id', 'name', $params['delivery_vat'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Система налогообложения
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['sno_list'], 'pm_params[sno]', 'class = "inputbox" size = "1"', 'id', 'name', $params['sno'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Метод платежа
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['payment_method_list'], 'pm_params[payment_method]', 'class = "inputbox" size = "1"', 'id', 'name', $params['payment_method'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Предмет расчёта
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['payment_object_list'], 'pm_params[payment_object]', 'class = "inputbox" size = "1"', 'id', 'name', $params['payment_object'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Предмет расчёта на доставку
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['payment_object_list'], 'pm_params[payment_object_delivery]', 'class = "inputbox" size = "1"', 'id', 'name', $params['payment_object_delivery'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Статус оформленного заказа
   </td>
   <td>
     <?php
     echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status'] );
     echo " ".JHTML::tooltip("Статус заказа после его оформления.");
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Статус успешной оплаты
   </td>
   <td>
     <?php
     echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
     echo " ".JHTML::tooltip("Выберите статус заказа, который будет установлен, если транзакция прошла успешно.");
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Статус возврата
   </td>
   <td>
     <?php
     echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_refund_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_refund_status'] );
     echo " ".JHTML::tooltip("Будет осуществлён возврат средств покупателю при смене статуса заказа на указанный.");
     ?>
   </td>
 </tr>
 <tr>
   <td  class="key">
     Схема проведения платежа
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['payment_scheme_list'], 'pm_params[payment_scheme]', 'class = "inputbox" size = "1"', 'id', 'name', $params['payment_scheme'] );
    ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     Статус для подтверждения оплаты
   </td>
   <td>
     <?php
     echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_capture_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_capture_status'] );
     ?>
   </td>
 </tr>
<tr>
   <td  class="key">
     Разрешено принимать запросы
   </td>
   <td>
    <?php
    echo JHTML::_('select.genericlist', $data['allow_ip_list'], 'pm_params[allow_ip]', 'class = "inputbox" size = "1"', 'id', 'name', $params['allow_ip'] );
    ?>
   </td>
 </tr>
 <tr>
   <td  class="key">
     Список IP адресов Mixplat
   </td>
   <td>
   <textarea name="pm_params[mixplat_ip_list]" rows="4"><?php echo htmlspecialchars($params['mixplat_ip_list'])?></textarea>
   <?php echo " ".JHTML::tooltip("Через запятую");?>
   </td>
 </tr>

</table>
</fieldset>
</div>
<div class="clr"></div>