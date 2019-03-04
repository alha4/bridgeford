<?php

namespace PDF;

final class PdfController {

  const RENT = 0;

  const SALE = 1;

  const RENT_BUSSINES = 2;
   
  public static function run() : void {

    $doc_id  = (int)$_REQUEST['doc_id'];

    $template_type = $_REQUEST['template'];

    $request = \CCrmDeal::GetCategoryID($doc_id);

    switch($request) {

       case self::RENT :

            \PDF\Rent\RentBuilding::instance()->export($doc_id, \PDF\RentTemplate::instance($template_type));

       break;

       case self::SALE :

           \PDF\Sale\SaleBuilding::instance()->export($doc_id, \PDF\SaleTemplate::instance($template_type));

       break;

       case self::RENT_BUSSINES :

            \PDF\RentBussines\RentBussinesBuilding::instance()->export($doc_id, \PDF\RentBussinesTemplate::instance($template_type));

       break;

       default : 

         throw new Exception('Не корректный запрос');

    }


  }

}