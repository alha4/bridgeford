<?php

namespace XML\Parser;

use XML\Parser\Parser;

class Rent extends Parser {

  protected static $path = '/rent.xml';

  private const CATEGORY = 0;

  private const TITLE = 'Помещение в аренду';

  protected function execute(\DOMElement $document) : array {

     $nodes = $document->childNodes;

     $arResult = [];

     foreach($nodes as $item) {

      if($item->nodeType == self::$NODE_ELEMENT && $item->nodeName == 'offer') {

         $external_id = $item->getAttribute('internal-id');

         $photos = $item->getElementsByTagName('photo')[0]->childNodes;

         $arResult[] =  [

           'ORIGIN_ID'   => $external_id,
           'CATEGORY_ID' => self::CATEGORY,
           'TITLE'       => $this->getTitle($item),
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
           'UF_CRM_1540456608'    => $this->enumID($this->getValue($item, 'taxation'), 'UF_CRM_1540456608'),
           'UF_CRM_1540532330'    => $this->getPhoto($photos),
           'UF_CRM_1540456473'    => 144,
           'UF_CRM_1540471409'    => 'заглушка',
           'UF_CRM_1540202900'    => $this->getValue($item, 'street-name'),
           'UF_CRM_1540202908'    => $this->getValue($item, 'building-number'),
           'UF_CRM_1540203111'    => $this->enumID($this->getValue($item, 'Moscow-area'),'UF_CRM_1540203111'),
           'UF_CRM_1540202817'    => $this->getValue($item, 'town'),
           'UF_CRM_1540385262'    => $this->enumID($this->repairMorphology($this->getValue($item, 'renovation')), 'UF_CRM_1540385262'),
           'UF_CRM_1540202766'    => $this->getValue($item, 'district'),
           'UF_CRM_1540202807'    => 288,
           'UF_CRM_1540203015'    => 290,
           'UF_CRM_1540202747'    => 287,
           'UF_CRM_1540385040'    => $this->enumID($this->getValue($item, 'entrance'),'UF_CRM_1540385040'),
           'UF_CRM_1543406565'    => $this->getMetro($this->getValue($item, 'subway'))
       
         ];

      }
     }
    
     return array_splice($arResult,0,3);

  }

  private function getPhoto(\DOMNodeList $nodes) : array {

    $photos = [];

    foreach($nodes as $photo) {

      $arFile = \CFile::MakeFileArray($photo->nodeValue);

      $arFile['del'] = 'Y';
      $arFile['MODULE_ID'] = 'crm';

      $photos[] = \CFile::SaveFile($arFile,'crm');

    }
    
    return $photos;

  }

  private function getTitle($item) : string {

    return sprintf("%s - %s %s %s", self::TITLE, $this->getValue($item, 'street-name'),  
                   $this->getValue($item, 'street-type'), $this->getValue($item, 'building-number'));

  }



}