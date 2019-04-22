<?php

namespace XML\Parser;

trait ParserHelper {

  protected static $NODE_ELEMENT = 1;
  protected static $NODE_ATTRIBUTE = 2;
  protected static $NODE_TEXT = 3;

  protected function buildMorphology(string $value) : string {

    $in = ['жилой','административный','отдельно стоящее здание','бизнес центр','торговый центр','торгово-офисный центр','производственный комплекс','складской комплекс'];

    $out = ['Жилое','Административное','ОСЗ','Бизнес-центр','Торговый центр','Торгово-офисный центр','Производственный комплекс','Складской комплекс'];

    return str_replace($in, $out, $value);

  }

  protected function roomMorphology(string $value) : string {

    $in = ['торговое','под бытовые услуги (салон красоты и т.д.)','производственное помещение','склад'];
    
    $out = ['Торговое помещение','Под бытовые услуги','Производственное','Складское'];

    return str_replace($in, $out, $value);

  }

  protected function repairMorphology(string $value) : string {

    $in = ['shell & core'];
    
    $out = ['ShellCore'];

    return str_replace($in, $out, $value);

  }

  protected function getValue(\DOMElement $node, string $code) : string {

    return $node->getElementsByTagName($code)[0]->nodeValue ? : 'не актуально';

  }

  protected function enumID(string $value, string $code, ?string $entity = 'CRM_DEAL') : int {

    $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => $entity, "FIELD_NAME" => $code));
    $entity = $entityResult->Fetch();
  
    $enumResult = \CUserFieldEnum::GetList(['VALUE' => "DESC"], ["USER_FIELD_ID" => $entity['ID'], "VALUE" => $value]);
     
    while($enum = $enumResult->GetNext()) {
  
      if($enum['VALUE'] == $value) {
    
           return $enum['ID'];
    
        }
      }
  
     return -1;
   }

   protected function getMetro(string $value) {

     $metro = \CIBlockElement::GetList(['NAME' => 'DESC'], ['%NAME' => $value, 'IBLOCK_ID' => 29], false, false, ['ID'])->Fetch();

     return $metro['ID'];

   }

}