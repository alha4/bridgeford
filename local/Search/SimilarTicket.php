<?php

namespace Search; 

use \Bitrix\Main\Loader,
     Bitrix\Crm\LeadTable,
     Bitrix\Crm\DealTable,
     Bitrix\Main\ORM\Query\Result;

Loader::IncludeModule("crm");

class SimilarTicket {

  private const MOSKOW_REGION = [365];

  private function __construct() {}

  public static function search(int $id) : Result {

   $sort = ['ID' => 'DESC'];

   $filter = ['CHECK_PERMISSIONS' => 'N', 'ID' => $id];

  /** подбор Объекта из лида (заявки)
   *  UF_CRM_1545390144 [enumeration] - Регион 
   *  UF_CRM_1540202667 [enumeration] - Регион сделки
   *  UF_CRM_1545390183 [string]  - Район сделки
   *  UF_CRM_1540202766 [string]  - Район
   *  UF_CRM_1545390372 [enumeration] - Округ
   *  UF_CRM_1540203111 [enumeration] - Округ сделки
   *  UF_CRM_1547120946759 [string] - Площадь
   *  UF_CRM_1547551210 [string] - Стоимость аренды
   *  UF_CRM_1541076330647 [string] - Площадь
   *  UF_CRM_1545649289833 [string] - Реальная цена
   *  
  */

    $select = ['UF_CRM_1545390144','UF_CRM_1545390372','UF_CRM_1540202766','UF_CRM_1547551210','UF_CRM_1547120946759'];
    
    $current = \CCrmLead::GetList($sort, $filter, $select);
    $arResult = $current->Fetch();

    $region_value = enumValue($arResult['UF_CRM_1545390144'],'UF_CRM_1545390144');
    $area_value  = enumValue($arResult['UF_CRM_1545390372'],'UF_CRM_1545390372');

    $region = enumID($region_value, 'UF_CRM_1540202667');
    $area   = enumID($area_value, 'UF_CRM_1540203111');

    $real_price = (int)$arResult['UF_CRM_1547551210'];
    $square     = (int)$arResult['UF_CRM_1547120946759'];
    
    $price_from =  $real_price - ($real_price / 100) * FILTER_PRECENT;
    $price_to   =  $real_price + ($real_price / 100) * FILTER_PRECENT;
    
    $square_from = $square  - ($square  / 100) * FILTER_PRECENT;
    $square_to   = $square  + ($square  / 100) * FILTER_PRECENT;
    
    if(in_array($arResult['UF_CRM_1545390144'], self::MOSKOW_REGION)) {
    
    $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
       where('UF_CRM_1540202667', '=',  $region)->
       where('UF_CRM_1540203111', '=',  $area)->
       whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
       whereBetween("UF_CRM_1545649289833", $price_from, $price_to)->exec();
    
    } else {
    
      $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
      where('UF_CRM_1540202667', '=',  $region)->
      where('UF_CRM_1545390183', '=',  $arResult['UF_CRM_1540202766'])->
      whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
      whereBetween("UF_CRM_1545649289833", $price_from, $price_to)->exec();
    
    }
    
    return $object;

  }
}