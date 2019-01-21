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

  
  private const CATEGORY_ADS = [

                  '88' => 'shoppingArea',
                  '89' => 'freeAppointmentObject',
                  '90' => 'office',
                  '91' => 'freeAppointmentObject',
                  '92' => 'freeAppointmentObject',
                  '93' => 'industry',
                  '94' => 'warehouse'
                ];

  private const DEFAULT_CITY = 'Москва';

  private const STREET_TYPE = 37;

  protected function buildXml() : string {

    $sort = ["UF_CRM_1545199624" => "DESC"];

    $filter = ["CHECK_PERMISSION" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT];

    $select = ["UF_CRM_1540202817","UF_CRM_1540202900","UF_CRM_1540202889","UF_CRM_1540202908",
               "UF_CRM_1540886934","UF_CRM_1540384807664","UF_CRM_1540384963","UF_CRM_1541076330647",
               "UF_CRM_1540371585","UF_CRM_1540385060","OPPORTUNITY"];

    $xml_string = '<feed><feed_version>2</feed_version>';

    $object = \CCrmDeal::GetList($sort, $filter, $select);

    while($row = $object->Fetch()) {

      $xml_string.= sprintf("<object><ExternalId>%s</ExternalId>", $row['ID']);
      $xml_string.= sprintf("<Category>%s</Category>", $this->getCategory($row['UF_CRM_1540384807664'], \CCrmDeal::GetCategoryID($row['ID'])));
      $xml_string.= sprintf("<Description>%s</Description>", "Описание");
      $xml_string.= sprintf("<Address>%s</Address>", $this->getAddress($row));
      $xml_string.= sprintf("<Phones><PhoneSchema><CountryCode>+7</CountryCode><Number>%s</Number></PhoneSchema></Phones>",$this->getPhone((int)$row['UF_CRM_1540886934']));
      $xml_string.= sprintf("<FloorNumber>%s</FloorNumber>",$row['UF_CRM_1540384963']);
      $xml_string.= sprintf("<TotalArea>%s</TotalArea>",$row['UF_CRM_1541076330647']);

      $xml_string.= sprintf("<Building><FloorsCount>%s</FloorsCount>", $row['UF_CRM_1540371585']);
      $xml_string.= sprintf("<CeilingHeight>%s</CeilingHeight>", $row['UF_CRM_1540385060']);
      $xml_string.= '</Building>';

      $xml_string.= sprintf("<BargainTerms><Price>%s</Price></BargainTerms>", (int)$row['OPPORTUNITY']);


      $xml_string.= '</object>';

    }

    $xml_string.= '</feed>';

    return $xml_string;


  }

  private function getCategory(string $type, int $category_id) : string {

    return self::CATEGORY_ADS[$type].self::CATEGORY[$category_id];

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