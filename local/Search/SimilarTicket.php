<?php
namespace Search; 
use \Bitrix\Main\Loader,
     Bitrix\Crm\LeadTable,
     Bitrix\Crm\DealTable,
     Bitrix\Main\ORM\Query\Result;
Loader::IncludeModule("crm");
class SimilarTicket {
  private const MOSKOW_REGION = [365];

  private const CATEGORY_MAP = [
    '362' => 0,
    '363' => 1,
    '364' => 2
  ];

  private const OBJECT_ACTIVE = 357;

  private function __construct() {}

  public static function search(int $id) : Result {

   $sort = ['ID' => 'DESC'];

   $filter = ['CHECK_PERMISSIONS' => 'N', 'ID' => $id];
  /** подбор Объекта из лида (заявки)
   *  UF_CRM_1545389958 [enumeration] - Тип помещения
   *  UF_CRM_1545390144 [enumeration] - Регион 
   *  UF_CRM_1540202667 [enumeration] - Регион сделки
   *  UF_CRM_1545390183 [string]  - Район сделки
   *  UF_CRM_1540202766 [string]  - Район
   *  UF_CRM_1545390372 [enumeration] - Округ  - UF_CRM_1565850691 множественное
   *  UF_CRM_1540203111 [enumeration] - Округ сделки
   *  UF_CRM_1547120946759 [string] - Площадь
   *  UF_CRM_1565250252 [string] - Площадь До
   *  UF_CRM_1547551210 [string] - Стоимость аренды
   *  UF_CRM_1565250601 [string] - Стоимость аренды До
   *  UF_CRM_1541076330647 [string] - Площадь Сделки
   *  UF_CRM_1545649289833 [string] - Реальная цена Сделки
   *  UF_CRM_1541072013901 [double] - Стоимсть объекта Сделки
   *  UF_CRM_1545199624 [enumeration] - Статус объекта / Актив 357 - Да
  */
    $select = ['UF_CRM_1545390144','UF_CRM_1545390372','UF_CRM_1540202766','UF_CRM_1545389958',
    'UF_CRM_1547551210','UF_CRM_1547120946759','UF_CRM_1565250601','UF_CRM_1565250252','UF_CRM_1565850691','UF_CRM_1547628348754','UF_CRM_1565250284'];
    

    $current = \CCrmLead::GetList($sort, $filter, $select);
    $arResult = $current->Fetch();

    $region_value = enumValue($arResult['UF_CRM_1545390144'],'UF_CRM_1545390144');

    $area_value  = enumValue($arResult['UF_CRM_1545390372'],'UF_CRM_1545390372');
    
    $region = enumID($region_value, 'UF_CRM_1540202667');
   
    $area   = enumID($area_value, 'UF_CRM_1540203111');

    $real_price = (int)$arResult['UF_CRM_1547551210'];
    
    $square     = (int)$arResult['UF_CRM_1547120946759'];


    $area_value_multi = self::enumValuemulti($arResult['UF_CRM_1565850691']); // здесь множественное округ

    $area_multi = self::enumIDmulti($area_value_multi); 

   
    $okupaemost = $arResult['UF_CRM_1547628348754'];
    $okupaemost_to = $arResult['UF_CRM_1565250284'];

    if($okupaemost_to > 0) {

      $okupaemost_from = $okupaemost;
      
    } else {
    
      $okupaemost_from = $okupaemost  - ($okupaemost  / 100) * FILTER_PRECENT;
      $okupaemost_to   = $okupaemost  + ($okupaemost  / 100) * FILTER_PRECENT;

    }

    if($arResult['UF_CRM_1565250601'] > 0) {

      $price_from = $real_price;
      $price_to   = $arResult['UF_CRM_1565250601'];

    } else {

      $price_from =  $real_price - ($real_price / 100) * FILTER_PRECENT;
      $price_to   =  $real_price + ($real_price / 100) * FILTER_PRECENT;

    }

    if($arResult['UF_CRM_1565250252'] > 0) {

      $square_from = $square;
      $square_to   = $arResult['UF_CRM_1565250252'];

    } else {
    
      $square_from = $square  - ($square  / 100) * FILTER_PRECENT;
      $square_to   = $square  + ($square  / 100) * FILTER_PRECENT;

    }

    $type = self::CATEGORY_MAP[$arResult['UF_CRM_1545389958']];

    $logger = \Log\Logger::instance();
    $logger->setPath('/local/logs/similatticket_log.txt');
    $logger->info( ['area_value_multi' => $area_value_multi, 'area_multi' => $area_multi, $type, $arResult['UF_CRM_1545390144'], $square_from, $square_to, $price_from, $price_to,$okupaemost,$okupaemost_from, $okupaemost_to]);

// для АБ добавляем поле окупаемость в поиск

    if($type == '2' and $okupaemost != 0) {

       if(in_array($arResult['UF_CRM_1545390144'], self::MOSKOW_REGION)) {
    
         $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
         where('CATEGORY_ID','=', $type)->
         where('UF_CRM_1540202667', '=',  $region)->
         where('UF_CRM_1545199624', '=',  self::OBJECT_ACTIVE)->       // только Актив
         whereIn('UF_CRM_1540203111', $area_multi)->
         whereBetween("UF_CRM_1566542004", $okupaemost_from, $okupaemost_to)->   //окупаемость
         whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
         whereBetween("UF_CRM_1541072013901", $price_from, $price_to)->exec();
    
       } else {
    
         $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
         where('CATEGORY_ID','=', $type)->
         where('UF_CRM_1540202667', '=',  $region)->
         where('UF_CRM_1545199624', '=',  self::OBJECT_ACTIVE)->       // только Актив
         where('UF_CRM_1545390183', '=',  $arResult['UF_CRM_1540202766'])->
         whereBetween("UF_CRM_1566542004", $okupaemost_from, $okupaemost_to)->   //окупаемость
         whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
         whereBetween("UF_CRM_1545649289833", $price_from, $price_to)->exec();
    
       }
    
     return $object;

    }
    
    if(in_array($arResult['UF_CRM_1545390144'], self::MOSKOW_REGION)) {
    
       $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
       where('CATEGORY_ID','=', $type)->
       where('UF_CRM_1540202667', '=',  $region)->
       where('UF_CRM_1545199624', '=',  self::OBJECT_ACTIVE)->       // только Актив
       whereIn('UF_CRM_1540203111', $area_multi)->
       whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
       whereBetween("UF_CRM_1541072013901", $price_from, $price_to)->exec();
    
    } else {
    
      $object = DealTable::query()->addSelect("TITLE")->addSelect("ID")->
      where('CATEGORY_ID','=', $type)->
      where('UF_CRM_1540202667', '=',  $region)->
      where('UF_CRM_1545199624', '=',  self::OBJECT_ACTIVE)->       // только Актив
      where('UF_CRM_1545390183', '=',  $arResult['UF_CRM_1540202766'])->
      whereBetween("UF_CRM_1541076330647", $square_from, $square_to)->
      whereBetween("UF_CRM_1545649289833", $price_from, $price_to)->exec();
    
    }
    
    return $object;
  }

  private static function enumValuemulti(array &$data) : array {

    $enums = [];

    foreach($data as $value) {
    
      $enums[] = enumValue($value,'UF_CRM_1565850691','CRM_LEAD');

    }

    return $enums;

  }

  private static function enumIDmulti(array &$data) : array {

    $enums = [];

    foreach($data as $value) {
    
      $enums[] = enumID($value,'UF_CRM_1540203111');

    }

    return $enums;

  }
}
