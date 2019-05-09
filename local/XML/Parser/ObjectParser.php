<?php

namespace XML\Parser;

use \Bitrix\Main\Event;

class ObjectParser extends Parser {

  private const CITY_TYPE = 288;

  private const REGION_TYPE = 287;

  private const CURRENCY_TYPE = 144;

  private const METRO_TIME_DEFAULT = 290;

  private const METRO_MAX_TIME = 30;

  private const IS_NEW_CONSTRUCTION = 82;

  private const LEASEHOLDER = 248;

  private const DEFAULT_CITY = 'Москва';

  private const NOT_ACTUAL = 'не актуально';

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

         $internal_id = $item->getAttribute('internal-id');

         $arResult[ $internal_id ] =  [

           'ORIGIN_ID'   => $internal_id,
           'CATEGORY_ID' => self::CATEGORY_MAP[$type],
           'TITLE'       => $this->getTitle($type, $item),
           'UF_CRM_1540202889'    => $this->enumID($this->getValue($item,'street-type'), 'UF_CRM_1540202889'),
           'UF_CRM_1540371261836' => $this->enumID($this->buildMorphology($this->getValue($item, 'building-type')), 'UF_CRM_1540371261836'),
           'UF_CRM_1540384807664' => $this->enumID($this->roomMorphology($this->getValue($item, 'facility-type')), 'UF_CRM_1540384807664'),
           'UF_CRM_1540202667'    => $this->enumID($this->getValue($item, 'region'), 'UF_CRM_1540202667'),
           'UF_CRM_1540203144'    => $this->enumID($this->getValue($item, 'moscow-ring'), 'UF_CRM_1540203144'),
           'UF_CRM_1555933663301' => $this->getValue($item, 'price'),
           'UF_CRM_1545649289833' => (int)$this->getValue($item, 'price'),
           'UF_CRM_1540456417'    => $this->getValue($item, 'price'),
           'UF_CRM_1541072013901' => $this->getValue($item, 'price'),
           'UF_CRM_1540384944'    => $this->getValue($item, 'space'),
           'UF_CRM_1540385060'    => $this->getValue($item, 'ceiling'),
           'UF_CRM_1540385112'    => $this->getValue($item, 'electricity'),
           'UF_CRM_1540384963'    => $this->getValue($item, 'floor'),
           'UF_CRM_1540371585'    => $this->getValue($item, 'floors-total'),
           'UF_CRM_1540456608'    => $this->enumID($this->taxMorphology($this->getValue($item, 'taxation')), 'UF_CRM_1540456608'),
      
           'UF_CRM_1540532330'    => [1111],
           'UF_CRM_1540532459'    => [2222],

           'UF_CRM_1540895373'    => $this->getPerson($this->getValue($item, 'ActualizationPerson')),
           'UF_CRM_1540886934'    => $this->getPerson($this->getValue($item, 'Broker')),
           'UF_CRM_1540456473'    => self::CURRENCY_TYPE,
           'UF_CRM_1540471409'    => $this->getValue($item, 'description'),
           'UF_CRM_1540202900'    => $this->getValue($item, 'street-name'),
           'UF_CRM_1540202908'    => $this->getValue($item, 'building-number'),
           'UF_CRM_1540203111'    => $this->enumID($this->getValue($item, 'Moscow-area'),'UF_CRM_1540203111'),
           'UF_CRM_1540202817'    => $this->getValue($item, 'town') == 'не актуально' ? self::DEFAULT_CITY : $this->getValue($item, 'town'),
           'UF_CRM_1540385262'    => $this->enumID($this->repairMorphology(mb_ucfirst($this->getValue($item, 'renovation'))), 'UF_CRM_1540385262'),
           'UF_CRM_1541055237379' => $this->enumID($this->getValue($item, 'leaseholder-standart-name') ,'UF_CRM_1541055237379'),
           'UF_CRM_1540202766'    => $this->getValue($item, 'district'),
           'UF_CRM_1554303694'    => $this->getValue($item, 'Comission'),
           'UF_CRM_1540202807'    => $this->enumID($this->getValue($item, 'town-type'),'UF_CRM_1540202807') ? : self::CITY_TYPE,
           'UF_CRM_1540203015'    => $this->getMetroTime($item),
           'UF_CRM_1540202747'    => $this->enumID($this->getValue($item, 'district-type'),'UF_CRM_1540202747') ? : self::REGION_TYPE,
           'UF_CRM_1540385040'    => $this->enumID(mb_ucfirst($this->getValue($item, 'entrance')),'UF_CRM_1540385040'),
           'UF_CRM_1543406565'    => $this->getMetro($this->getValue($item, 'subway')),
           'UF_CRM_1540392018'    => $this->getPurpose($purpose),
           'UF_CRM_1543834582'    => 1,
           'UF_CRM_1545906357580' => 1,
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

         $arResult[ $internal_id ][ self::SEMANTIC_CODE[$type]  ] = $this->getSemantic($semantic);

         if($type == 'Арендный бизнес') {

            $arResult[ $internal_id ]['UF_CRM_1541056258'] = $this->getValue($item, 'lease-date');

            if($leaseholder = $this->getValue($item, 'leaseholder-name')) {

              $arResult[$internal_id]['UF_CRM_1541055274251'] = $leaseholder;
              $arResult[$internal_id]['UF_CRM_1541055237379'] = self::LEASEHOLDER;
  
           } else {

              $arResult[$internal_id]['UF_CRM_1541055237379'] = $this->enumID($this->getValue($item, 'leaseholder-standart-name'), 'UF_CRM_1541055237379');

           }
        
         }

         $this->arFiles[] = ['UF_CRM_1540532330' => $this->getPhoto($photos), 
                             'UF_CRM_1540532459' => $this->getPhoto($explition)];

          
        $iter++;
      }
    }

    return array_splice($arResult, 0, LIMIT);

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

  private function getPhoto(\DOMNodeList $nodes)  {

    $photos = [];

    foreach($nodes as $photo) {

     if($photo->nodeValue) {

       $arFile = \CFile::MakeFileArray($photo->nodeValue);

       $arFile['name'] = strtolower(str_replace(" ","", $arFile['name']));
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

  private function getSemantic(?\DOMNodeList $semantics) : array {

    $arResult = [];

    foreach($semantics as $item) {

      if($enum_id = $this->enumID($item->nodeValue, 'UF_CRM_1540974006') != -1) {

         $arResult[] = $enum_id;

      }
    }

    return $arResult;

  }

  private function getPurpose(?\DOMNodeList $semantics) : array {

    $arResult = [];

    foreach($semantics as $item) {

      if($enum_id = $this->enumID($item->nodeValue, 'UF_CRM_1540392018') != -1) {

         $arResult[] = $enum_id;

      }

    }

    return $arResult;

  }

  private function getMetroTime(\DOMElement $node) : int {

    $valueFeet      = $this->getValue($node, 'subway-time-feet');
    $valueTransport = $this->getValue($node, 'subway-time-transport');

    if($valueFeet != self::NOT_ACTUAL) {

        return $this->enumID( sprintf("%s %s пешком",$valueFeet, $valueFeet == 1 ? 'минута' : 'минут'), 'UF_CRM_1540203015');

    } elseif($valueTransport != self::NOT_ACTUAL) {

      if($valueTransport > self::METRO_MAX_TIME) {

         return $this->enumID( sprintf("Более %s минут на транспорте", $valueTransport), 'UF_CRM_1540203015');

      }
      
      return $this->enumID( sprintf("%s минут на транспорте", $valueTransport), 'UF_CRM_1540203015');

    }

    return self::METRO_TIME_DEFAULT;

  }

  private function getTitle(string $type, \DOMElement $item) : string {

    return sprintf("%s - %s %s %s", $type, 
                   $this->getValue($item, 'street-name'),  
                   $this->getValue($item, 'street-type'), 
                   $this->getValue($item, 'building-number')
                  );

  }

}