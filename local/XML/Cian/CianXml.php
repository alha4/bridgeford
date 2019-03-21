<?php

namespace XML\Cian;

use XML\ExportBase;

final class CianXml extends ExportBase {

  protected $fileName = '/cian_commerc.xml';
  
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

                  '198' => 'free',
                  '199' => 'paid',
                  '200' => 'top3',
                  '201' => 'highlight'

                ];

  private const SERVICE_TYPE_1 = [

                  '202' => 'free',
                  '203' => 'paid',
                  '204' => 'top3',
                  '205' => 'highlight'

                ];

  private const SERVICE_TYPE_2 = [

                  '206' => 'free',
                  '207' => 'paid',
                  '208' => 'top3',
                  '209' => 'highlight'

                ];
  
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

                  '74' => 'officeAndResidentialComplex',
                  '75' => 'administrativeBuilding',
                  '76' => 'mansion',
                  '77' => 'businessCenter',
                  '78' => 'multifunctionalComplex',
                  '79' => 'officeBuilding',
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
               "UF_CRM_1540886934","UF_CRM_1540384807664","UF_CRM_1540384963","UF_CRM_1541076330647", "UF_CRM_1545906357580",
               "UF_CRM_1540371585","UF_CRM_1540385060","OPPORTUNITY","UF_CRM_1540371261836","UF_CRM_1540301873849",
               "UF_CRM_1540456473","UF_CRM_1540456608","UF_CRM_1540381458431","UF_CRM_1540532735882","UF_CRM_1540471409",
               "UF_CRM_1541004853118","UF_CRM_1540385040","UF_CRM_1540385112","UF_CRM_1540532330","UF_CRM_1540532419", "UF_CRM_1540384944",
               "UF_CRM_1544172451","UF_CRM_1540976407661","UF_CRM_1540977227270","UF_CRM_1540977306391","UF_CRM_1544431330",
               "UF_CRM_1540974006","UF_CRM_1544172451","UF_CRM_1544172560","UF_CRM_1552294499136","UF_CRM_1540203015"];

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

      $xml_string.= sprintf("<Category>%s</Category>", $this->getCategory($row['UF_CRM_1540384807664'], $category_id));

      if($category_id == self::RENT_BUSSINES) {

          $xml_string.= sprintf("<Description>%s %s</Description>", $title, $row['UF_CRM_1540471409']);

      } else {
     
          $xml_string.= sprintf("<Description>%s</Description>", $this->getDescription($semantic, $semantic_code, $row['UF_CRM_1540471409'], (bool)$row['UF_CRM_1552294499136']));

      }

      $xml_string.= sprintf("<Address>%s</Address>", $this->getAddress($row));
      $xml_string.= sprintf("<Phones><PhoneSchema><CountryCode>+7</CountryCode><Number>%s</Number></PhoneSchema></Phones>",
                    $this->getPhone((int)$row['UF_CRM_1540886934']));
      $xml_string.= sprintf("<FloorNumber>%s</FloorNumber>",$row['UF_CRM_1540384963']);
      $xml_string.= sprintf("<TotalArea>%s</TotalArea>", $row['UF_CRM_1541076330647']);
      $xml_string.= sprintf("<IsInHiddenBase>%s</IsInHiddenBase>", $row['UF_CRM_1541004853118']);
      $xml_string.= sprintf("<InputType>%s</InputType>", self::INPUTTYPE[$row["UF_CRM_1540385040"]]);
      $xml_string.= sprintf("<Electricity>%s</Electricity>", $row['UF_CRM_1540385112']);

      $xml_string.= sprintf("<PublishTerms><Terms><PublishTermSchema><Services>%s</Services></PublishTermSchema></Terms></PublishTerms>", 
                    $this->getAdsServices($row));

      $xml_string.= "<Photos>";
      $xml_string.= $this->getPhotos((array)$row['UF_CRM_1540532330']);
      $xml_string.= "</Photos>";

      $xml_string.= "<Videos>";
      $xml_string.= $this->getVideos((array)$row['UF_CRM_1540532419']);
      $xml_string.= "</Videos>";

      $xml_string.= '<Building>';
      $xml_string.= sprintf("<FloorsCount>%s</FloorsCount>", $row['UF_CRM_1540371585']);
      $xml_string.= sprintf("<CeilingHeight>%s</CeilingHeight>", $row['UF_CRM_1540385060']);
      $xml_string.= sprintf("<Parking><PlacesCount>%s</PlacesCount></Parking>", $row['UF_CRM_1540301873849']);
      $xml_string.= sprintf("<VatType>%s</VatType>", self::VATTYPE[$row["UF_CRM_1540456608"]]);
      $xml_string.= sprintf("<Land><Type>%s</Type></Land>", self::AREATYPE[$row['UF_CRM_1540381458431']]);
      $xml_string.= '</Building>';

      $xml_string.= sprintf("<BargainTerms><Price>%s</Price>", (int)$row['OPPORTUNITY']);
      $xml_string.= sprintf("<Currency>%s</Currency>", self::CURRENCY[$row['UF_CRM_1540456473']]);
      $xml_string.= sprintf("<Type>%s</Type>", self::BUILDING_TYPE[$row['UF_CRM_1540371261836']]);
      $xml_string.= sprintf("<AgentBonus><Value>%s</Value><PaymentType>percent</PaymentType></AgentBonus></BargainTerms>",  
                    $row['UF_CRM_1540532735882']);

      $xml_string.= '</object>';

    }

    $xml_string.= '</feed>';

    return $xml_string;

  }

  private function getTitle(array $row, int $category_id) : string {

    $square = ($category_id == self::RENT_BUSSINES) ? $row['UF_CRM_1541076330647'] : $row['UF_CRM_1540384944'];

    $region = $this->enumValue((int)$row['UF_CRM_1540203111'],'UF_CRM_1540203111');
    $region.= ', ';
    
    switch($category_id) {

      case self::RENT :

      return strtoupper(sprintf("%s, %s %s метров",  self::TITLE_ALIAS_SYNONYM[$category_id],$region, $square));

      break;

      case self::SALE :

      return strtoupper(sprintf("%s, %s %s метров",  self::TITLE_ALIAS_SYNONYM[$category_id], $region, $square));

      break;

      case self::RENT_BUSSINES :

      if($row['UF_CRM_1545906357580']) {

          return strtoupper(sprintf("%s, окупаемость - %s", self::TITLE_ALIAS[$category_id],  $row['UF_CRM_1544431330']));

      }

      return strtoupper(sprintf("%s", self::TITLE_ALIAS[$category_id]));

      break;

    }

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

  private function getCategory(string $type, int $category_id) : string {

    return self::CATEGORY_ADS[$type].self::CATEGORY[$category_id];

  }

  private function getPhotos(array $data = []) : string {

   $xml_photo = '';

   foreach($data as $file_id) {

         $file = \CFile::GetFileArray($file_id);

         $xml_photo.= sprintf("<PhotoSchema><FullUrl>%s%s</FullUrl><IsDefault>true</IsDefault></PhotoSchema>", self::HOST, $file['SRC']);


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

 private function getAddress(array $row) : string {

    if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

        return sprintf("%s,%s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

    }

    return sprintf("%s, %s-й %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202908'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202900']);

  }

}