<?php

namespace XML\Parser;

use \Bitrix\Main\Event;

class ObjectParser extends Parser {

  private const CITY_TYPE = 288;

  private const REGION_TYPE = 287;

  private const CURRENCY_TYPE = 144;

  private const OBJECT_STATUS_ACTIVE = 357;

  private const OBJECT_IS_ACTUALITY = 329;

  private const PRECENT_PRICE = 466;

  private const FIX_PRICE = 467;

  private const PRECENT_MAP = 472;

  private const METRO_TIME_DEFAULT = 290;

  private const METRO_MAX_TIME = 30;

  private const IS_NEW_CONSTRUCTION = 82;

  private const LEASEHOLDER = 248;

  private const DEFAULT_CITY = 'Москва';

  private const NOT_ACTUAL = 'не актуально';

  private const NULL_FILE_VALUE = 111222;

  private const CATEGORY_MAP = [

     'Помещение в аренду'   => 0,
     'Помещение на продажу' => 1,
     'Арендный бизнес'      => 2
       
  ];

  private const SEMANTIC_CODE = [

    'Помещение в аренду'   => 'UF_CRM_1540974006',
    'Помещение на продажу' => 'UF_CRM_1544172451',
    'Арендный бизнес'      => 'UF_CRM_1544172560'

  ];

  private const CONTACT_TYPE_MAP = [
 
     'Собственник' => 'CLIENT',
     'Представитель собственника' => 'SUPPLIER',
     'Агент' => 'PARTNER'

  ];

  private const COMPANY_TYPE_MAP = [
 
    'Собственник' => 'CUSTOMER',
    'Представитель собственника' => 'SUPPLIER',
    'Агент' => 'COMPETITOR'

  ];

  private const PURPOSE_UF_CODE = ['UF_CRM_1540392018','UF_CRM_1540397421',
                                   'UF_CRM_1540392118','UF_CRM_1540393032',
                                   'UF_CRM_1540397194'];

  private $arFiles;

  protected function execute(\DOMElement $document) : array {

     $nodes = $document->childNodes;

     $this->arFiles = [];

     $arResult = [];

     $iter = 1;

     foreach($nodes as $item) {

      if($iter > LIMIT) break;

      if($item->nodeType == self::$NODE_ELEMENT && $item->nodeName == 'offer') {

         $type = $item->getElementsByTagName('type')[0]->nodeValue;

         $photos = $item->getElementsByTagName('photo')[0]->childNodes;

         $explition = $item->getElementsByTagName('photo-scheme')[0]->childNodes;

         $semantic = $item->getElementsByTagName('description-standardized')[0]->childNodes;

         $purpose = $item->getElementsByTagName('object-purpose')[0]->childNodes;

         $commissionValue = $this->getValue($item, 'Comission');

         $commissionType = $this->getTypeComission($commissionValue, $type);

         $legalEntity = $this->getValue($item, 'LegalEntity');

         $price   = (int)$this->getValue($item, 'price');

         $square  = (int)$this->getValue($item, 'space');

         $MAP     = (int)$this->getValue($item, 'monthly-lease');

         $internal_id = $item->getAttribute('internal-id');

         /**
          * фильтр по объектам
          */
         if(\USE_FILTER == 'Y') {

           if(!in_array($internal_id, \OBJECTS)) {

               continue;

           }

         }

         $arResult[ $internal_id ] =  [

           'ORIGIN_ID'   => $internal_id,
           'CATEGORY_ID' => self::CATEGORY_MAP[$type],
           'TITLE'       => $this->getTitle($type, $item),
           'UF_CRM_1540202889'    => $this->enumID($this->getValue($item,'street-type'), 'UF_CRM_1540202889'),
           'UF_CRM_1540371261836' => $this->enumID($this->buildMorphology($this->getValue($item, 'building-type')), 'UF_CRM_1540371261836'),
           'UF_CRM_1540384807664' => $this->enumID($this->roomMorphology($this->getValue($item, 'facility-type')), 'UF_CRM_1540384807664'),
           'UF_CRM_1540202667'    => $this->enumID($this->regionMorphology($this->getValue($item, 'region')), 'UF_CRM_1540202667'),
           'UF_CRM_1540203144'    => $this->enumID($this->ringMorphology($this->getValue($item, 'moscow-ring')), 'UF_CRM_1540203144'),
           'UF_CRM_1555933663301' => $this->getValue($item, 'price'),
           'UF_CRM_1545649289833' => $price,
           'UF_CRM_1540456417'    => $price,
           'UF_CRM_1541072013901' => $price,
           'UF_CRM_1544425067'    => \SaleFormatCurrency(round($price * 12, 2),'RUB'),
           'UF_CRM_1541072151310' => \SaleFormatCurrency($price / $square,'RUB'),
           'UF_CRM_1540384944'    => $square,
           'UF_CRM_1541076330647' => $square,
           'UF_CRM_1540554743072' => \SaleFormatCurrency(($price * 12) / $square, 'RUB'),
           'UF_CRM_1540385060'    => $this->getValue($item, 'ceiling'),
           'UF_CRM_1540385112'    => $this->getValue($item, 'electricity'),
           'UF_CRM_1540384963'    => $this->getValue($item, 'floor'),
           'UF_CRM_1540371585'    => $this->getValue($item, 'floors-total'),
           'UF_CRM_1540456608'    => $this->enumID($this->taxMorphology($this->getValue($item, 'taxation')), 'UF_CRM_1540456608'),
           'UF_CRM_1541055727999' => $MAP,
           'UF_CRM_1541056049'    => $this->getValue($item, 'annual-index'),
           'UF_CRM_1557913229266' => $this->getValue($item, 'annotation'),
           'UF_CRM_1556182166156' => $commissionType,
           'UF_CRM_1540532330'    => [self::NULL_FILE_VALUE],
           'UF_CRM_1540532459'    => [self::NULL_FILE_VALUE],
           'UF_CRM_1540895373'    => $this->getPerson($this->getValue($item, 'ActualizationPerson')),
           'UF_CRM_1540886934'    => $this->getPerson($this->getValue($item, 'Broker')),
           'ASSIGNED_BY_ID'       => $this->getPerson($this->getValue($item, 'Broker')),
           'UF_CRM_1540456473'    => self::CURRENCY_TYPE,
           'UF_CRM_1540471409'    => $this->getValue($item, 'description'),
           'UF_CRM_1540202900'    => $this->getValue($item, 'street-name'),
           'UF_CRM_1540202908'    => $this->getValue($item, 'building-number'),
           'UF_CRM_1540203111'    => $this->enumID($this->getValue($item, 'Moscow-area'),'UF_CRM_1540203111'),
           'UF_CRM_1540202817'    => $this->getValue($item, 'town') == 'не актуально' ? self::DEFAULT_CITY : $this->getValue($item, 'town'),
           'UF_CRM_1540385262'    => $this->enumID($this->repairMorphology(mb_ucfirst($this->getValue($item, 'renovation'))), 'UF_CRM_1540385262'),
           'UF_CRM_1540202766'    => $this->getValue($item, 'district'),
           'UF_CRM_1540202807'    => $this->enumID($this->getValue($item, 'town-type'),'UF_CRM_1540202807') ? : self::CITY_TYPE,
           'UF_CRM_1540203015'    => $this->getMetroTime($item),
           'UF_CRM_1540202747'    => $this->enumID($this->getValue($item, 'district-type'),'UF_CRM_1540202747') ? : self::REGION_TYPE,
           'UF_CRM_1540385040'    => $this->enumID(mb_ucfirst($this->getValue($item, 'entrance')),'UF_CRM_1540385040'),
           'UF_CRM_1543406565'    => $this->getMetro($this->getValue($item, 'subway')),
           'UF_CRM_1543834582'    => 1,
           'UF_CRM_1545906357580' => 1,
           'UF_CRM_1545199624'    => self::OBJECT_STATUS_ACTIVE,
           'UF_CRM_1543837331299' => $this->getFlag($item, 'publicOnCzian'),
           'UF_CRM_1543834597'    => $this->getFlag($item, 'publicOnYandex'),
           'UF_CRM_1540371938'    => $this->getFlag($item, 'is-mansion'),
           'UF_CRM_1540384916112' => $this->getFlag($item, 'is-basement'),
           'UF_CRM_1552294499136' => $this->getFlag($item, 'enabletext'),
           'UF_CRM_1540532917401' => $this->getValue($item, 'CommentComission'),
           'UF_CRM_1556020811397' => $this->getFlag($item, 'whole-building'),
           'UF_CRM_1544524903217' => $this->getDate($this->getValue($item, 'ActualizationDate')),
           'UF_CRM_1541056313'    => (int)$this->getValue($item, 'lease-duration'),
           'UF_CRM_1556017573094' => $this->getValue($item, 'autotext'),
           'UF_CRM_1556017644158' => $this->getFlag($item,  'BrokerOnDuty'),
           'UF_CRM_1540371455'    => $this->getFlag($item,  'is-new-construction') ? self::IS_NEW_CONSTRUCTION : FALSE,
           'UF_CRM_1552493240038' => $this->getValue($item, 'jk'),
           'UF_CRM_1541004853118' => $this->getFlag($item,  'private-sale'),
           'UF_CRM_1557383288525' => $this->getFlag($item,  'highlightOnCzian')
         ];

         /**
          * Назначение помещения
         */

         foreach(self::PURPOSE_UF_CODE as $purposeCode) {

           $purposeValue = $this->getPurpose($purpose, $purposeCode);

           if($purposeValue) {

              $arResult[ $internal_id ][ $purposeCode ] = $purposeValue;

           }

         }

         /**
          * Собственник
          */

         if($legalEntity != self::NOT_ACTUAL) {

            $arResult[ $internal_id ]['UF_CRM_1540895685'] = $this->createCompanyOwner($item, $legalEntity);

         } else {

           $arResult[ $internal_id ]['UF_CRM_1558086250'] = $this->createContactOwner($item);

         }

        
         /**
          * Агентское вознаграждение:
          * UF_CRM_1556186036149 - процент
          * UF_CRM_1556182207180 - фикс-я цена
          */
         
         if($commissionType == self::PRECENT_PRICE) {

            $arResult[ $internal_id ]['UF_CRM_1556186036149'] = $commissionValue;

         } elseif($commissionType == self::PRECENT_MAP) {

            $arResult[ $internal_id ]['UF_CRM_1556185907'] = $commissionValue;
            
         } elseif($commissionType == self::FIX_PRICE) {

            $arResult[ $internal_id ]['UF_CRM_1556182207180'] = $commissionValue;

         }

         /**
          * Объект актуализирован: да
          */
         if($this->getValue($item, 'ActualizationDate') != self::NOT_ACTUAL) {

             $arResult[ $internal_id ]['UF_CRM_1544528494'] = self::OBJECT_IS_ACTUALITY;
 
         }

         $semantic_code = self::SEMANTIC_CODE[$type];

         $arResult[ $internal_id ][  $semantic_code  ] = $this->getSemantic($semantic, $semantic_code);

         if($type == 'Арендный бизнес') {

            /**
             * Дата подписания договора аренды
             */
            if($this->getValue($item, 'lease-date') != self::NOT_ACTUAL) {

                 $arResult[ $internal_id ]['UF_CRM_1541056258'] = $this->getValue($item, 'lease-date');

            }
         
            $leaseholder = $this->getValue($item, 'leaseholder-standart-name');

            /**
             * Иное названия арендатора
             */
            if($leaseholder == '' || $leaseholder == self::NOT_ACTUAL) {

               $arResult[$internal_id]['UF_CRM_1541055274251'] = $this->getValue($item, 'leaseholder-name');
               $arResult[$internal_id]['UF_CRM_1541055237379'] = self::LEASEHOLDER;
  
            } else {

             /**
             * Название арендатора (стандартное)
             */

             $arResult[$internal_id]['UF_CRM_1541055237379'] = $this->enumID($this->getValue($item, 'leaseholder-standart-name'), 'UF_CRM_1541055237379');

            }

        
            $arResult[ $internal_id ]['UF_CRM_1541055405'] = $this->enumID($this->getValue($item, 'leaseholder-type-1'), 'UF_CRM_1541055405');
            $arResult[ $internal_id ]['UF_CRM_1541055672'] = $this->enumID($this->getValue($item, 'leaseholder-type-2'), 'UF_CRM_1541055672');

            /**
             * доходность
            */

            $arResult[ $internal_id ]['UF_CRM_1541067645026'] = number_format( round( ceil(12 * $MAP / $price * 100), 2), 1, ".","." );

            #echo $arResult[ $internal_id ]['UF_CRM_1541067645026'] ,'<br>';

            /**
             * окупаемость
            */
    
            $arResult[ $internal_id ]['UF_CRM_1544431330'] = $this->precentToDate(round($price / $MAP / 12, 2));

            #echo $arResult[ $internal_id ]['UF_CRM_1544431330'] ;
        
         }

         $this->arFiles[] = ['UF_CRM_1540532330' => $this->getPhoto($photos), 
                             'UF_CRM_1540532459' => $this->getPhoto($explition)];

          
        $iter++;
      }
    }

    return array_splice($arResult, 0, LIMIT);

  }

  private function getTypeComission(string $commission, string $category) : int {

    if($commission == self::NOT_ACTUAL) {

       return 0;
        
    }

    if(strpos($commission, '%') !== false) {

       return $category == 'Помещение в аренду' ?  self::PRECENT_MAP : self::PRECENT_PRICE;

    }
    
    return self::FIX_PRICE;

  }

  private function getPhoto(\DOMNodeList &$nodes)  {

    $photos = [];

    foreach($nodes as $photo) {

     if($photo->nodeValue) {

       $arFile = \CFile::MakeFileArray($photo->nodeValue);

       $arFile['del'] = 'Y';
       $arFile['MODULE_ID'] = 'crm';

       $photos[] = $arFile; 

     }

    }
    
    return $photos;

  }

  private function saveFiles(int $id) : void {

    $files = array_shift($this->arFiles);

    foreach($files as $code => $value) {
    
         $ufManager = new \CUserTypeManager;
         
         $arFields = ["$code" => $value];

         $ufManager->Update('CRM_DEAL', $id, $arFields);

    }
   
  }

  private function getSemantic(?\DOMNodeList &$semantics, string &$code) : array {

    $arResult = [];

    foreach($semantics as $item) {

      if( ($enum_id = $this->enumID($item->nodeValue, $code)) != -1) {

         $arResult[] = $enum_id;

      }
    }

    return $arResult;

  }

  private function getPurpose(?\DOMNodeList &$semantics, string $code) {

    $arResult = [];

    foreach($semantics as $item) {

      if(($enum_id = $this->enumID($item->nodeValue, $code)) != -1) {

         $arResult[] = $enum_id;

      }

    }

    return count($arResult) > 0 ? $arResult : false;

  }

  private function getMetroTime(\DOMElement &$node) : int {

    $valueFeet      = $this->getValue($node, 'subway-time-feet');
    $valueTransport = $this->getValue($node, 'subway-time-transport');

    if($valueFeet != self::NOT_ACTUAL) {

        return $this->enumID( sprintf("%s %s пешком", $valueFeet, $this->metroMinute($valueFeet)), 'UF_CRM_1540203015');

    } elseif($valueTransport != self::NOT_ACTUAL) {

      if($valueTransport > self::METRO_MAX_TIME) {

         return $this->enumID( sprintf("Более %s минут на транспорте", $valueTransport), 'UF_CRM_1540203015');

      }
      
      return $this->enumID( sprintf("%s %s на транспорте", $valueTransport, $this->metroMinute($valueTransport)), 'UF_CRM_1540203015');

    }

    return self::METRO_TIME_DEFAULT;

  }

  private function metroMinute(int $value) : string {

     return $value == 1 ? 'минута' : 'минут';

  }

  private function getTitle(string $type, \DOMElement &$item) : string {

    return sprintf("%s - %s %s %s", $type, 
                   $this->getValue($item, 'street-name'),  
                   $this->getValue($item, 'street-type'), 
                   $this->getValue($item, 'building-number')
                  );

  }

  private function createContactOwner(\DOMElement &$item, ?int $companyID = null) : int {

    if($this->getValue($item,'FIO_sobstvennik') == self::NOT_ACTUAL) {

      $name = array_pop($this->toArray($this->parseValue($this->getValue($item, 'Email_Sobstvennik'))));
      
    } else {

      [$name, $lastName, $secondName] = explode(' ', $this->getValue($item,'FIO_sobstvennik'));

    }

    $emails = $this->multiFields($this->toArray($this->parseValue($this->getValue($item, 'Email_Sobstvennik'))));

    $phones = $this->multiFields($this->toArray($this->parseValue($this->getValue($item, 'Tel_Sobstvennik'))));

    $arContact = [

         'NAME'        => $name,
         'LAST_NAME'   => $lastName,
         'SECOND_NAME' => $secondName,
         'ASSIGNED_BY_ID' => $this->getPerson($this->getValue($item, 'Broker')),
         'TYPE_ID'        => self::CONTACT_TYPE_MAP[ $this->getValue($item, 'Tip_Sobstvennik') ],
         'COMMENTS'       => $this->parseValue($this->getValue($item,'OwnerComment')),
         'UF_CRM_1558614620966' => $this->parseValue($this->getValue($item,'FIO_sobstvennik')),
         'FM' => [

            'EMAIL' => $emails,
            'PHONE' => $phones,
            'WEB'   => ['n0' => ['VALUE' => $this->parseValue($this->getValue($item,'www_link')), 'VALUE_TYPE' => 'WORK']]    

         ]
    ];

    if($companyID) {
      
       $arContact['COMPANY_ID'] = $companyID;

    }

    $contact = new \CCrmContact(false);

    $id = $contact->Add($arContact);

    if(!is_int($id)) {

       $this->errors[] = [ 'internal_id' => $item->getAttribute('internal-id') , 'error' => $contact->LAST_ERROR, 'data' => $arContact];

       return 0;

    } else {

       return $id;

    }

  }

  private function createCompanyOwner(\DOMElement &$item, string $legalEntity) : int {

    $company = new \CCrmCompany(false);

    $arCompany = [
         
         'TITLE'          => $legalEntity, 
         'COMPANY_TYPE'   => self::COMPANY_TYPE_MAP[ $this->getValue($item, 'Tip_Sobstvennik') ],
         'ASSIGNED_BY_ID' => $this->getPerson($this->getValue($item, 'Broker')),
         'FM' => [ 
           'WEB'   => ['n0' => ['VALUE' => $this->parseValue($this->getValue($item,'www_link')),'VALUE_TYPE' => 'WORK']] 
         ]   
      
    ];

    $companyID = $company->Add($arCompany);

    if($this->getValue($item,'FIO_sobstvennik')   != self::NOT_ACTUAL || 
       $this->getValue($item,'Email_Sobstvennik') != self::NOT_ACTUAL) {

       $this->createContactOwner($item, $companyID);

     }

     if(!is_int($companyID)) {

       $this->errors[] = $company->LAST_ERROR;

       return 0;

     } else {

       return $companyID;

    }
  }

  protected function fireEvent(array &$event) : bool {
 
    $afterEvents = GetModuleEvents('crm', 'OnAfterCrmDealUpdate');

    while ($arEvent = $afterEvents->Fetch()) {
              
      ExecuteModuleEventEx($arEvent, array(&$event));

    }

    $beforeEvents = GetModuleEvents('crm', 'OnBeforeCrmDealUpdate');

    while($arEvent = $beforeEvents->Fetch()) {
              
      ExecuteModuleEventEx($arEvent, array(&$event));

    }

    $this->saveFiles($event['ID']);

    return true;

  }
}