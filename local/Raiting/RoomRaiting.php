<?php

namespace Raiting;

abstract class RoomRaiting {

  protected $object;

  public function __construct(array $object)  {

     $this->object = $object;

  }

  public function summ() : int {

     return  $this->sizeCommision() + $this->countCompetitors() + $this->costObject(); 

  }

  /**
   * UF_CRM_1540532735882 - % от МАП
   * UF_CRM_1554303694 - % от цены объекта
   */
  protected function sizeCommision() : int {

     $precent = $this->object['UF_CRM_1554303694'];

     if(!$precent) {

        return 0;

     } elseif($precent <= 1 && $precent > 0) {

        return 1;

     } elseif($precent == 1.5) {

        return 2;

     } elseif($precent == 2) {

        return 3;

     } elseif($precent == 2.5) {

        return 4;

     } elseif($precent >= 3) {

        return 5;

     }
  }

  /**
   * UF_CRM_1542089326915 - количество конкурентов
   */
  protected function countCompetitors() : int {

    $competitors = $this->object['UF_CRM_1542089326915'];

    if(!$competitors) {

       return 5;

    } elseif($competitors == 1) {

       return 4;

    } elseif($competitors <= 3) {

      return 3;

    } elseif($competitors <= 5) {

      return 2;

    } elseif($competitors <= 7) {

      return 1;

    } else {

      return 0;

    }

  }

  /**
   * UF_CRM_1541072013901 - цена объекта
   */
  protected function costObject() : int {

    $price = (int)$this->object['UF_CRM_1541072013901'];

    if($price >= 100000000 && $price <= 200000000) {

        return 5;

    } elseif($price >= 200000000 && $price <= 250000000 ||
             $price >= 70000000 && $price <= 100000000) {

      return 4;

    } elseif($price >= 250000000 && $price <= 300000000 ||
             $price >= 50000000 && $price <= 70000000) {

      return 3;

    } elseif($price >= 300000000 && $price <= 500000000 ||
            $price >= 30000000 && $price <= 70000000) {

      return 2;

    } elseif($price >= 500000000 && $price <= 1000000000 ||
            $price >= 15000000 && $price <= 30000000) {

      return 1;

    } elseif($price > 1000000000 || $price <= 15000000) {

      return 0;

    }

  }
}

