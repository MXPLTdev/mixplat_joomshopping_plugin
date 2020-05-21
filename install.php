<?php
/**
 * @copyright	Copyright (C) 2020 mixplat.ru. All rights reserved.
 */
defined('_JEXEC') or die;
class pkg_joomshopping_mixplatInstallerScript{
	public function postflight( $type, $parent,$result ) {
		if ($type=='install'){
			$db = JFactory::getDBO();
			$db->setQuery('UPDATE #__extensions set enabled=1 where `type`="plugin" and (
				(element="jshopping_mixplat" and folder="jshoppingorder")
				)');
			$db->query();
			JFolder::move(dirname(__FILE__).'/pm_mixplat',JPATH_ROOT .'/components/com_jshopping/payments/pm_mixplat');
			$db->setQuery('insert into #__jshopping_payment_method (payment_code, payment_class,  payment_publish,  payment_type, price,  price_type,show_descr_in_email,`name_ru-RU`,`name_en-GB`) values("mixplat","pm_mixplat",0,2,0.00,0,0,"Оплата по карте","Оплата по карте")');
		    $db->query();
		    $id = $db->insertid();
		    $db->setQuery('
				CREATE TABLE IF NOT EXISTS `#__jshopping_mixplat` (
					`id` varchar(36)  NOT NULL,
					`order_id` int(11) NOT NULL,
					`status` varchar(20)  NOT NULL,
					`status_extended` varchar(30)  NOT NULL,
					`date` datetime NOT NULL,
					`extra` text,
					`amount` int(11) NOT NULL,
					PRIMARY KEY (order_id)
				)
		    	');
		    $db->query();
		    echo "<a href='index.php?option=com_jshopping&controller=payments&task=edit&payment_id=".$id."'>Перейти к настройке</a>";
		}

		return true;
	}

	function uninstall($x){
    $db = JFactory::getDBO();
    $db->setQuery('delete from  #__jshopping_payment_method where payment_class="pm_mixplat"');
    $db->query();
    $db->setQuery('DROP TABLE `#__jshopping_mixplat`');
    $db->query();
    JFolder::delete(JPATH_ROOT .'/components/com_jshopping/payments/pm_mixplat');
  }


}
