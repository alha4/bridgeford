<?php

namespace Raiting;

use Raiting\BaseRaiting;

class RentalRaiting extends BaseRaiting {

  protected function sizeCommision() : int {

    $precent = $this->object['UF_CRM_1540532735882'];
     
    if(!$precent) {

      return 0;

    } elseif($precent >= 100 && $precent <= 150) {

      return 3;

    } elseif($precent == 150) {

      return 4;

   } elseif($precent == 200) {

      return 5;

   } elseif($precent == 50) {

      return 2;

   } elseif($precent < 50) {

      return 1;

   } 

  }

  protected function costObject() : int {

    $price = (int)$this->object['UF_CRM_1540456417'];

    #file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', $price);

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