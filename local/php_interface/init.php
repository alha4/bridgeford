<?

if(!$_SERVER["DOCUMENT_ROOT"]) {

  $_SERVER["DOCUMENT_ROOT"] = '/home/bitrix/ext_www/crm.bridgeford.ru';

}

use \Bitrix\Main\EventManager,
    \Raiting\RaitingFactory,
    \Bitrix\Main\Web\HttpClient,
    \Bitrix\Main\UserFieldTable,
    \Bitrix\Main\Loader,
    \Bitrix\Main\ORM\Query\Result,
    \Bitrix\Main\ORM\Query\Query,
    \Bitrix\Crm\Category\DealCategory,
    \Bitrix\Crm\DealTable,
    \Bitrix\Crm\LeadTable;

const PSR_CLASS_PATH = '/local/Psr/Log';
const CIAN_ROOT_CLASS_PATH = '/local/Cian';
const STAT_ROOT_CLASS_PATH = '/local/Stat';
const SEARCH_ROOT_CLASS_PATH = '/local/Search';
const XML_PARSER_PATH = '/local/XML/Parser';
const XML_CLASS_PATH = '/local/XML';
const SEMANTIC_CLASS_PATH = '/local/XML/Semantic';
const LOG_PATH = '/local/Cian/log.txt';
const REQUEST_LOG = 'N';
const GENERAL_BROKER = 15;
const FILTER_PRECENT = 10;
const YANDEX_API_KEY = '5e926399-e46a-4846-a809-c3d370aa399e';
const GOOGLE_API_KEY = 'AIzaSyAUi5d1Fe5Vl02YJE-cckShpqtqaVH4Jzk';
const DEBUG_AUTOTEXT = 'Y';
const AUTOTEXT_API_URL = 'http://92.53.97.50/autotext.php';
const NOT_ACTUAL_VALUE = 'не актуально';

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
     '\Cian\EventInterface'      => CIAN_ROOT_CLASS_PATH.'/EventInterface.php',
     '\Cian\CianHelper'          => CIAN_ROOT_CLASS_PATH.'/CianHelper.php',
     '\Cian\CianPriceMonitoring' => CIAN_ROOT_CLASS_PATH.'/CianPriceMonitoring.php',
     '\Cian\CrmObject'           => CIAN_ROOT_CLASS_PATH.'/CrmObject.php',
     '\Cian\Logger'              => CIAN_ROOT_CLASS_PATH.'/Logger.php',
     '\Raiting\RaitingFactory'   => "/local/Raiting/RaitingFactory.php",
     '\Stat\CompetitorEvent'     => STAT_ROOT_CLASS_PATH.'/CompetitorEvent.php',
     '\Search\SimilarObject'     => SEARCH_ROOT_CLASS_PATH."/SimilarObject.php",
     '\Search\SimilarTicket'     => SEARCH_ROOT_CLASS_PATH."/SimilarTicket.php",
     '\XML\Helpers\Description'  => XML_CLASS_PATH.'/Helpers/Description.php',
     '\Semantic\SemanticFactory' => SEMANTIC_CLASS_PATH.'/SemanticFactory.php',
     '\XML\Heplers\WhaterMark'   => XML_CLASS_PATH.'/Helpers/WhaterMark.php',
     '\XML\ExportBase'           => XML_CLASS_PATH.'/ExportBase.php',
     '\XML\Helpers\Speciality'   => XML_CLASS_PATH.'/Helpers/Speciality.php',
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
$event->addEventHandler('crm', 'OnAfterCrmLeadUpdate', 'tiketSave');
$event->addEventHandler('crm', 'OnAfterCrmLeadAdd', 'tiketSave');
$event->addEventHandler('crm', 'OnBeforeCrmLeadAdd', 'ObligatoryFieldFill');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setRaiting');
//$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setAdvertisingStatus');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setRealPrice');
$event->addEventHandler('crm', 'OnAfterCrmDealAdd', 'setRealPrice');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setWatermark');
$event->addEventHandler('crm', 'OnAfterCrmDealAdd', 'setWatermark');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setAutotext');
$event->addEventHandler('crm', 'OnAfterCrmDealAdd', 'setAutotext');
$event->addEventHandler('crm', 'OnBeforeCrmDealUpdate', 'setMapLocation');
$event->addEventHandler('crm', 'OnAfterCrmDealAdd', 'setActuality');
$event->addEventHandler('crm', 'OnAfterCrmDealAdd', 'setLocation');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'setLocation');
$event->addEventHandler('crm', 'OnBeforeCrmDealUpdate', 'setSemantic');
$event->addEventHandler('crm', 'OnAfterCrmDealAdd', 'setObjectList');
$event->addEventHandler('crm', 'OnAfterCrmDealUpdate', 'updateObjectList');
$event->addEventHandler('crm', 'OnBeforeCrmDealDelete', 'deleteObjectList');
$event->addEventHandler('crm', 'OnAfterCrmLeadAdd', 'setTicketList');
$event->addEventHandler('crm', 'OnAfterCrmLeadUpdate', 'updateTicketList');
$event->addEventHandler('crm', 'OnBeforeCrmLeadDelete', 'deleteTicketList');
$event->addEventHandler('crm', 'OnBeforeCrmDealUpdate', "checkOwner");

function checkOwner($arFields) {
    
  if(!$arFields['UF_CRM_1540895685'] && !$arFields['UF_CRM_1558086250']) {
  
    $arFields['RESULT_MESSAGE'] = "error";

    return false;

  }

  return $arFields;

}

const OBJECT_LIST_ID  = 31;

const TICKET_LIST_ID  = 32;


function setTicketList(&$arFields) {

$logger = \Log\Logger::instance();
$logger->setPath("/local/logs/binder.txt");

$select = ['ID','TITLE','UF_CRM_1545389896','UF_CRM_1545389958','UF_CRM_1566919640','UF_CRM_1566919661'];

$ID = $arFields['ID'];

$crm_object = LeadTable::query()
->where('ID', '=' , $ID)
->where(Query::filter()
 ->logic('or')
 ->where('UF_CRM_1566919640', '>', 0)
 ->where('UF_CRM_1566919661', '>', 0) 
)
->setSelect($select)
->exec()->fetch();

if($crm_object['UF_CRM_1566919640'] || $crm_object['UF_CRM_1566919661']) {
  
  $list = new \CIBlockElement(false);

  $arObject = [

  'IBLOCK_ID' => \TICKET_LIST_ID,
  'NAME' => $crm_object['TITLE'],

    'PROPERTY_VALUES' => [

      'KONTAKT'     => $crm_object['UF_CRM_1566919640'],
      'KOMPANIYA'   => $crm_object['UF_CRM_1566919661'],
      'ID_ZAYAVKI'  => sprintf("<a href='/crm/lead/details/%d/'>%d</a>",$ID, $ID),
      'ID_ZAYAVKI_TSIFRA' => $ID,
      'TIP_ZAYAVKI' => enumValue($crm_object['UF_CRM_1545389896'],$ID,'UF_CRM_1545389896','CRM_LEAD'),
      'TIP_OBEKTA'  => enumValue($crm_object['UF_CRM_1545389958'],$ID,'UF_CRM_1545389958','CRM_LEAD'),

   ]
  ];
  
  $logger->info($crm_object);


  if(!$list->Add($arObject)) {

    $logger->error([$ID, $list->LAST_ERROR,  $arObject]);

  }
 }
}


function updateTicketList(&$arFields) {

$logger = \Log\Logger::instance();
$logger->setPath("/local/logs/binder_update.txt");

$select = ['ID','TITLE','UF_CRM_1545389896','UF_CRM_1545389958','UF_CRM_1566919640','UF_CRM_1566919661'];

$ID = $arFields['ID'];

$crm_object = LeadTable::query()
->where('ID', '=' , $ID)
->where(Query::filter()
 ->logic('or')
 ->where('UF_CRM_1566919640', '>', 0)
 ->where('UF_CRM_1566919661', '>', 0) 
)
->setSelect($select)
->exec()->fetch();

if($crm_object['UF_CRM_1566919640'] || $crm_object['UF_CRM_1566919661']) {
  
  $list = new \CIBlockElement(false);

  $arObject = [

  'IBLOCK_ID' => \TICKET_LIST_ID,
  'NAME' => $crm_object['TITLE'],

    'PROPERTY_VALUES' => [

      'KONTAKT'     => $crm_object['UF_CRM_1566919640'],
      'KOMPANIYA'   => $crm_object['UF_CRM_1566919661'],
      'ID_ZAYAVKI'  => sprintf("<a href='/crm/lead/details/%d/'>%d</a>",$ID, $ID),
      'ID_ZAYAVKI_TSIFRA' => $ID,
      'TIP_ZAYAVKI' => enumValue($crm_object['UF_CRM_1545389896'],$ID,'UF_CRM_1545389896','CRM_LEAD'),
      'TIP_OBEKTA'  => enumValue($crm_object['UF_CRM_1545389958'],$ID,'UF_CRM_1545389958','CRM_LEAD'),

   ]
  ];
  
    $logger->info($arObject);

    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ID_ZAYAVKI_TSIFRA");
    $arFilter = Array("IBLOCK_ID"=>\TICKET_LIST_ID, "PROPERTY_ID_ZAYAVKI_TSIFRA"=>$ID); //
    $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), $arFilter, false, false, $arSelect);
    while($ar_fields = $res->GetNext())
    {
      $logger->info($ar_fields['ID']);

      if(!$list->Update($ar_fields['ID'],$arObject)) {

        $logger->error([$ID, $list->LAST_ERROR,  $arObject]);

      }

    }

 }
}


function deleteTicketList($ID) {

$logger = \Log\Logger::instance();
$logger->setPath("/local/logs/binder_delete.txt");

$select = ['ID','TITLE','UF_CRM_1545389896','UF_CRM_1545389958','UF_CRM_1566919640','UF_CRM_1566919661'];

$logger->info($ID);

$crm_object = LeadTable::query()
->where('ID', '=' , $ID)
->where(Query::filter()
 ->logic('or')
 ->where('UF_CRM_1566919640', '>', 0)
 ->where('UF_CRM_1566919661', '>', 0) 
)
->setSelect($select)
->exec()->fetch();

if($crm_object['UF_CRM_1566919640'] || $crm_object['UF_CRM_1566919661']) {
  
  $list = new \CIBlockElement(false);
  $deleted = "Заявка удалена";

  $arObject = [

  'IBLOCK_ID' => \TICKET_LIST_ID,
  'NAME' => $crm_object['TITLE'],

    'PROPERTY_VALUES' => [

      'KONTAKT'     => $crm_object['UF_CRM_1566919640'],
      'KOMPANIYA'   => $crm_object['UF_CRM_1566919661'],
      'ID_ZAYAVKI'  => sprintf("<a href='/crm/lead/details/%d/'>%d</a>",$ID, $ID),
      'ID_ZAYAVKI_TSIFRA' => $ID,
      'TIP_ZAYAVKI' => enumValue($crm_object['UF_CRM_1545389896'],$ID,'UF_CRM_1545389896','CRM_LEAD'),
      'TIP_OBEKTA'  => enumValue($crm_object['UF_CRM_1545389958'],$ID,'UF_CRM_1545389958','CRM_LEAD'),
      'UDALENA' => $deleted
   ]
  ];
  
    $logger->info($arObject);

    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ID_ZAYAVKI_TSIFRA");
    $arFilter = Array("IBLOCK_ID"=>\TICKET_LIST_ID, "PROPERTY_ID_ZAYAVKI_TSIFRA"=>$ID); //
    $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), $arFilter, false, false, $arSelect);
    while($ar_fields = $res->GetNext())
    {
      $logger->info($ar_fields['ID']);

      if(!$list->Update($ar_fields['ID'],$arObject)) {

        $logger->error([$ID, $list->LAST_ERROR,  $arObject]);

      }

    }

 }
}


function setObjectList(&$arFields) {

  $logger = \Log\Logger::instance();
  $logger->setPath("/local/logs/binder.txt");

  $select = ['ID','TITLE','CATEGORY_ID','UF_CRM_1540202908','UF_CRM_1541072013901','UF_CRM_1540202889',
            'UF_CRM_1540895685','UF_CRM_1558086250','UF_CRM_1541076330647','UF_CRM_1540202900',
            'UF_CRM_1540456417','UF_CRM_1540384944'];

  $ID = $arFields['ID'];

  $crm_object = DealTable::query()
    ->where('ID', '=' , $ID)
    ->where(Query::filter()
    ->logic('or')
     ->where('UF_CRM_1540895685', '>', 0)
     ->where('UF_CRM_1558086250', '>', 0) 
    )
    ->setSelect($select)
    ->exec()->fetch();

  if($crm_object['UF_CRM_1540895685'] || $crm_object['UF_CRM_1558086250']) {
    
    $list = new \CIBlockElement(false);

    $arObject = [

    'IBLOCK_ID' => \OBJECT_LIST_ID,
    'NAME' => $crm_object['TITLE'],

    'PROPERTY_VALUES' => [

       'KONTAKT'    => $crm_object['UF_CRM_1558086250'],
       'KOMPANIYA'  => $crm_object['UF_CRM_1540895685'],
       'ID_OBEKTA'  => sprintf("<a href='/crm/deal/details/%d/'>%d</a>",$ID, $ID),
       'ID_OBEKTA_TSIFRA' => $ID,
       'TIP_OBEKTA' => DealCategory::getName($crm_object['CATEGORY_ID']),
       'NOMER_DOMA' => $crm_object['UF_CRM_1540202908'],
       'PLOSHCHAD'  => $crm_object['UF_CRM_1540384944'],
       'ULITSA'     => $crm_object['UF_CRM_1540202900'],
       'STOIMOST'   => SaleFormatCurrency($crm_object['CATEGORY_ID'] == 0 ? $crm_object['UF_CRM_1540456417'] : $crm_object['UF_CRM_1541072013901'],'RUB'),
       'TIP_ULITSY' => enumValue($crm_object['UF_CRM_1540202889'], $ID, 'UF_CRM_1540202889')

     ]
    ];
    
    $logger->info($crm_object);


    if(!$list->Add( $arObject)) {

      $logger->error([$ID, $list->LAST_ERROR,  $arObject]);

    }

  }

}


function updateObjectList(&$arFields) {

  $logger = \Log\Logger::instance();
  $logger->setPath("/local/logs/binder_update.txt");

  $select = ['ID','TITLE','CATEGORY_ID','UF_CRM_1540202908','UF_CRM_1541072013901','UF_CRM_1540202889',
            'UF_CRM_1540895685','UF_CRM_1558086250','UF_CRM_1541076330647','UF_CRM_1540202900',
            'UF_CRM_1540456417','UF_CRM_1540384944'];

  $ID = $arFields['ID'];

  $crm_object = DealTable::query()
    ->where('ID', '=' , $ID)
    ->where(Query::filter()
    ->logic('or')
     ->where('UF_CRM_1540895685', '>', 0)
     ->where('UF_CRM_1558086250', '>', 0) 
    )
    ->setSelect($select)
    ->exec()->fetch();

  if($crm_object['UF_CRM_1540895685'] || $crm_object['UF_CRM_1558086250']) {
    
    $list = new \CIBlockElement(false);

    $arObject = [

    'IBLOCK_ID' => \OBJECT_LIST_ID,
    'NAME' => $crm_object['TITLE'],

    'PROPERTY_VALUES' => [

       'KONTAKT'    => $crm_object['UF_CRM_1558086250'],
       'KOMPANIYA'  => $crm_object['UF_CRM_1540895685'],
       'ID_OBEKTA'  => sprintf("<a href='/crm/deal/details/%d/'>%d</a>",$ID, $ID),
       'ID_OBEKTA_TSIFRA' => $ID,
       'TIP_OBEKTA' => DealCategory::getName($crm_object['CATEGORY_ID']),
       'NOMER_DOMA' => $crm_object['UF_CRM_1540202908'],
       'PLOSHCHAD'  => $crm_object['UF_CRM_1540384944'],
       'ULITSA'     => $crm_object['UF_CRM_1540202900'],
       'STOIMOST'   => SaleFormatCurrency($crm_object['CATEGORY_ID'] == 0 ? $crm_object['UF_CRM_1540456417'] : $crm_object['UF_CRM_1541072013901'],'RUB'),
       'TIP_ULITSY' => enumValue($crm_object['UF_CRM_1540202889'], $ID, 'UF_CRM_1540202889')

     ]
    ];
    
    $logger->info($arObject);

    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ID_OBEKTA_TSIFRA");
    $arFilter = Array("IBLOCK_ID"=>\OBJECT_LIST_ID, "PROPERTY_ID_OBEKTA_TSIFRA"=>$ID); //
    $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), $arFilter, false, false, $arSelect);
    while($ar_fields = $res->GetNext())
    {
      $logger->info($ar_fields['ID']);

      if(!$list->Update($ar_fields['ID'],$arObject)) {

        $logger->error([$ID, $list->LAST_ERROR,  $arObject]);

      }

    }

  }

}


function deleteObjectList($ID) {

  $logger = \Log\Logger::instance();
  $logger->setPath("/local/logs/binder_delete.txt");

  $select = ['ID','TITLE','CATEGORY_ID','UF_CRM_1540202908','UF_CRM_1541072013901','UF_CRM_1540202889',
            'UF_CRM_1540895685','UF_CRM_1558086250','UF_CRM_1541076330647','UF_CRM_1540202900',
            'UF_CRM_1540456417','UF_CRM_1540384944'];

  $logger->info($ID);

  $crm_object = DealTable::query()
    ->where('ID', '=' , $ID)
    ->where(Query::filter()
    ->logic('or')
     ->where('UF_CRM_1540895685', '>', 0)
     ->where('UF_CRM_1558086250', '>', 0) 
    )
    ->setSelect($select)
    ->exec()->fetch();

  if($crm_object['UF_CRM_1540895685'] || $crm_object['UF_CRM_1558086250']) {
    
    $list = new \CIBlockElement(false);
    $deleted = "Объект удален";

    $arObject = [

    'IBLOCK_ID' => \OBJECT_LIST_ID,
    'NAME' => $crm_object['TITLE'],

    'PROPERTY_VALUES' => [

       'KONTAKT'    => $crm_object['UF_CRM_1558086250'],
       'KOMPANIYA'  => $crm_object['UF_CRM_1540895685'],
       'ID_OBEKTA'  => sprintf("<a href='/crm/deal/details/%d/'>%d</a>",$ID, $ID),
       'ID_OBEKTA_TSIFRA' => $ID,
       'TIP_OBEKTA' => DealCategory::getName($crm_object['CATEGORY_ID']),
       'NOMER_DOMA' => $crm_object['UF_CRM_1540202908'],
       'PLOSHCHAD'  => $crm_object['UF_CRM_1540384944'],
       'ULITSA'     => $crm_object['UF_CRM_1540202900'],
       'STOIMOST'   => SaleFormatCurrency($crm_object['CATEGORY_ID'] == 0 ? $crm_object['UF_CRM_1540456417'] : $crm_object['UF_CRM_1541072013901'],'RUB'),
       'TIP_ULITSY' => enumValue($crm_object['UF_CRM_1540202889'], $ID, 'UF_CRM_1540202889'),
       'UDALEN' => $deleted

     ]
    ];

    $logger->info($arObject);

    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ID_OBEKTA_TSIFRA");
    $arFilter = Array("IBLOCK_ID"=>\OBJECT_LIST_ID, "PROPERTY_ID_OBEKTA_TSIFRA"=>$ID); //
    $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), $arFilter, false, false, $arSelect);
    while($ar_fields = $res->GetNext())
    {
      $logger->info($ar_fields['ID']);

      if(!$list->Update($ar_fields['ID'],$arObject)) {

        $logger->error([$ID, $list->LAST_ERROR,  $arObject]);

      }

    }

  }

}



/**
 * UF_CRM_1568100006 - старое направление
 * CATEGORY_ID - новое направление
 *
*/
function setSemantic(&$arFields) {

  $semanticMap = [

    '0' => 'UF_CRM_1540974006',
    '1' => 'UF_CRM_1544172451',
    '2' => 'UF_CRM_1544172560'
 
  ];

  $deal = \Bitrix\Crm\DealTable::getList(['filter' => ['ID' => $arFields['ID']] ,'select' => 
  ['CATEGORY_ID','TITLE','UF_CRM_1540974006','UF_CRM_1544172451','UF_CRM_1544172560','UF_CRM_1568100006']])->fetch();

  if($deal['CATEGORY_ID'] != $deal['UF_CRM_1568100006']) {

    $uf = new CUserTypeManager();

    $fields = [
  
     'UF_CRM_1568100006' => $deal['CATEGORY_ID']
  
    ];

    $uf->Update('CRM_DEAL', $arFields['ID'], $fields);

    $semanticNew = $semanticMap[$deal['CATEGORY_ID']];

    $semanticLast = $semanticMap[$deal['UF_CRM_1568100006']];
  
    $arSemantic = [];

    foreach($deal[$semanticLast] as $valueId) {
    
      $enumId = enumID(enumValue($valueId, $semanticLast), $semanticNew);
    
      if($enumId != -1) {

          $arSemantic[] = $enumId;
       
      }

    }

    if(count($arFields[ $semanticNew ]) <= 0 && $arSemantic) {

      $arFields[ $semanticNew ] = $arSemantic;

    }

   /* $logger = \Log\Logger::instance();
    $logger->setPath("/local/logs/semantic.txt");

    $logger->info([$semanticNew,$semanticLast, $arSemantic, $deal[$semanticLast],  $arFields] );*/
   
   /* $logger->info([$arFields, $deal]);
    $logger->info([$semanticNew, $semanticLast]);*/

  }

}

function ObligatoryFieldFill(&$arFields) {

 $logger = \Log\Logger::instance();
 $logger->setPath("/local/logs/leadsObligatryFill.txt");

 if ($arFields['UF_CRM_1545389896'] == '360') {


  $userTypeManager = new CUserTypeManager();

  $arLeadFields['UF_CRM_1545390144'] = '526';
  $arLeadFields['UF_CRM_1547551210'] = '1'; //стоимость
  $arLeadFields['UF_CRM_1547120946759'] = '1'; //площадь

  $userTypeManager->Update('CRM_LEAD', $arFields['ID'], $arLeadFields);
  $logger->info([$arLeadFields]); 
    
  }

}


function tiketSave(&$arFields) {

 $logger = \Log\Logger::instance();
 $logger->setPath("/local/logs/leads.txt");

 if(array_key_exists('UF_CRM_1565872302', $arFields)) {

   $deal_id = (int)str_replace('D_','', $arFields['UF_CRM_1565872302']);

 } else {

   $lead = \Bitrix\Crm\LeadTable::getList(['filter' => ['ID' => $arFields['ID']] ,'select' => ['UF_CRM_1565872302']])->fetch();

   $deal_id = (int)str_replace('D_','',$lead['UF_CRM_1565872302']);

 }

 if($deal_id > 0) {

  $commonFields = ['UF_CRM_1540384944','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540202817','UF_CRM_1540202667','UF_CRM_1540203111','UF_CRM_1543406565','UF_CRM_1540203015', 'UF_CRM_1540202889',
  'UF_CRM_1545649289833','UF_CRM_1541072013901','UF_CRM_1540456417','UF_CRM_1540554743072','UF_CRM_1541072151310','UF_CRM_1541055727999','UF_CRM_1544431330','UF_CRM_1541055405','UF_CRM_1541055672','UF_CRM_1541055237379','UF_CRM_1541055274251','UF_CRM_1566542004']; 
  
  $mapFields = [

     '362' => array_merge($commonFields, []),
     '363' => array_merge($commonFields, []),
     '364' => array_merge($commonFields, [])

  ];

  $listTypeMap = [

    'UF_CRM_1540202667' => [
      '26' => 365,
      '27' => 512,
      '28' => 366   
    ],

  
/** округ заявки (объект) - 495 ЦАО (58), 496 САО (59), 497 СВАО (60), 498 ВАО (61), 499 ЮВАО (62), 500 ЮАО (63), 501 ЮЗАО (64), 502 ЗАО (65), 503 СЗАО (66), 504 Зеленоградский АО (67), 505 Новомосковский АО (68), 506 Троицкий АО (69), не актуально (289)
*
*    'UF_CRM_1540203111' => [       
*
*      '58' => 495,
*      '59' => 496,
*      '60' => 497,
*      '61' => 498,
*      '62' => 499,
*      '63' => 500,
*      '64' => 501,
*      '65' => 502,
*      '66' => 503,
*      '67' => 504,
*      '68' => 505,
*      '69' => 506
*    ]
*/


  ];


  $select = $mapFields[ $arFields['UF_CRM_1545389958'] ];

  $filter = ['ID' => $deal_id];

  $object = \CCrmDeal::GetList(['ID'=>"DESC"], $filter, $select)->Fetch();

  // UF_CRM_1541055672 - второй тип арендатора а объектах, UF_CRM_1547632842691 - второй тип в заявках
  // UF_CRM_1541055237379 - название арендатора стандартное в объектах, UF_CRM_1566804181754 - название стандартное в заявках
  // UF_CRM_1541055274251 - иное название арендатора в объектах, UF_CRM_1547632526938 - иное название в заявках
  // UF_CRM_1540203015 - расстояние до метро объекты, UF_CRM_1547117427 - расстояние до метро заявки

  $street_type = enumValue($object['UF_CRM_1540202889'],'UF_CRM_1540202889');

  $rastmetro_value = enumValue($object['UF_CRM_1540203015'],'UF_CRM_1540203015'); // находим значение поля расстояние до метро в объектах
  $rastmetro = enumID($rastmetro_value, 'UF_CRM_1547117427', 'CRM_LEAD');  // находим id по значению расстояние до метро в заявках

  $firsttip_value = enumValue($object['UF_CRM_1541055405'],'UF_CRM_1541055405'); // находим значение поля 1 тип арендатора в объектах
  $firsttip = enumID($firsttip_value, 'UF_CRM_1547632637130', 'CRM_LEAD');  // находим id по значению 1 тип арендатора в заявках

  $secondtip_value = enumValue($object['UF_CRM_1541055672'],'UF_CRM_1541055672'); // находим значение поля 2 тип арендатора в объектах
  $secondtip = enumID($secondtip_value, 'UF_CRM_1547632842691', 'CRM_LEAD');  // находим id по значению 2 тип арендатора в заявках

  $arendstandart_value = enumValue($object['UF_CRM_1541055237379'],'UF_CRM_1541055237379'); // находим значение поля Стандартное название арендатора в объектах
  $arendstandart = enumID($arendstandart_value, 'UF_CRM_1566804181754', 'CRM_LEAD');  // находим id по значению Стандартное название арендатора в заявках

  $area_value = enumValue($object['UF_CRM_1540203111'],'UF_CRM_1540203111'); // находим значение Округ в объектах
  $area_multi = enumID($area_value, 'UF_CRM_1565850691', 'CRM_LEAD');  // находим id по значению Округ в заявках в поле Округ множественное
  $area = enumID($area_value, 'UF_CRM_1566212549228', 'CRM_LEAD');  // находим id по значению Округ в заявках

  $userTypeManager = new CUserTypeManager();

  $arLeadFields['UF_CRM_1567070139034'] =  $object['UF_CRM_1540202900'] . " " . $street_type . " " . $object['UF_CRM_1540202908'];  //адрес для заявок по объекту

  $arLeadFields['UF_CRM_1545390144'] = $listTypeMap['UF_CRM_1540202667'][$object['UF_CRM_1540202667']];
  $arLeadFields['UF_CRM_1565850328'] = [$object['UF_CRM_1543406565']];
  $arLeadFields['UF_CRM_1547120946759'] = $object['UF_CRM_1540384944'];

  $arLeadFields['UF_CRM_1547551210'] = $object['UF_CRM_1540456417'] ? : $object['UF_CRM_1541072013901']; // стоимость заявки из "Стоимость аренды за все помещение в месяц" или "Стоимость объекта"  

  $arLeadFields['UF_CRM_1565850691'] = [$area_multi]; // Округ множественное
  $arLeadFields['UF_CRM_1566212549228'] = $area; // Округ 

  $logger->info([$area_multi, $area]); 

  $arLeadFields['UF_CRM_1547218667182'] = $object['UF_CRM_1540554743072']; // стоимость за 1 кв. м в год
  
  $arLeadFields['UF_CRM_1565853455'] = $object['UF_CRM_1541072151310']; // стоимость за 1 кв. м 

  $arLeadFields['UF_CRM_1547632526938'] = $object['UF_CRM_1541055237379']; // арендатор добавляется id элемента списка, а не значение

  $arLeadFields['UF_CRM_1547629103665'] = $object['UF_CRM_1541055727999']; // мап

  $arLeadFields['UF_CRM_1547628348754'] = $object['UF_CRM_1566542004']; // окупаемость  
  $arLeadFields['UF_CRM_1547632637130'] = $firsttip; // 1 тип  
  $arLeadFields['UF_CRM_1547632842691'] = $secondtip; // 2 тип
  $arLeadFields['UF_CRM_1566804181754'] = $arendstandart; // стандартное название арендатора
  $arLeadFields['UF_CRM_1547632526938'] = $object['UF_CRM_1541055274251']; // иное название арендатора 

  $arLeadFields['UF_CRM_1547117427'] = $rastmetro; // расстояние до метро

  $userTypeManager->Update('CRM_LEAD', $arFields['ID'], $arLeadFields);

  $logger->info([$arLeadFields]); 


 } else {

  $logger->info([$arFields,$deal_id]); 

 }
}

function setActuality(&$arFields)  {

  global $USER;

  $uf = new CUserTypeManager();

  $price = $arFields['UF_CRM_1540456417'] ? : $arFields['UF_CRM_1541072013901'];

  $fields = [

   'UF_CRM_1540895373' => $USER->GetID(),
   'UF_CRM_1544524903217' => date("d.m.Y"),
   'UF_CRM_1544528494' => 329,
   'UF_CRM_1545199624' => 357,
   'UF_CRM_1545649289833' => "$price|RUB"
  ];

  $uf->Update('CRM_DEAL', $arFields['ID'], $fields);


}


function setLocation(&$arFields)  {

//$logger = \Log\Logger::instance();
//$logger->setPath("/local/logs/location_update.txt");




  $select = ['ID','UF_CRM_1540202889','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540202817','UF_CRM_1540202667'];

  $filter = ['ID' => $arFields['ID']];

  $object = \CCrmDeal::GetList(['ID'=>"DESC"], $filter, $select)->Fetch();

//  $logger->info($object); 

$ID_loc = $arFields['ID'];
$typestreet_loc = $object['UF_CRM_1540202889'];
$street_loc = $object['UF_CRM_1540202900'];
$house_loc = $object['UF_CRM_1540202908'];
$city_loc = $object['UF_CRM_1540202817'];
$region_loc = $object['UF_CRM_1540202667'];

  $adress = str_replace([" ",","],"+",sprintf("Россия+%s+%s+%s+%s", getCity($city_loc), enumValue($typestreet_loc,'UF_CRM_1540202889'), $street_loc, $house_loc));


  $http = new HttpClient();

  $result = json_decode($http->get(sprintf("https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s&language=ru", urlencode($adress), GOOGLE_API_KEY)) ,1);


  if($result['results'][0]['geometry']['location']) {

    $loc2 = $result['results'][0]['geometry']['location'];

    $userField = new CUserTypeManager();

    $arLocation = [
      'UF_CRM_1565253760' => sprintf("%s,%s", $loc2['lat'],  $loc2['lng'])
    ];

    $userField->Update('CRM_DEAL',$ID_loc, $arLocation);

  }

}


function setAutotext(&$arFields)  {

  $select = ['ID','UF_CRM_1540202667','UF_CRM_1540202889','UF_CRM_1540202900','UF_CRM_1540202908','UF_CRM_1540203015',
           'UF_CRM_1543406565','UF_CRM_1540203111','UF_CRM_1540371261836','UF_CRM_1540371455','UF_CRM_1540371585',
           'UF_CRM_1540371563','UF_CRM_1556020811397','UF_CRM_1540384807664','UF_CRM_1540384944','UF_CRM_1541076330647',
           'UF_CRM_1540384963','UF_CRM_1540385040','UF_CRM_1540385060','UF_CRM_1540385112','UF_CRM_1540974006','UF_CRM_1544172451',
           'UF_CRM_1541055274251','UF_CRM_1541055405','UF_CRM_1541055237379','UF_CRM_1540202817',
           'UF_CRM_1544172560','UF_CRM_1540456417','UF_CRM_1541072013901','UF_CRM_1540392018', 'UF_CRM_1540397421', 'UF_CRM_1541055727999'];

  $keys = array_keys($arFields);
  $id_index = array_search('ID',$keys);
  $keys[$id_index] = null;
  unset($keys[$id_index]);

  if(count(array_intersect($select, $keys)) > 0) {

  $filter = ['CHECK_PERMISSIONS' => "N", "ID"=> $arFields['ID']];

  $object = \CCrmDeal::GetList(['ID'=>"DESC"], $filter, $select)->Fetch();

  $jsonData = [];

  $rawData = [];

  $ID = $object['ID'];

  foreach($object as $code=>&$value) {

    if($code == 'UF_CRM_1540456417' && \CCrmDeal::GetCategoryID($ID) == 2) {

      continue;

    }

    if($code == 'UF_CRM_1540456417' && \CCrmDeal::GetCategoryID($ID) == 1) {

      continue;

    }

    if($code != 'ID') {

      $userField = UserFieldTable::getList(array(

        'filter' => ['FIELD_NAME'=> $code],
        'select' => ['USER_TYPE_ID','MULTIPLE']
  
      ))->fetch();

      switch($userField['USER_TYPE_ID']) {

        case 'enumeration' :

        if($userField['MULTIPLE'] != 'Y' && !is_array($value)) {
  
          if(!is_null($value))

             $value = enumValue($value, $code);

        } else {

           if(in_array($code, \SEMANTIC_CODE)) {

              $code = \SEMANTIC_CODE[\CCrmDeal::GetCategoryID($object['ID'])];

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

      $rawData['ID'] = $ID;
      $rawData['CATEGORY_ID'] = OBJECT_TYPE[\CCrmDeal::GetCategoryID($ID)];
      $rawData[$code] = $value;
      
    }
  }

  $jsonData[] = $rawData;

  $http = new HttpClient();

  $http->setHeader("Content-Type","application/json");

  $responce = $http->post(\AUTOTEXT_API_URL, json_encode($jsonData,JSON_UNESCAPED_UNICODE));

  if($http->getStatus() == 200) {

     $autotext = json_decode($responce,1)[0];

     $userType = new CUserTypeManager();

     $arAutotext = [
  
       'UF_CRM_1556017573094' => $autotext['text']

     ];

     $userType->Update('CRM_DEAL', $ID, $arAutotext);

     $logger = \Log\Logger::instance();
     $logger->setPath('/local/logs/autotext_log.txt');
     $logger->info( ['id'=> $ID, 'request' => $jsonData, 'response' => $autotext['text']] );

  }

 }

}

function setWatermark(&$arFields) {

 if(array_key_exists('UF_CRM_1540532330',$arFields) || array_key_exists('UF_CRM_1540532459',$arFields)) {

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], 
  ["ID","UF_CRM_1540532330","UF_CRM_1540532459", "ORIGIN_ID","ASSIGNED_BY_ID"])->Fetch();

  $watermark = new \XML\Heplers\WhaterMark();
  $watermark->setPath('/upload/br2.png');

  $logger = \Log\Logger::instance();
  $logger->setPath('/local/logs/watermark_log.txt');

  $watermark->setLogger($logger);

  $photos = $crm_object['UF_CRM_1540532330'];

  $arPhotoWatermark = [];

  foreach($photos as $fileID) {

    $arPhotoWatermark[] = \CFile::MakeFileArray($watermark->createWhaterMark($fileID, (int)$crm_object['ID'], (int)$crm_object['ORIGIN_ID'],(int)$crm_object['ASSIGNED_BY_ID']));

  }

  $watermark->setPath('/upload/br.png');

  $explications = $crm_object['UF_CRM_1540532459'];

  $arExplWatermark = [];

  foreach($explications as $fileID) {

    $arExplWatermark[] = \CFile::MakeFileArray($watermark->createWhaterMark($fileID, (int)$crm_object['ID'], (int)$crm_object['ORIGIN_ID'],(int)$crm_object['ASSIGNED_BY_ID']));

  }


  $fields = ['UF_CRM_1559649507' => $arPhotoWatermark,
             'UF_CRM_1563270390' => $arExplWatermark];

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

  $adress = str_replace([" ",","],"+",sprintf("Россия+%s+%s+%s+%s", getCity($data['UF_CRM_1540202817']), enumValue($data['UF_CRM_1540202889'],'UF_CRM_1540202889'), $data['UF_CRM_1540202900'], $data['UF_CRM_1540202908'])); // "Россия+%s+%s+%s+д%s"

  $http = new HttpClient();

  //https://geocode-maps.yandex.ru/1.x/?apikey=%s&geocode=%s&format=json&lang=ru_RU&rspn=0

  $result = json_decode($http->get(sprintf("https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s&language=ru", urlencode($adress), GOOGLE_API_KEY)) ,1);

  //$ll = str_replace(' ',',', $result['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos']);

  $location = $result['results'][0]['geometry']['location'];

  $mapUrl = sprintf("https://maps.googleapis.com/maps/api/staticmap?center=%s&zoom=17&size=500x420&maptype=roadmap&markers=color:blue|%s,%s|label:S&key=%s&language=ru-RU",
  $adress, 
  $location['lat'],
  $location['lng'],
  GOOGLE_API_KEY);

  //"https://static-maps.yandex.ru/1.x/?ll=$ll&size=500,420&z=16&l=map&pt=$ll,pm2bll";
  
  $arFile = CFile::MakeFileArray($mapUrl);

  $type = array_pop(explode('/',$arFile['type']));

  $arFile['name'] ="{$arFile['name']}.{$type}";
  $arFile['del'] = 'Y';
  $arFile['MODULE_ID'] = 'crm';
  $arFile['SUBDIR'] = 'crm_deal_map';

  $arFields['UF_CRM_1548410231729'] = $arFile;

  file_put_contents($_SERVER['DOCUMENT_ROOT'].'/map_log.txt',   $mapUrl.print_r($location ,1).date("d/m/Y H:i:s")."\r\n", FILE_APPEND);

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
 * UF_CRM_1566542004 - окупаемость цифровое 
 * UF_CRM_1541055727999 - МАП
 */
function setRealPrice(&$arFields) : void {

  $select = ['UF_CRM_1540456417','UF_CRM_1541072013901','UF_CRM_1541055727999'];

  $crm_object = \CCrmDeal::GetList(['ID'=>'DESC'], ['ID' => $arFields['ID'] ], $select);

  $object = $crm_object->Fetch();

  $category = \CCrmDeal::GetCategoryID($arFields['ID']);

// проставляем значение "Окупаемость цифровое"

  if($category == 2 && $object['UF_CRM_1541055727999'] > 0) {

    $map_year = $object['UF_CRM_1541055727999'] * 12;
    $paybackDigital = round($object['UF_CRM_1541072013901'] / $map_year, 1);

    $UF = new CUserTypeManager;

    $fields = [
 
      'UF_CRM_1566542004' => $paybackDigital
  
    ];

    if(!$UF->Update("CRM_DEAL", $arFields['ID'], $fields)) {

      file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt', print_r( $fields  ,1).date("d/m/Y H:i:s")."\r\n");

    }

  }



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

  file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/logs/raiting_log.txt', print_r( $arFields ,1).date("d/m/Y H:i:s")."\r\n");
  
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
      'CITY'   => $row['UF_CRM_1540202817'],
      'IS_MOSKOW' => ($row['UF_CRM_1540202817'] == 'Москва' || enumValue($row['UF_CRM_1540202667'],'UF_CRM_1540202667') == 'Москва') ? 1 : 0,

    ];

  }

  if(count(array_values($arResult)) > 3) {

  try {

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

   } catch(Exception $e) {

     $logger = \Log\Logger::instance();
     $logger->setPath('/local/logs/cian_log.txt');
     $logger->error($e->getMessage());

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

function enumValue(?int $value_id = 0, string $code, ?string $entity = 'CRM_DEAL') : string {

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

function getCity(string $value) : string {

  return $value == NOT_ACTUAL_VALUE || !$value ? 'Москва' : $value;

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

if(!function_exists('_SaleFormatCurrency')) {

  function _SaleFormatCurrency(?float $number) : string {

    if(!$number) {
  
      return '';
  
    }
  
    return number_format($number, 0, '', ' ').html_entity_decode("&#8381;");
   
  }

}




