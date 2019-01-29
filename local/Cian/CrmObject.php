<?php

 namespace Cian;

 use \Bitrix\Main\Loader,
     \Bitrix\Main\Error;

 Loader::IncludeModule("crm");
 /**
  * UF_CRM_1541076330647 - Площадь объекта
  * UF_CRM_1540202889 - тип улицы
  * UF_CRM_1540202900 - улица 
  * UF_CRM_1540202908 - номер дома
  * UF_CRM_1540202817 - город
  * UF_CRM_1540202667 - регион
  * UF_CRM_1540202766 - район
  * UF_CRM_1542955977 - геокодированные данные
  * UF_CRM_1541753539107 - шаг цены
  * UF_CRM_1541678101879 - активировано автоматическое ценообразование
  * UF_CRM_1542011568379 - изначальная цена
  * UF_CRM_1542028303 - стратегии автоматического ценообразования
  * UF_CRM_1542029182 - главный якорь
  * UF_CRM_1542089326915 - кол-во конкурентов
  * OPPORTUNITY - стоимость объекта циан
  */
 final class CrmObject {

   private const GEODATA_ROWS = 3;

   private const DEFAULT_CITY = 'Москва';

   public static $LAST_ERROR;

   public static function getAll(?int $object_id = 0) : array {

      $sort   = ['UF_CRM_1541678101879' => 'DESC'];

      $filter = ['UF_CRM_1541678101879' => 1, 'CHECK_PERMISSIONS' => 'N'];

      if($object_id > 0) {

        $filter['ID'] = $object_id;

      }

      $select = ['UF_CRM_1540202889','UF_CRM_1542029182','UF_CRM_1541076330647','UF_CRM_1542955977','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540202817','UF_CRM_1540202667','UF_CRM_1541753539107','CATEGORY_ID'];

      $arResult = [];

      $crm_object = \CCrmDeal::GetListEx($sort, $filter, false, false, $select);

      while($row = $crm_object->Fetch()) {

        $geodata = json_decode($row['UF_CRM_1542955977'], 1);

        if(count($geodata) >= self::GEODATA_ROWS) {
   
          $arResult[] = [

            'ID'     => $row['ID'],
            'SQUARE' => (int)$row['UF_CRM_1541076330647'],
            'STREET' => $geodata['STREET'],
            'HOUSE'  => $geodata['HOUSE'],
            'CITY'   => $geodata['CITY'],
            'IS_MOSKOW' => ($row['UF_CRM_1540202817'] == self::DEFAULT_CITY),
            'REGION' => $row['UF_CRM_1540202667'],
            'PRICE_STEP' => (float)$row['UF_CRM_1541753539107'],
            'CATEGORY_ID' => $row['CATEGORY_ID'],
            'MAIN_ANCHOR' => (int)$row['UF_CRM_1542029182'],
            'IS_DECODED'  => 'Y'
          ];


        } else {

         $arResult[] = [

           'ID'     => $row['ID'],
           'SQUARE' => (int)$row['UF_CRM_1541076330647'],
           'STREET' => self::street($row['UF_CRM_1540202900'], $row['UF_CRM_1540202889']),
           'HOUSE'  => $row['UF_CRM_1540202908'],
           'CITY'   => $row['UF_CRM_1540202817'],
           'IS_MOSKOW' => ($row['UF_CRM_1540202817'] == self::DEFAULT_CITY),
           'REGION' => $row['UF_CRM_1540202667'],
           'PRICE_STEP' => (float)$row['UF_CRM_1541753539107'],
           'CATEGORY_ID' => $row['CATEGORY_ID'],
           'MAIN_ANCHOR' => (int)$row['UF_CRM_1542029182'], 
           'IS_DECODED'  => 'N'
        ];

        }
      }

      return $arResult;

   }

   public static function street(?string $street = '', ?int $street_type = 0) : ?string {

    if($street_type > 0 && strlen($street) > 0) {

      return sprintf("%s %s", $street, self::streetType($street_type) );

    }

    return $street;

   }

   private static function streetType(?int $variant_id) : string {

    $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_DEAL", "FIELD_NAME" => 'UF_CRM_1540202889'));
    $entity = $entityResult->Fetch();

    $enumResult = \CUserTypeEnum::GetList($entity);

    while($enum = $enumResult->Fetch()) {

      if($enum['ID'] ==  $variant_id) {

         return $enum['VALUE'];

       }

    }

    return '';

   }

   public static function setCompetitors(int $id, array $data) : bool {

     $crm_object = new \CCrmDeal(false);

     $fields = [
        'UF_CRM_1542029126' => json_encode($data, JSON_UNESCAPED_UNICODE),
        'UF_CRM_1542089326915' => count($data) 
     ];
      
     if($crm_object->Update($id, $fields)) {
  
        return true;

     }

     self::$LAST_ERROR = $crm_object->LAST_ERROR;

     return false;
     
   }

   public static function setPrice(int $id, float $price, float $price_step) : bool {

     $deal = new \CCrmDeal(false);

     $price = $price - $price_step;

     $price_field = [

        'OPPORTUNITY' => $price

     ];

     if($deal->Update($id, $price_field)) {

        return true;

     }

     self::$LAST_ERROR = $deal->LAST_ERROR;

     return false;

   }

   public static function actuality(int $object_id) : bool {

    $deal = new \CCrmDeal(false);

    global $USER;

    $arFields = [

      'UF_CRM_1544528494' => 329,
      'UF_CRM_1544524903217' => date("d.m.Y"),
      'UF_CRM_1540895373'    => $USER->GetID()

    ];

    if($deal->Update($object_id, $arFields)) {

        return true;

     }

     self::$LAST_ERROR = $deal->LAST_ERROR;

     return false;

   }

   private function __construct(){}
 }