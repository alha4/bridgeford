<?php

namespace XML\Cian;

use XML\ExportBase;

final class CianXml extends ExportBase {

  protected $fileName = '/cian_commerc.xml';

  private const HOST = 'https://crm.bridgeford.ru';

  private const CATEGORY = [

                   '0' => 'Rent',
                   '1' => 'Sale',
                   '2' => 'Sale'
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
                 '81' => 'warehouseComplex',
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
                   "95"  =>  "separateFromStreet",
                ];

  private const AREATYPE = [

                  "86" => "owned",
                  "87" => "rent"

                ];

  private const DEFAULT_CITY = 'Москва';

  private const STREET_TYPE = 37;

  protected function buildXml() : string {

    $sort = ["UF_CRM_1545199624" => "DESC"];

    $filter = ["CHECK_PERMISSION" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1543837331299" => 1];

    $select = ["UF_CRM_1540202817","UF_CRM_1540202900","UF_CRM_1540202889","UF_CRM_1540202908",
               "UF_CRM_1540886934","UF_CRM_1540384807664","UF_CRM_1540384963","UF_CRM_1541076330647",
               "UF_CRM_1540371585","UF_CRM_1540385060","OPPORTUNITY","UF_CRM_1540371261836","UF_CRM_1540301873849",
               "UF_CRM_1540456473","UF_CRM_1540456608","UF_CRM_1540381458431","UF_CRM_1540532735882","UF_CRM_1540471409",
               "UF_CRM_1541004853118","UF_CRM_1540385040","UF_CRM_1540385112","UF_CRM_1540532330","UF_CRM_1540532419","UF_CRM_1544172451"];

    $xml_string = '<feed><feed_version>2</feed_version>';

    $object = \CCrmDeal::GetList($sort, $filter, $select);

    while($row = $object->Fetch()) {

      $xml_string.= sprintf("<object><ExternalId>%s</ExternalId>", $row['ID']);
      $xml_string.= sprintf("<Category>%s</Category>", $this->getCategory($row['UF_CRM_1540384807664'], \CCrmDeal::GetCategoryID($row['ID'])));
      $xml_string.= sprintf("<Description>%s</Description>", $row['UF_CRM_1540471409']);
      $xml_string.= sprintf("<Address>%s</Address>", $this->getAddress($row));
      $xml_string.= sprintf("<Phones><PhoneSchema><CountryCode>+7</CountryCode><Number>%s</Number></PhoneSchema></Phones>",$this->getPhone((int)$row['UF_CRM_1540886934']));
      $xml_string.= sprintf("<FloorNumber>%s</FloorNumber>",$row['UF_CRM_1540384963']);
      $xml_string.= sprintf("<TotalArea>%s</TotalArea>",$row['UF_CRM_1541076330647']);
      $xml_string.= sprintf("<IsInHiddenBase>%s</IsInHiddenBase>", $row['UF_CRM_1541004853118']);
      $xml_string.= sprintf("<InputType>%s</InputType>", $this->getInputType($row["UF_CRM_1540385040"]));
      $xml_string.= sprintf("<Electricity>%s</Electricity>", $row['UF_CRM_1540385112']);

      $xml_string.= "<Photos>";
      $xml_string.= $this->getPhotos($row['UF_CRM_1540532330']);
      $xml_string.= "</Photos>";

      $xml_string.= "<Videos>";
      $xml_string.= $this->getVideos($row['UF_CRM_1540532419']);
      $xml_string.= "</Videos>";


      $xml_string.= sprintf("<Building><FloorsCount>%s</FloorsCount>", $row['UF_CRM_1540371585']);
      $xml_string.= sprintf("<CeilingHeight>%s</CeilingHeight>", $row['UF_CRM_1540385060']);
      $xml_string.= sprintf("<Parking><PlacesCount>%s</PlacesCount></Parking>", $row['UF_CRM_1540301873849']);
      $xml_string.= sprintf("<VatType>%s</VatType>", $this->getVatType($row["UF_CRM_1540456608"]));
      $xml_string.= sprintf("<Land><Type>%s</Type></Land>", $this->getTypeArea($row['UF_CRM_1540381458431']));
      //$xml_string.= sprintf("<Infrastructure>%s</Infrastructure>", $this->getInfrastructure($row['UF_CRM_1544172451']));
      $xml_string.= '</Building>';

      $xml_string.= sprintf("<BargainTerms><Price>%s</Price><Currency>%s</Currency><Type>%s</Type><AgentBonus><Value>%s</Value><PaymentType>percent</PaymentType></AgentBonus></BargainTerms>", 
      (int)$row['OPPORTUNITY'], 
      $this->getCurrency($row['UF_CRM_1540456473']),
      $this->getBuildingType($row['UF_CRM_1540371261836']),
      $row['UF_CRM_1540532735882']);


      $xml_string.= '</object>';

    }

    $xml_string.= '</feed>';

    return $xml_string;


  }

  private function getCategory(string $type, int $category_id) : string {

    return self::CATEGORY_ADS[$type].self::CATEGORY[$category_id];

  }

  private function getCurrency(string $currency_id) : string {

    return self::CURRENCY[$currency_id];

  }

  private function getBuildingType(string $type) : string {

    return self::BUILDING_TYPE[$type];

  }

  private function getVatType(string $type = ' ') : string {

    return self::VATTYPE[$type];

  }

  private function getTypeArea(string $type) : string {

    return self::AREATYPE[$type];

  }

  private function getInputType(string $type) : string {
 
     
     return self::INPUTTYPE[$type];

  }

  private function getInfrastructure(array $data) : string {





  }

  private function getPhotos(array $data) : string {

     foreach($data as $file_id) {

         $file = \CFile::GetFileArray($file_id);

         $xml_photo.= sprintf("<PhotoSchema><FullUrl>%s%s</FullUrl><IsDefault>true</IsDefault></PhotoSchema>", self::HOST, $file['SRC']);


     }

     return $xml_photo;

  }

  private function getVideos(array $data) : string {

    foreach($data as $file_id) {

        $file = \CFile::GetFileArray($file_id);

        $xml_video.= sprintf("<Video><Url>%s%s</Url></Video>", self::HOST, $file['SRC']);


    }

    return  $xml_video;

 }

  private function getPhone(int $user_id = 1) : string {

    $order = array('id' => 'asc');
    $sort = 'id';
    $user_id = 1;

    $filter = array("ID" => $user_id);

    $rsUsers = \CUser::GetList($order, $sort, $filter, ["SELECT" => array("PERSONAL_PHONE") ]);


    return $rsUsers->Fetch()['PERSONAL_PHONE'];


  }

  private function getAddress(array $row) : string {

    if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

        return sprintf("%s,%s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

    }

    return sprintf("%s, %s-й %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202908'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202900']);

  }

  private function enumValue(int $value_id, string $code) : string {

    $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_DEAL", "FIELD_NAME" => $code));
    $entity = $entityResult->Fetch();
    $enumResult = \CUserTypeEnum::GetList($entity);
 
    while($enum =  $enumResult->GetNext()) {

        if($enum['ID'] == $value_id) {

           return $enum['VALUE'];

        }

    }

    return '';

  }


}