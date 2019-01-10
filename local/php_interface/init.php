<?

use \Bitrix\Main\EventManager,
    \Raiting\RaitingFactory;
    
const GENERAL_BROKER = 15;

$event = EventManager::getInstance();

$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setGeoData');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setSquareClone');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setRaiting');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setAdvertisingStatus');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setRealPrice');

require_once $_SERVER['DOCUMENT_ROOT']."/local/Cian/CianPriceMonitoring.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Cian/CrmObject.php";
require_once $_SERVER['DOCUMENT_ROOT']."/local/Raiting/RaitingFactory.php";


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
 * UF_CRM_1540532735882 - Собственник платит (в %)
 * UF_CRM_1542089326915 - кол-во конкурентов
 * UF_CRM_1541072013901 - Стоимость объекта
 * UF_CRM_1540456417    - Стоимость аренды за все помещение в месяц
 * UF_CRM_1544446024    - Рейтинг
 */
function setRaiting(&$arFields) : void {

  $select = ['UF_CRM_1540456417','UF_CRM_1540532735882','UF_CRM_1542089326915','UF_CRM_1541072013901'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $select);

  $object = $crm_object->Fetch();

  $raiting = RaitingFactory::create($object, \CCrmDeal::GetCategoryID($arFields['ID']));

  $UF = new CUserTypeManager;

  $fields = [
 
    'UF_CRM_1544446024' => $raiting->summ()

  ];

  #file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt',  $raiting->summ().' '.print_r(  \CCrmDeal::GetCategoryID($arFields['ID'])  ,1).date("d/m/Y H:i:s")."\r\n");

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

   // file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

  }
}


