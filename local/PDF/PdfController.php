<?php

namespace PDF;

final class PdfController {

  const RENT = 0;

  const SALE = 1;

  const RENT_BUSSINES = 2;
   
  
  public static function run() : void {

    $doc_id  = (int)$_REQUEST['doc_id'];

    $request = \CCrmDeal::GetCategoryID($doc_id);

    switch($request) {

       case self::RENT :

            \PDF\Rent\RentBuilding::instance()->export($doc_id);

       break;

       case self::SALE :

            \PDF\Sale\SaleBuilding::instance()->export($doc_id);

       break;

       case self::RENT_BUSSINES :

            \PDF\RentBussines\RentBussinesBuilding::instance()->export($doc_id);

       break;

       default : 

         throw new Exception('Не корректный запрос');

    }


  }

}