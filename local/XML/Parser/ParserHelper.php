<?php

namespace XML\Parser;

trait ParserHelper {

  protected static $NODE_ELEMENT    = 1;
  protected static $NODE_ATTRIBUTE  = 2;
  protected static $NODE_TEXT       = 3;
  protected static $METRO_DEFAULT   = 159;
  protected static $METRO_IBLOCK_ID = 29;

  protected function buildMorphology(string $value) : string {

    $in =  ['жилой','административный','отдельно стоящее здание','бизнес центр','торговый центр','торгово-офисный центр','производственный комплекс','складской комплекс'];

    $out = ['Жилое','Административное','ОСЗ','Бизнес-центр','Торговый центр','Торгово-офисный центр','Производственный комплекс','Складской комплекс'];

    return str_replace($in, $out, $value);

  }

  protected function roomMorphology(string $value) : string {

    $in =  ['общепит','офисное', 'торговое','под бытовые услуги (салон красоты и т.д.)','производственное помещение','склад'];
    
    $out = ['Общепит','Офисное','Торговое помещение','Под бытовые услуги','Производственное','Складское'];

    return str_replace($in, $out, $value);

  }

  protected function repairMorphology(string $value) : string {

    $in =  ['Shell and core','От предыдущего арендатора'];
    
    $out = ['ShellCore','От прошлого арендатора'];

    return str_replace($in, $out, $value);

  }

  protected function ringMorphology(string $value) : string {

    $in =  ['Бульварное','Садовое'];
    
    $out = ['Бульварное кольцо','Садовое кольцо'];

    return str_replace($in, $out, $value);

  }

  protected function taxMorphology(string $value) : string {

    if($value == 'не актуально') {

       return 'не известно';

    }

    return str_replace('ОСН', 'Включая НДС', $value);

  }

  protected function regionMorphology(string $value) : string {

    return str_replace('Московская область', 'Подмосковье', $value);

  }

  protected function getValue(\DOMElement $node, string $code) : string {

    return trim($node->getElementsByTagName($code)[0]->nodeValue) ? : 'не актуально';

  }

  protected function getFlag(\DOMElement $node, string $code) : string {

     $nodeValue = strtolower($node->getElementsByTagName($code)[0]->nodeValue);

     if($nodeValue == 'да' || $nodeValue == 'yes') {

        return 1;

     }

     return 0;

  }

  protected function parseValue(string $value) : string {

    if($value == 'не актуально') {

       return '';

    }

    return $value;

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

   protected function getDate(string $dateTime) : string {

      if(!strtotime($dateTime)) {

         return '';
         
      }

      $date = new \DateTime($dateTime);

      return $date->format("d.m.Y");

   }

   protected function getPerson(string $userName) : int {

     $filter = array("NAME" => trim($userName), "CHECK_PERMISSIONS" => "N");

     $rsUsers = \CUser::GetList($sort = "NAME",$order = 'desc', $filter, ['SELECT' => ["ID"]]);
 
     return $rsUsers->Fetch()['ID'] ? : GENERAL_BROKER;

   }

   protected function getMetro(string $value) {

     $metro = \CIBlockElement::GetList(['NAME' => 'DESC'], ['%NAME' => $value, 'IBLOCK_ID' => self::$METRO_IBLOCK_ID], false, false, ['ID'])->Fetch();

     return $metro['ID'] ? : self::$METRO_DEFAULT;

   }

   protected function precentToDate(float $number) : string {

    $monthIndex = false;

		if(($monthIndex = strpos($number, ".")) !== false) {

		   $month = (float)("0.".substr($number, $monthIndex + 1));

       $dateYear = (int)substr($number, 0, $monthIndex);
        		
       $yearText = '';
        
       $monthText = '';
        
       $monthValue = (int)(($month) * 365 / 30);

		   if($dateYear == 1) {

					$yearText = 'год';

		   } elseif($dateYear > 1 && $dateYear <= 4 || ($dateYear >= 102 && $dateYear <= 104)) {

				 	$yearText = 'года';

			 } else {

					$yearText = 'лет';

			 }

		   if($monthValue == 0) {

					$monthValue = $monthText = '';
				
			 } else if($monthValue == 1) {

					$monthText = 'месяц';

			 } else if($monthValue > 1 && $monthValue <= 4) {

				 	$monthText = 'месяца';

			 } else {

			  	$monthText = 'месяцев';

       }

			 if($monthValue > 0) {

           $monthValue = ' и '.$monthValue;

			 }
				
			 if($dateYear > 0) {

				  return "$dateYear $yearText $monthValue {$monthText}.";

			 }

		   return "$monthValue {$monthText}.";

			}

			return "$number лет.";

  }

}