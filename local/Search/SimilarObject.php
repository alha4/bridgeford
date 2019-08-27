<?php

namespace Search; 

use \Bitrix\Main\Loader,
     Bitrix\Crm\DealTable,
     Bitrix\Main\ORM\Query\Result;

Loader::IncludeModule("crm");


class SimilarObject {

  private const MOSKOW_REGION = [26, 27];

  private function __construct() {}

  public static function search(int $id) : Result {

   $sort = ['ID' => 'DESC'];

   $filter = ['CHECK_PERMISSIONS' => 'N', 'ID' => $id];

   /** 
    *  UF_CRM_1540202667 [enumeration] - Регион
    *  UF_CRM_1540202766 [string]  -  Район
    *  UF_CRM_1540203111 [enumeration] - Округ
    *  UF_CRM_1544431330 [string] - Окупаемость
    *  UF_CRM_1541076330647 [string] - Площадь
    *  UF_CRM_1545649289833 [string] - Реальная цена
    */

    $select = ['UF_CRM_1541076330647','UF_CRM_1545649289833','UF_CRM_1540203111','UF_CRM_1540202667','UF_CRM_1540202766','UF_CRM_1544431330','CATEGORY_ID'];

    $category_id = \CCrmDeal::GetCategoryID($id);
    
    $current = \CCrmDeal::GetList($sort, $filter, $select);
    $arResult = $current->Fetch();

// $logger = \Log\Logger::instance();
// $logger->setPath("/local/logs/SimilarObject.txt");
// $logger->info([$category_id]); 

    $real_price = (int)$arResult['UF_CRM_1545649289833'];
    $square     = (int)$arResult['UF_CRM_1541076330647'];
    
    $price_from =  $real_price - ($real_price / 100) * FILTER_PRECENT;
    $price_to   =  $real_price + ($real_price / 100) * FILTER_PRECENT;
    
    $square_from = $square  - ($square  / 100) * FILTER_PRECENT;
    $square_to   = $square  + ($square  / 100) * FILTER_PRECENT;
    
    if(in_array($arResult['UF_CRM_1540202667'], self::MOSKOW_REGION)) {
    
    $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
       where('CATEGORY_ID','=', $category_id)->
       where('UF_CRM_1540202667', '=',  $arResult['UF_CRM_1540202667'])->
       where('UF_CRM_1540203111', '=',  $arResult['UF_CRM_1540203111'])->
       whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
       whereBetween("UF_CRM_1545649289833", $price_from, $price_to)->exec();
    
    } else {
    
      $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
      where('CATEGORY_ID','=', $category_id)->
      where('UF_CRM_1540202667', '=',  $arResult['UF_CRM_1540202667'])->
      where('UF_CRM_1540202766', '=',  $arResult['UF_CRM_1540202766'])->
      whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
      whereBetween("UF_CRM_1545649289833", $price_from, $price_to)->exec();
    
    }
    

    return $object;

  }
}