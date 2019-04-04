<?php

namespace Raiting;

use Raiting\RoomRaiting;

class RentalRaiting extends RoomRaiting {

  protected function sizeCommision() : int {

    $precent = $this->object['UF_CRM_1540532735882'];
     
    if(!$precent) {

      return 0;

    } elseif($precent == 100) {

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

    if($price >= 500000 && $price <= 700000) {

        return 5;

    } elseif($price >= 700000 && $price <= 1000000 ||  
             $price >= 300000 && $price <= 5000000) {

        return 4;

    } elseif($price >= 1000000 && $price <= 1500000 || 
             $price >= 150000 && $price <= 3000000) {

        return 3;

    } elseif($price >= 1500000 && $price <= 2000000 || 
             $price >= 100000 && $price <= 150000) {

        return 2;

    } elseif($price >= 2000000 && $price <= 3000000 || 
             $price >= 50000 && $price <= 100000) {

        return 1;

    } elseif($price > 3000000 || $price < 50000) {

         return 0;

    }

  }
}