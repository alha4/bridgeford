<?php

namespace XML\Parser;

class ObjectParser extends Parser {

  private const CITY_TYPE = 288;

  private const REGION_TYPE = 287;

  private const CURRENCY_TYPE = 144;

  private const METRO_TIME_DEFAULT = 290;

  private const METRO_MAX_TIME = 30;

  private const CATEGORY_MAP = [

     'Помещение в аренду'   => 0,
     'Помещение на продажу' => 1,
     'Арендный бизнес'      => 2
       
  ];

  protected function execute(\DOMElement $document) : array {

     $nodes = $document->childNodes;

     $arResult = [];

     foreach($nodes as $item) {

      if($item->nodeType == self::$NODE_ELEMENT && $item->nodeName == 'offer') {

         $type = $item->getElementsByTagName('type')[0]->nodeValue;

         $photos = $item->getElementsByTagName('photo')[0]->childNodes;

         $explition = $item->getElementsByTagName('photo-scheme')[0]->childNodes;

         $semantic = $item->getElementsByTagName('description-standardized')[0]->childNodes;

         $purpose = $item->getElementsByTagName('object-purpose')[0]->childNodes;

         $arResult[] =  [

           'ORIGIN_ID'   => $item->getAttribute('internal-id'),
           'CATEGORY_ID' => self::CATEGORY_MAP[$type],
           'TITLE'       => $this->getTitle($type, $item),
           'UF_CRM_1540202889'    => $this->enumID($this->getValue($item,'street-type'), 'UF_CRM_1540202889'),
           'UF_CRM_1540371261836' => $this->enumID($this->buildMorphology($this->getValue($item, 'building-type')), 'UF_CRM_1540371261836'),
           'UF_CRM_1540384807664' => $this->enumID($this->roomMorphology($this->getValue($item, 'facility-type')), 'UF_CRM_1540384807664'),
           'UF_CRM_1540202667'    => $this->enumID($this->getValue($item, 'region'), 'UF_CRM_1540202667'),
           'UF_CRM_1540203144'    => $this->enumID($this->getValue($item, 'moscow-ring'), 'UF_CRM_1540203144'),
           'UF_CRM_1555933663301' => $this->getValue($item, 'price'),
           'UF_CRM_1545649289833' => $this->getValue($item, 'price_st'),
           'UF_CRM_1540384944'    => $this->getValue($item, 'space'),
           'UF_CRM_1540385060'    => $this->getValue($item, 'ceiling'),
           'UF_CRM_1540385112'    => $this->getValue($item, 'electricity'),
           'UF_CRM_1540384963'    => $this->getValue($item, 'floor'),
           'UF_CRM_1540371585'    => $this->getValue($item, 'floors-total'),
           'UF_CRM_1540456608'    => $this->enumID($this->taxMorphology($this->getValue($item, 'taxation')), 'UF_CRM_1540456608'),
           'UF_CRM_1540532330'    => $this->getPhoto($photos),
           'UF_CRM_1540532459'    => $this->getPhoto($explition),
           'UF_CRM_1540895373'    => $this->getPerson($this->getValue($item, 'ActualizationPerson')),
           'UF_CRM_1540886934'    => $this->getPerson($this->getValue($item, 'Broker')),
           'UF_CRM_1540456473'    => self::CURRENCY_TYPE,
           'UF_CRM_1540471409'    => $this->getValue($item, 'description'),
           'UF_CRM_1540202900'    => $this->getValue($item, 'street-name'),
           'UF_CRM_1540202908'    => $this->getValue($item, 'building-number'),
           'UF_CRM_1540203111'    => $this->enumID($this->getValue($item, 'Moscow-area'),'UF_CRM_1540203111'),
           'UF_CRM_1540202817'    => $this->getValue($item, 'town'),
           'UF_CRM_1540385262'    => $this->enumID($this->repairMorphology(mb_ucfirst($this->getValue($item, 'renovation'))), 'UF_CRM_1540385262'),
           'UF_CRM_1540202766'    => $this->getValue($item, 'district'),
           'UF_CRM_1554303694'    => $this->getValue($item, 'Comission'),
           'UF_CRM_1540202807'    => self::CITY_TYPE,
           'UF_CRM_1540203015'    => $this->getMetroTime($item),
           'UF_CRM_1540202747'    => self::REGION_TYPE,
           'UF_CRM_1540385040'    => $this->enumID(mb_ucfirst($this->getValue($item, 'entrance')),'UF_CRM_1540385040'),
           'UF_CRM_1543406565'    => $this->getMetro($this->getValue($item, 'subway')),
           'UF_CRM_1540974006'    => $this->getSemantic($semantic),
           'UF_CRM_1540392018'    => $this->getPurpose($purpose),
           'UF_CRM_1543834582'    => 1,
           'UF_CRM_1543837331299' => $this->getFlag($item, 'publicOnCzian'),
           'UF_CRM_1543834597'    => $this->getFlag($item, 'publicOnYandex'),
           'UF_CRM_1540371938'    => $this->getFlag($item, 'is-mansion'),
           'UF_CRM_1540384916112' => $this->getFlag($item, 'is-basement'),
           'UF_CRM_1552294499136' => $this->getFlag($item, 'enabletext'),
           'UF_CRM_1540532917401' => $this->getValue($item, 'CommentComission'),
           'UF_CRM_1556020811397' => $this->getFlag($item, 'whole-building'),
           'UF_CRM_1544524903217' => $this->getDateActualization($this->getValue($item, 'ActualizationDate')),
           'UF_CRM_1556017573094' => $this->getValue($item, 'autotext'),
           'UF_CRM_1556017644158' => $this->getFlag($item,  'BrokerOnDuty'),
         ];

      }
     }
    
     return array_splice($arResult,0,3);

  }

  private function getPhoto(\DOMNodeList $nodes)  {

    $photos = [];

    foreach($nodes as $photo) {

    if($photo->nodeValue) {

       $arFile = \CFile::MakeFileArray($photo->nodeValue);

       $arFile['del'] = 'Y';
       $arFile['MODULE_ID'] = 'crm';

       $photos[] = \CFile::SaveFile($arFile,'crm_deal_rent');

     }

    }
    
    return $photos;

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

    if($valueFeet) {

        return $this->enumID( sprintf("%s %s пешком",$valueFeet, $valueFeet == 1 ? 'минута' : 'минут'), 'UF_CRM_1540203015');

    } elseif($valueTransport) {

      if($valueTransport > self::METRO_MAX_TIME) {

         return $this->enumID( sprintf("Более %s минут на транспорте", $valueTransport), 'UF_CRM_1540203015');

      }
      
      return $this->enumID( sprintf("%s минут на транспорте",$valueFeet), 'UF_CRM_1540203015');

    }

    return self::METRO_TIME_DEFAULT;

  }

  private function getDateActualization(string $dateTime) : string {

    $date = new \DateTime($dateTime);

    return $date->format("d.m.Y");

  }

  private function getTitle(string $type, \DOMElement $item) : string {

    return sprintf("%s - %s %s %s", $type, 
                   $this->getValue($item, 'street-name'),  
                   $this->getValue($item, 'street-type'), 
                   $this->getValue($item, 'building-number')
                  );

  }

}