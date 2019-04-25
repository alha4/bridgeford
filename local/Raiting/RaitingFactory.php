<?php

namespace Raiting;

require_once $_SERVER['DOCUMENT_ROOT']."/local/Raiting/BaseRaiting.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Raiting/RentalRaiting.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Raiting/SaleRaiting.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Raiting/RentalBusinessRaiting.php";

final class RaitingFactory {

  public static function create(array $object, int $type) : BaseRaiting {

    switch($type) {

      case 0 :
      
      return new RentalRaiting($object);
      
      case 1 :
      
      return new SaleRaiting($object);

      case 2 :
      
      return new RentalBusinessRaiting($object);

    }


  }

}