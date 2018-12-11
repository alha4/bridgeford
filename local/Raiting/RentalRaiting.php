<?php

namespace Raiting;

use Raiting\RoomRaiting;

class RentalRaiting extends RoomRaiting {


  protected function costObject() : int {

    $price = (int)$this->object['UF_CRM_1541072013901'];

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

    } elseif($price > 3000000 || $price <= 50000) {

         return 0;

    }

  }
}