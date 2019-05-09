<?php

namespace Raiting;

use Raiting\BaseRaiting;

class RentalRaiting extends BaseRaiting {

  /**
   * UF_CRM_1556185907 - double % от МАП
   * UF_CRM_1556182207180 - Фиксированная оплата
   * UF_CRM_1556182166156 - Тип вознаграждения
   * UF_CRM_1540456417    - Стоимость аренды за все помещение в месяц
   */
  protected function sizeCommision() : int {

    $precent = $this->object['UF_CRM_1556185907'];

    if($this->object['UF_CRM_1556182166156'] == self::FIX_PRICE_TYPE) {

       $fixPrice = (float)$this->object['UF_CRM_1556182207180'];
       $precent = (int) ($fixPrice * 100 / (float)$this->object['UF_CRM_1540456417']);


    } else {

      if(strpos($precent,'-') !== false) {

        $maxPrecent = (int)array_pop(explode("-",trim($precent)));
        $precent = $maxPrecent;

      }

    }
     
    if(!$precent) {

      return 0;

    } elseif($precent >= 100 && $precent < 150) {

      return 3;

    } elseif($precent >= 150 && $precent < 200) {

      return 4;

   } elseif($precent >= 200) {

      return 5;

   } elseif($precent >= 50 && $precent < 100) {

      return 2;

   } elseif($precent < 50) {

      return 1;

   }
   
   return 0;

  }

  protected function costObject() : int {

    $price = (int)$this->object['UF_CRM_1540456417'];

    if($price >= 400000 && $price <= 600000) {

        return 5;

    } elseif($price > 600000 && $price <= 1000000 ||  
             $price >= 250000 && $price < 400000) {

        return 4;

    } elseif($price > 1000000 && $price <= 1500000 || 
             $price >= 200000 && $price < 250000) {

        return 3;

    } elseif($price > 1500000 && $price <= 2000000 || 
             $price >= 150000 && $price < 200000) {

        return 2;

    } elseif($price > 2000000 && $price <= 2500000 || 
             $price >= 100000 && $price < 150000) {

        return 1;

    } elseif($price > 2500000 || $price < 100000) {

         return 0;

    }

  }
}