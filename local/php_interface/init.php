<?

if(!$_SERVER["DOCUMENT_ROOT"]) {

  $_SERVER["DOCUMENT_ROOT"] = '/home/bitrix/ext_www/bf.angravity.ru';

}

use \Bitrix\Main\EventManager,
    \Raiting\RaitingFactory,
    \Bitrix\Main\Web\HttpClient,
    \Bitrix\Main\UserFieldTable;

const PSR_CLASS_PATH = '/local/Psr/Log';
const CIAN_ROOT_CLASS_PATH = '/local/Cian';
const STAT_ROOT_CLASS_PATH = '/local/Stat';
const SEARCH_ROOT_CLASS_PATH = '/local/Search';
const XML_PARSER_PATH = '/local/XML/Parser';
const XML_CLASS_PATH = '/local/XML';
const SEMANTIC_CLASS_PATH = '/local/XML/Semantic';
const LOG_PATH = '/local/Cian/log.txt';
const REQUEST_LOG = 'Y';
const GENERAL_BROKER = 15;
const FILTER_PRECENT = 33;
const YANDEX_API_KEY = '5e926399-e46a-4846-a809-c3d370aa399e';
const DEBUG_AUTOTEXT = 'Y';
const AUTOTEXT_API_URL = 'http://92.53.97.50/autotext.php';

const OBJECT_TYPE = [

  '0' => 'Аренда',
  '1' => 'Продажа помещения',
  '2' => 'Продажа арендного бизнеса'
  
];

const SEMANTIC_CODE = [

  '0' => 'UF_CRM_1540974006',
  '1' => 'UF_CRM_1544172451',
  '2' => 'UF_CRM_1544172560'

];

Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
     '\Cian\CianHelper'          => CIAN_ROOT_CLASS_PATH.'/CianHelper.php',
     '\Cian\CianPriceMonitoring' => CIAN_ROOT_CLASS_PATH.'/CianPriceMonitoring.php',
     '\Cian\CrmObject'           => CIAN_ROOT_CLASS_PATH.'/CrmObject.php',
     '\Cian\Logger'              => CIAN_ROOT_CLASS_PATH.'/Logger.php',
     '\Stat\CompetitorEvent'     => STAT_ROOT_CLASS_PATH.'/CompetitorEvent.php',
     '\Search\SimilarObject'     => SEARCH_ROOT_CLASS_PATH."/SimilarObject.php",
     '\Search\SimilarTicket'     => SEARCH_ROOT_CLASS_PATH."/SimilarTicket.php",
     '\XML\Helpers\Description'  => XML_CLASS_PATH.'/Helpers/Description.php',
     '\Semantic\SemanticFactory' => SEMANTIC_CLASS_PATH.'/SemanticFactory.php',
     '\XML\Heplers\WhaterMark'   => XML_CLASS_PATH.'/Helpers/WhaterMark.php',
     '\XML\ExportBase'           => XML_CLASS_PATH.'/ExportBase.php',
     '\XML\Helpers\ExportHelper' => XML_CLASS_PATH.'/Helpers/ExportHelper.php',
     '\XML\Parser\ParserFactory' => XML_PARSER_PATH."/ParserFactory.php",
     '\XML\Parser\Parser'        => XML_PARSER_PATH."/Parser.php",
     '\XML\Parser\ObjectParser'  => XML_PARSER_PATH.'/ObjectParser.php',
     '\XML\Parser\ParserHelper'  => XML_PARSER_PATH.'/ParserHelper.php',
     '\Psr\Log\LoggerInterface'  => PSR_CLASS_PATH .'/LoggerInterface.php',
     '\Psr\Log\LogLevel'         => PSR_CLASS_PATH .'/LogLevel.php',
     '\Log\Logger'               => '/local/Log/Logger.php'
));  

    
$event = EventManager::getInstance();

$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setGeoData');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setSquareClone');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setPaybackAutotext');
$event->addEventHandler('crm', 'OnAfterCrmLeadUpdate', 'setTiketSquareClone');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setRaiting');
//$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setAdvertisingStatus');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setRealPrice');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setWatermark');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setAutotext');

$event->addEventHandler('crm', 'OnBeforeCrmDealUpdate', 'setMapLocation');

require_once $_SERVER['DOCUMENT_ROOT']."/local/Cian/CianPriceMonitoring.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Cian/CrmObject.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Raiting/RaitingFactory.php";


function setAutotext($arFields)  {

  $select = ['ID','UF_CRM_1540202667','UF_CRM_1540202889','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540203015',
           'UF_CRM_1543406565','UF_CRM_1540203111','UF_CRM_1540371261836','UF_CRM_1540371455','UF_CRM_1540371585',
           'UF_CRM_1540371563','UF_CRM_1556020811397','UF_CRM_1540384807664','UF_CRM_1540384944','UF_CRM_1541076330647',
           'UF_CRM_1540384963','UF_CRM_1540385040','UF_CRM_1540385060','UF_CRM_1540385112','UF_CRM_1540974006','UF_CRM_1544172451',
           'UF_CRM_1544172560','UF_CRM_1540456417','UF_CRM_1541072013901','UF_CRM_1540392018', 'UF_CRM_1540397421'];

  $keys = array_keys($arFields);
  $id_index = array_search('ID',$keys);
  $keys[$id_index] = null;
  unset($keys[$id_index]);
  
  if(count(array_intersect($select, $keys)) > 0) {

  $filter = ['CHECK_PERMISSIONS' => "N", "ID"=> $arFields['ID']];

  $objects = \CCrmDeal::GetList(['ID'=>"DESC"], $filter, $select);

  $jsonData = [];

  while( $object = $objects->Fetch() ) {

   $rawData = [];

   foreach($object as $code=>&$value) {

    if($code != 'ID') {

      $userField = UserFieldTable::getList(array(

        'filter' => ['FIELD_NAME'=> $code],
        'select' => ['USER_TYPE_ID','MULTIPLE']
  
      ))->fetchAll()[0];

      //print_r($userField);

      switch($userField['USER_TYPE_ID']) {

        case 'enumeration' :

        if($userField['MULTIPLE'] != 'Y' && !is_array($value)) {

           #echo $userField['MULTIPLE'], $code,'<br>';
  
          if(!is_null($value))

             $value = enumValue($value, $code);

        } else {

           #echo $userField['MULTIPLE'], $code,'<br>';

           if(in_array($code, \SEMANTIC_CODE)) {

              $code = \SEMANTIC_CODE[\CCrmDeal::GetCategoryID($object['ID'])];

              #echo $object['ID'],' ',$code,'<br>';


           }

           $values = [];

           foreach($value as $enumID) {

             $enumValue = enumValue($enumID, $code);

             if(!is_numeric($enumValue) && !is_integer($enumValue)) {

                $values[] = $enumValue;

             }
           }

           $value = $values;

        }

        break;

        case 'iblock_element' :

        $value = iblockValue($value);

        break;


      }

    }

    if($value) {

      $rawData['ID'] = $object['ID'];
      $rawData['CATEGORY_ID'] = OBJECT_TYPE[\CCrmDeal::GetCategoryID($object['ID'])];
      $rawData[$code] = $value;
      
    }
   }

   $jsonData[] = $rawData;

  }

  /*$http = new HttpClient();

  $http->setHeader("Content-Type","application/json");

  $responce = $http->post(\AUTOTEXT_API_URL, json_encode($jsonData,JSON_UNESCAPED_UNICODE));

  if($http->getStatus() == 200) {


  }

  $logger = \Log\Logger::instance();
  $logger->setPath('/local/logs/autotext_log.txt');
  $logger->info( $responce);*/

 }

}

function setWatermark(&$arFields) {

 if(array_key_exists('UF_CRM_1540532330',$arFields)) {

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], ["ID","UF_CRM_1540532330"])->Fetch();

  $watermark = new \XML\Heplers\WhaterMark();
  $watermark->setPath('/upload/newwater.png');

  $logger = \Log\Logger::instance();
  $logger->setPath('/local/logs/watermark_log.txt');

  $watermark->setLogger($logger);

  $files = $crm_object['UF_CRM_1540532330'];

  $arWatermark = [];

  foreach($files as $fileID) {

    $arWatermark[] = \CFile::MakeFileArray($watermark->createWhaterMark($fileID));

  }

  $fields = ['UF_CRM_1559649507' => $arWatermark];

  $userField = new \CUserTypeManager();

  if(!$userField->Update("CRM_DEAL", $crm_object['ID'], $fields)) {

     $logger->error([$row['ID'], $userField]);

  } else {

     $logger->info(['ID' => $crm_object['ID'], 'message' => 'водяной знак наложен']);

  }

 }

}

function mapPicureUpdate(int $id) {

  $select = ['UF_CRM_1540202889','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540202817','UF_CRM_1540202667'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' =>  $id ], $select);

  $data = $crm_object->Fetch();

  $adress = sprintf("Россия,Москва,%s+%s,дом+%s", enumValue($data['UF_CRM_1540202889'],'UF_CRM_1540202889'), $data['UF_CRM_1540202900'], $data['UF_CRM_1540202908']);

  $http = new HttpClient();

  $result = json_decode($http->get(sprintf("https://geocode-maps.yandex.ru/1.x/?apikey=%s&geocode=%s&format=json&lang=ru_RU&rspn=0", YANDEX_API_KEY, $adress )) ,1);

  $ll = str_replace(' ',',', $result['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos']);

  $mapUrl = "https://static-maps.yandex.ru/1.x/?ll=$ll&size=500,420&z=16&l=map&pt=$ll,pm2bll";

  $arFile = CFile::MakeFileArray($mapUrl);

  $type = array_pop(explode('/',$arFile['type']));

  $arFile['name'] ="{$arFile['name']}.{$type}";
  $arFile['del'] = 'Y';
  $arFile['MODULE_ID'] = 'crm';
  $arFile['SUBDIR'] = 'crm_deal_map';

  $arFields['UF_CRM_1548410231729'] = $arFile;

  file_put_contents($_SERVER['DOCUMENT_ROOT'].'/map_log.txt', print_r( $arFile  ,1).date("d/m/Y H:i:s")."\r\n", FILE_APPEND);

  return $arFile;

}

/**
 * UF_CRM_1540202889 - Тип улицы 
 * UF_CRM_1540202900 - Название улицы 
 * UF_CRM_1540202908 - Номер дома
 * UF_CRM_1540202817 - Город 
 * UF_CRM_1540202667 - Регион
 * 
 */
  
function setMapLocation(&$arFields)  {
 
  $select = ['UF_CRM_1540202889','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540202817','UF_CRM_1540202667'];

  if(array_intersect(array_keys($arFields),$select)) {

  $arFields['UF_CRM_1548410231729'] = mapPicureUpdate($arFields['ID']);

  //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/map_log.txt', print_r( $arFile  ,1).date("d/m/Y H:i:s")."\r\n", FILE_APPEND);

  return $arFields;

  } else {

  //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/map_log.txt', print_r($arFields  ,1).date("d/m/Y H:i:s")."\r\n", FILE_APPEND);

  }

}


/**
 * UF_CRM_1545649289833 - Реальная цена 
 * UF_CRM_1540456417 - Стоимость аренды за все помещение в месяц
 * UF_CRM_1541072013901 - Стоимость объекта 
 */
function setRealPrice(&$arFields) : void {

  $select = ['UF_CRM_1540456417','UF_CRM_1541072013901'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $select);

  $object = $crm_object->Fetch();

  $category = \CCrmDeal::GetCategoryID($arFields['ID']);

  if($category > 0 && $object['UF_CRM_1541072013901'] > 0) {

    $UF = new CUserTypeManager;

    $fields = [
 
      'UF_CRM_1545649289833' => "{$object['UF_CRM_1541072013901']}|RUB"
  
    ];

    if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

    }

  } elseif($object['UF_CRM_1540456417'] > 0) {

    $UF = new CUserTypeManager;

    $fields = [
 
      'UF_CRM_1545649289833' => "{$object['UF_CRM_1540456417']}|RUB"
  
    ];

    if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

    }
  }

}

/**
 * UF_CRM_1556185907 -  % от МАП
 * UF_CRM_1542089326915 - кол-во конкурентов
 * UF_CRM_1541072013901 - Стоимость объекта
 * UF_CRM_1540456417    - Стоимость аренды за все помещение в месяц
 * UF_CRM_1544446024    - Рейтинг
 * UF_CRM_1556182166156 - Тип вознаграждения
 * UF_CRM_1556182207180 - Фиксированная оплата
 * UF_CRM_1556277658242 - Ручное заполнение рейтинга
 */
function setRaiting(&$arFields) : void {

  global $USER;

  $select = ['UF_CRM_1556277658242','UF_CRM_1540456417','UF_CRM_1556185907','UF_CRM_1542089326915','UF_CRM_1541072013901','UF_CRM_1556186036149','UF_CRM_1556182166156','UF_CRM_1556182207180'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $select);

  $arFields = array_merge($arFields , $crm_object->Fetch());

  if($USER->isAdmin() && $arFields['UF_CRM_1556277658242'] == 1) {

    return;

  }

  $raiting = RaitingFactory::create($arFields, \CCrmDeal::GetCategoryID($arFields['ID']));

  $UF = new CUserTypeManager;

  $fields = [
 
    'UF_CRM_1544446024' => $raiting->summ()

  ];

  if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

  }
}
/**
 * UF_CRM_1541572359657 - Признак размещения в рекламе 
 * UF_CRM_1544521987    - Статус объекта / Актив 
 */
function setAdvertisingStatus(&$arFields) : void {

  if($arFields['UF_CRM_1544521987'] == 0) {

    $UF = new CUserTypeManager;

    $fields = [
   
      'UF_CRM_1541572359657' => 0
  
    ];
  
    if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

    } 

  }
}

/**
 * UF_CRM_1540384944 - площадь помещения
 * UF_CRM_1541076330647 - площадь объекта клон для рассчётов
 */

function setSquareClone(&$arFields) : void {

  $select = ['UF_CRM_1540384944'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $select);

  $row = $crm_object->Fetch();

  $UF = new CUserTypeManager;

  $fields = [
 
    'UF_CRM_1541076330647' => $row['UF_CRM_1540384944']

  ];

  if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

  }

}

function setTiketSquareClone(&$arFields) : void {

  $select = ['UF_CRM_1547120946759'];

  $crm_object = \CCrmLead::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $select);

  $row = $crm_object->Fetch();

  $UF = new CUserTypeManager;

  $fields = [
 
    'UF_CRM_1547551577246' => $row['UF_CRM_1547120946759']

  ];

  if(!$UF->Update("CRM_LEAD", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

  }

}

/**
 * UF_CRM_1540202889 - Тип улицы 
 * UF_CRM_1540202900 - Название улицы 
 * UF_CRM_1540202908 - Номер дома
 * UF_CRM_1540202817 - Город 
 * UF_CRM_1541076330647 - Площадь объекта 
 * UF_CRM_1542955977 - Гео-данные 
 */
function setGeoData(&$arFields) : void {

  $dealCategory = \CCrmDeal::GetCategoryID($arFields['ID']);

  $required_fields = ['UF_CRM_1540202889','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540202817','UF_CRM_1541076330647'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $required_fields);

  while($row = $crm_object->Fetch()) {

    $arResult = [

      'STREET' => \Cian\CrmObject::street($row['UF_CRM_1540202900'], $row['UF_CRM_1540202889']),
      'HOUSE'  => $row['UF_CRM_1540202908'],
      'CITY'   => $row['UF_CRM_1540202817']

    ];

  }

  if(count(array_values($arResult)) >= 3) {

    $cian = \Cian\CianPriceMonitoring::instance();

    $geodata  = $cian->getGeocodedAdress( $arResult  );

    unset($geodata['SQUARE']);

    $fields = [
 
       'UF_CRM_1542955977' => json_encode($geodata)

    ];

    $UF = new CUserTypeManager;

    if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

    }

  }
}

function setPaybackAutotext(&$arFields) : void {

   $UF = new CUserTypeManager;

   $fields = [
 
     'UF_CRM_1545906357580' => 1

   ];

   if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

   }

}

function enumValue(int $value_id, string $code, ?string $entity = 'CRM_DEAL') : string {

  $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => $entity, "FIELD_NAME" => $code));
  $entity = $entityResult->Fetch();
  
  $enumResult = CUserFieldEnum::GetList(['ID' => "DESC"], ["ID" => $value_id,"USER_FIELD_ID" => $entity['ID']]);

  while($enum = $enumResult->GetNext()) {

      if($enum['ID'] == $value_id) {

         return $enum['VALUE'];

      }
    }

    return '';
}

function enumID(string $value, string $code, ?string $entity = 'CRM_DEAL') : int {

  $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => $entity, "FIELD_NAME" => $code));
  $entity = $entityResult->Fetch();

  $enumResult = CUserFieldEnum::GetList(['VALUE' => "DESC"], ["USER_FIELD_ID" => $entity['ID'], "VALUE" => $value]);
   
  while($enum = $enumResult->GetNext()) {

    if($enum['VALUE'] == $value) {
  
         return $enum['ID'];
  
      }
    }

   return -1;
 }

 function iblockValue(int $id) : string {

   return \CIBlockElement::GetByID($id)->Fetch()['NAME'];

 }


 function getEnumList(string $fieldName, string $entity = 'CRM_DEAL') {

  $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => $entity, "FIELD_NAME" => $fieldName));
  $entity = $entityResult->Fetch();

  return \CUserTypeEnum::GetList($entity);

 }

 function getEnumEntity(string $fieldName, string $entity = 'CRM_DEAL') {

  $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => $entity, "FIELD_NAME" => $fieldName));
  $entity = $entityResult->Fetch();

  return \CUserTypeEntity::GetByID($entity['ID']);

 }

 if (!function_exists("array_key_last")) {

  function array_key_last($array) {
      if (!is_array($array) || empty($array)) {
          return NULL;
      }
      
      return array_keys($array)[count($array)-1];
  }
  
}

if (!function_exists('mb_ucfirst'))
{
    function mb_ucfirst($value)
    {
        return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1);
    }
}




