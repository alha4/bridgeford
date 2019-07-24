<?php

namespace XML\Cian;

use XML\ExportBase;

final class CianXml extends ExportBase {

  protected $fileName = '/cian_commerc.xml';

  private const PHONE_NUMBER = '4951545346';
  
  private const CATEGORY = [

                   '0' => 'Rent',
                   '1' => 'Sale',
                   '2' => 'Sale'
                ];
              
  private const TITLE_ALIAS = [

                  '0' => 'Аренда помещения',
                  '1' => 'Помещение на продажу',
                  '2' => 'Арендный бизнес'
            
                ];
            
            
  private const TITLE_ALIAS_SYNONYM = [
            
                  '0' => 'Помещение в аренду',
                  '1' => 'Продажа помещения'
            
                ];
            
  private const SERVICE_TYPE = [

     
                  '200' => 'paid',
                  '201' => 'premium',
                  '479' => 'top3',
                  '199' => 'highlight'

                ];

  private const SERVICE_TYPE_1 = [


                  '204' => 'paid',
                  '205' => 'premium',
                  '475' => 'top3',
                  '203' => 'highlight'

                ];

  private const SERVICE_TYPE_2 = [


                  '208' => 'paid',
                  '477' => 'top3',
                  '207' => 'highlight',
                  '209' => 'premium'

                ];
              
  private const SERVICE_NO_PREMIUM = 480;

  private const SERVICE_TYPE_1_NO_PREMIUM = 481;
              
  private const SERVICE_TYPE_2_PREMIUM = 482;
  
  private const CATEGORY_ADS = [

                  '88' => 'shoppingArea',
                  '89' => 'freeAppointmentObject',
                  '90' => 'office',
                  '91' => 'freeAppointmentObject',
                  '92' => 'freeAppointmentObject',
                  '93' => 'industry',
                  '94' => 'warehouse'

                ];

  private const BUILDING_TYPE = [

                  '74' => 'residentialHouse',
                  '75' => 'administrativeBuilding',
                  '76' => 'standaloneBuilding',
                  '77' => 'businessCenter',
                  '78' => 'shoppingCenter',
                  '79' => 'shoppingAndBusinessComplex',
                  '80' => 'industrialComplex',
                  '81' => 'warehouseComplex'

                ];

  private const CURRENCY =  [

                  "144" => 'rur',
                  "145" => 'usd',
                  "146" => 'eur'

                ];

  private const VATTYPE = [

                   "150" => 'usn',
                   "151" => 'vatIncluded',
                   " "   => 'vatNotIncluded'
                ];

  private const INPUTTYPE = [

                   "96"  =>  "commonFromStreet",
                   "95"  =>  "separateFromStreet"

                ];

  private const AREATYPE = [

                  "86" => "owned",
                  "87" => "rent"

                ];

  /**
   * 
   * bool UF_CRM_1543837331299 - Реклама циан 
   * 
  */             
              
  protected function buildXml() : string {

    $sort   = ["UF_CRM_1545199624" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1543837331299" => 1];

    $select = ["UF_CRM_1540202817","UF_CRM_1540202900","UF_CRM_1540202889","UF_CRM_1540202908", "UF_CRM_1540203111",
               "UF_CRM_1540202667", "UF_CRM_1540371938", "UF_CRM_1540456737395","UF_CRM_1540456417","UF_CRM_1540554743072",
               "UF_CRM_1540886934","UF_CRM_1540384807664","UF_CRM_1540384963","UF_CRM_1541076330647", "UF_CRM_1545906357580",
               "UF_CRM_1540371585","UF_CRM_1540385060","OPPORTUNITY","UF_CRM_1540371261836","UF_CRM_1540301873849","UF_CRM_1543406565",
               "UF_CRM_1540456473","UF_CRM_1540456608","UF_CRM_1540381458431","UF_CRM_1540532735882","UF_CRM_1540471409",
               "UF_CRM_1541004853118","UF_CRM_1540385040","UF_CRM_1540385112","UF_CRM_1540532330","UF_CRM_1540532419", "UF_CRM_1540384944",
               "UF_CRM_1544172451","UF_CRM_1540976407661","UF_CRM_1540977227270","UF_CRM_1540977306391","UF_CRM_1544431330",
               "UF_CRM_1540974006","UF_CRM_1544172451","UF_CRM_1544172560","UF_CRM_1552294499136","UF_CRM_1540203015","UF_CRM_1541072013901",
               "UF_CRM_1541072151310","UF_CRM_1540371455","UF_CRM_1541055237379","UF_CRM_1544431330","UF_CRM_1541056313","UF_CRM_1540392018",
              "UF_CRM_1540371802","UF_CRM_1555070914","UF_CRM_1545649289833","UF_CRM_1556020811397", "UF_CRM_1559649507", "UF_CRM_1556017573094"];

    $xml_string = '<feed><feed_version>2</feed_version>';

    $object = \CCrmDeal::GetList($sort, $filter, $select);

    while($row = $object->Fetch()) {

      $category_id =  \CCrmDeal::GetCategoryID($row['ID']);

      $semantic_code = self::SEMANTIC_CODE[$category_id];

      $semantic = (array)$row[$semantic_code];

      $title = $this->getTitle($row, $category_id);

      $xml_string.= '<object>';
      $xml_string.= sprintf("<ExternalId>%s</ExternalId>", $row['ID']);
      $xml_string.= sprintf("<Title>%s</Title>", $title);

      
      $xml_string.= sprintf("<Category>%s</Category>", $this->getCategory($row['UF_CRM_1540384807664'], $category_id, $row['UF_CRM_1556020811397']));


	  	//bool UF_CRM_1552294499136 - автотекст в xml
	    //string UF_CRM_1556017573094 - автотекст с сайта
		  // UF_CRM_1540471409 - описание объекта

      if($category_id == self::RENT_BUSSINES) {

      $xml_string.= sprintf("<Description>%s %s</Description>", $title, (bool)$row['UF_CRM_1552294499136'] ? 
                        $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));

      } else {

      $xml_string.= sprintf("<Description>%s</Description>", (bool)$row['UF_CRM_1552294499136'] ? 
                    $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));

      }

      $xml_string.= '<PlacementType>streetRetail</PlacementType>';

      $xml_string.= sprintf("<Address>%s</Address>", $this->getAddress($row));
      $xml_string.= sprintf("<Phones><PhoneSchema><CountryCode>+7</CountryCode><Number>%s</Number></PhoneSchema></Phones>",
                    self::PHONE_NUMBER);
                  
      $metroTime = $this->enumValue((int)$row['UF_CRM_1540203015'],'UF_CRM_1540203015');

      $xml_string.= sprintf('<Undergrounds><UndergroundInfoSchema>%s</UndergroundInfoSchema></Undergrounds>', 
      $this->getMetro((int)$row['UF_CRM_1543406565'], $metroTime));

      $xml_string.= sprintf("<FloorNumber>%s</FloorNumber>",$row['UF_CRM_1540384963']);
      $xml_string.= sprintf("<TotalArea>%s</TotalArea>", $row['UF_CRM_1541076330647']);
      $xml_string.= sprintf("<IsInHiddenBase>%s</IsInHiddenBase>", $row['UF_CRM_1541004853118']);
      $xml_string.= sprintf("<InputType>%s</InputType>", self::INPUTTYPE[$row["UF_CRM_1540385040"]]);
      $xml_string.= sprintf("<Electricity>%s</Electricity>", $row['UF_CRM_1540385112']);
      
      
      $xml_string.= sprintf("<PublishTerms><Terms><PublishTermSchema><Services>%s</Services>%s</PublishTermSchema></Terms></PublishTerms>", 
      $this->getAdsServices($row),
      $this->getAdsExcluded((int)$row['UF_CRM_1540976407661']));

      $xml_string.= "<Photos>";
		  $xml_string.= $this->getPhotos((array)$row['UF_CRM_1559649507']); //без вотермарков  UF_CRM_1540532330   с вотермарками UF_CRM_1559649507
      $xml_string.= "</Photos>";

      $xml_string.= '<Building>';
      $xml_string.= sprintf("<Type>%s</Type>", $this->getBuildingType($row['UF_CRM_1540371261836'], $row['UF_CRM_1540371938']));
      $xml_string.= sprintf("<FloorsCount>%s</FloorsCount>", $row['UF_CRM_1540371585']);
      $xml_string.= sprintf("<CeilingHeight>%s</CeilingHeight>", $row['UF_CRM_1540385060']);
      $xml_string.= sprintf("<Parking><PlacesCount>%s</PlacesCount></Parking>", $row['UF_CRM_1540301873849']);
      $xml_string.= sprintf("<Land><Type>%s</Type></Land>", self::AREATYPE[$row['UF_CRM_1540381458431']]);
      $xml_string.= '</Building>';
   
      $xml_string.= sprintf("<BargainTerms><VatType>%s</VatType>", self::VATTYPE[$row["UF_CRM_1540456608"]]);

      $xml_string.= sprintf("<Price>%s</Price>", (int)$row['UF_CRM_1545649289833']);

      $xml_string.= "<PriceType>all</PriceType>";

      $xml_string.= "<PaymentPeriod>monthly</PaymentPeriod>";


      if($category_id == self::RENT) {

      $xml_string.= sprintf("<HasGracePeriod>%s</HasGracePeriod>", $row['UF_CRM_1540456737395'] == 1 ? 'true' : 'false');

      }

      $xml_string.= "</BargainTerms>";

      $xml_string.= sprintf("<Currency>%s</Currency>", self::CURRENCY[$row['UF_CRM_1540456473']]);

      $xml_string.= '</object>';

    }

    $xml_string.= '</feed>';

    return $xml_string;

  }

  private function getTitle(array $row, int $category_id) : string {

    $square = ($category_id == self::RENT_BUSSINES) ? (int)$row['UF_CRM_1541076330647'] : (int)$row['UF_CRM_1540384944'];

    $region = $this->enumValue((int)$row['UF_CRM_1540203111'],'UF_CRM_1540203111');
    $region.= ', ';
    
    switch($category_id) {

      case self::RENT :

      return strtoupper(sprintf("%s, %s %s",  self::TITLE_ALIAS_SYNONYM[$category_id],$region, $this->getMetre($square)));

      break;

      case self::SALE :

      return strtoupper(sprintf("%s, %s %s",  self::TITLE_ALIAS_SYNONYM[$category_id], $region, $this->getMetre($square)));

      break;

      case self::RENT_BUSSINES :

      if($row['UF_CRM_1545906357580']) {

          return strtoupper(sprintf("%s, окупаемость - %s", self::TITLE_ALIAS[$category_id],  $row['UF_CRM_1544431330']));

      }

      return strtoupper(sprintf("%s", self::TITLE_ALIAS[$category_id]));

      break;

    }

  }  

  private function getAdsExcluded(int $variant) : string {

    if($variant != self::SERVICE_NO_PREMIUM) {

       return '';

    }

    return '<ExcludedService><Services><ServicesEnum>premium</ServicesEnum></Services></ExcludedService>';

  }
  
  private function getAdsServices(array $data)  : string {

    $xml_service = sprintf("<ServicesEnum>%s</ServicesEnum>",self::SERVICE_TYPE[$data['UF_CRM_1540976407661']]);

    if($data['UF_CRM_1540977227270'] > 0) {

      $xml_service.= sprintf("<ServicesEnum>%s</ServicesEnum>",self::SERVICE_TYPE_1[$data['UF_CRM_1540977227270']]);

    }

    if($data['UF_CRM_1540977306391'] > 0) {

      $xml_service.= sprintf("<ServicesEnum>%s</ServicesEnum>",self::SERVICE_TYPE_2[$data['UF_CRM_1540977306391']]);

    }

    return $xml_service;

  }

  private function getBuildingType(int $type, $is_mansion) : string {

    if((bool)$is_mansion) {

       return 'mansion';

    }

    return self::BUILDING_TYPE[$type];

  }

  private function getCategory(string $type, int $category_id, int $full_building) : string {

    if((bool)$full_building) {

      return 'building'.self::CATEGORY[$category_id];

    }

    return self::CATEGORY_ADS[$type].self::CATEGORY[$category_id];

  }

  private function getPhotos(array $data = []) : string {

   $xml_photo = '';

   foreach($data as $k=>$file_id) {

         $file = \CFile::GetFileArray($file_id);

         $xml_photo.= sprintf("<PhotoSchema><FullUrl>%s%s</FullUrl>%s</PhotoSchema>", 
         self::HOST, $file['SRC'], $k == 0 ? '<IsDefault>true</IsDefault>' : '');


    }

    return $xml_photo;

 }

 private function getVideos(array $data = []) : string {

    $xml_video = '';

    foreach($data as $file_id) {

        $file = \CFile::GetFileArray($file_id);

        $xml_video.= sprintf("<Video><Url>%s%s</Url></Video>", self::HOST, $file['SRC']);


    }

    return  $xml_video;

 }

 private function getAddress(array &$row) : string {

  $city = $this->enumValue((int)$this->$row['UF_CRM_1540202667'], 'UF_CRM_1540202667');

  if($city != self::MOSKOW) {

     $city = $row['UF_CRM_1540202817'];

  }

  if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

    return sprintf("%s, %s %s %s",$city, $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

  }

  return sprintf("%s, %s %s %s", $city, $row['UF_CRM_1540202900'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202908']);

 }

 private function getMetro(int $id, string $metroTime) : string {

   $xml_metro = '';

   $property = \CIBlockElement::GetList(['ID'=>'DESC'],['ID' => $id], false, false, ['PROPERTY_KOD_METRO'])->Fetch();

   if($this->isTransportMetro($metroTime)) {

     $xml_metro.= "<TransportType>transport</TransportType>";

   } else {

     $xml_metro.= "<TransportType>walk</TransportType>";

   }

   $xml_metro.= sprintf("<Time>%d</Time>", (int)$metroTime);

   $xml_metro.= sprintf("<Id>%s</Id>", $property['PROPERTY_KOD_METRO_VALUE']);

   return $xml_metro;


  }
 

}