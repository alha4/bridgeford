<?php
namespace XML\Avito;

use XML\ExportBase;

final class AvitoXml extends ExportBase {

  protected $fileName = '/avito_commerc.xml';

  private const ADS_TYPE = [

                '0' => 'Сдам',
                '1' => 'Продам',
                '2' => 'Сдам'

               ];

  private const OBJECT_TYPE = [
                   
                '88' => 'Торговое помещение',
                '89' => 'Помещение свободного назначения',
                '90' => 'Офисное помещение',
                '91' => 'Помещение общественного питания',
                '92' => 'Помещение свободного назначения',
                '93' => 'Производственное помещение',
                '94' => 'Складское помещение'
             
               ];

  private const RENT_TYPE = [

                '0' => 'На длительный срок',
                '1' => 'Посуточно'

                ];


  /**
   * bool UF_CRM_1543834582 - Реклама авито
   */
  protected function buildXml() : string {

    $sort   = ["UF_CRM_1543834582" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1543834582" => 1];

    $select = ["OPPORTUNITY","UF_CRM_1540886934","ASSIGNED_BY_ID","UF_CRM_1540202817",
               "UF_CRM_1540202908","UF_CRM_1540202889","UF_CRM_1540202900","UF_CRM_1540471409",
               "UF_CRM_1540381545640","UF_CRM_1540384944","UF_CRM_1540384807664","UF_CRM_1541056338255",
               "UF_CRM_1540532735882","UF_CRM_1540532330","UF_CRM_1540384963","UF_CRM_1540371585","UF_CRM_1541076330647",
               "UF_CRM_1540974006","UF_CRM_1544172451","UF_CRM_1544172560","UF_CRM_1552294499136","UF_CRM_1540203015",
               "UF_CRM_1540385060","UF_CRM_1540385040","UF_CRM_1540385112","UF_CRM_1540392018","UF_CRM_1540456417",
               "UF_CRM_1540554743072","UF_CRM_1540371261836","UF_CRM_1540384916112","UF_CRM_1540456737395","UF_CRM_1543406565",
               "UF_CRM_1540203015","UF_CRM_1540202667","UF_CRM_1540203111","UF_CRM_1540371938","UF_CRM_1541072013901",
               "UF_CRM_1541072151310","UF_CRM_1540371455","UF_CRM_1552493240038","UF_CRM_1540456608","UF_CRM_1541055237379",
               "UF_CRM_1544431330","UF_CRM_1541056313","UF_CRM_1540371802","UF_CRM_1555070914"];
    
    $object = \CCrmDeal::GetList($sort, $filter, $select);

    $xml_string.= '<Ads formatVersion="3" target="Avito.ru">';

    while($row = $object->Fetch()) {

      $category_id = \CCrmDeal::GetCategoryID($row['ID']);

      $semantic_code = self::SEMANTIC_CODE[$category_id];

      $semantic = (array)$row[$semantic_code];

      $xml_string.= '<Ad>';

      $xml_string.= sprintf('<Id>%s</Id>', $row['ID']);

      $xml_string.= '<ListingFee>PackageSingle</ListingFee>';

      $xml_string.= sprintf('<AdStatus>%s</AdStatus>','Free');

      $xml_string.= '<AllowEmail>Да</AllowEmail>';

      $xml_string.= sprintf('<ManagerName>%s</ManagerName>', $this->getUserFullName($row['ASSIGNED_BY_ID']));

      $xml_string.= '<PropertyRights>Посредник</PropertyRights>';

      $xml_string.= sprintf('<ContactPhone>+7%s</ContactPhone>', $this->getPhone($row["ASSIGNED_BY_ID"]));

      $xml_string.= sprintf('<Address>%s</Address>', $this->getAddress($row));

      $xml_string.= sprintf('<Description>%s</Description>', (bool)$row['UF_CRM_1552294499136'] ? 
      $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));
     
      $xml_string.= '<Category>Коммерческая недвижимость</Category>';

      $xml_string.= sprintf('<OperationType>%s</OperationType>', self::ADS_TYPE[$category_id]);

      $xml_string.= sprintf('<Price>%s</Price>', (int)$row['OPPORTUNITY']);

      $xml_string.= sprintf('<ObjectType>%s</ObjectType>', self::OBJECT_TYPE[$row['UF_CRM_1540384807664']]);

      $xml_string.= sprintf('<Floor>%s</Floor>',$row['UF_CRM_1540384963']);
      $xml_string.= sprintf('<Floors>%s</Floors>',$row['UF_CRM_1540371585']);

      $xml_string.= '<Images>';
      $xml_string.= $this->getPhotos((array)$row['UF_CRM_1540532330']);
      $xml_string.= '</Images>';

      if($category_id == self::TYPE_DEAL['RENT_BUSSINES']) {

          $xml_string.= sprintf('<Square>%s</Square>', $row['UF_CRM_1540381545640']);


      } else {

         $xml_string.= sprintf('<Square>%s</Square>', $row['UF_CRM_1540384944']);

      }

      $xml_string.= '<LeaseDeposit>Без залога</LeaseDeposit>';
      
      $xml_string.= sprintf('<LeaseType>%s</LeaseType>', self::RENT_TYPE[$row['UF_CRM_1541056338255']]);

      $xml_string.= sprintf('<LeaseCommissionSize>%s</LeaseCommissionSize>', $row['UF_CRM_1540532735882']);
      
      $xml_string.='</Ad>';

    }
  
    $xml_string.= '</Ads>';

    return $xml_string;

  }

  private function getPhotos(array $data = []) : string {

    $xml_photo = '';
 
    foreach($data as $file_id) {
 
       $file = \CFile::GetFileArray($file_id);
 
       $xml_photo.= sprintf('<Image url="%s%s"></Image>', self::HOST, $file['SRC']);
 
    }
 
    return $xml_photo;
 
  }

  protected function getAddress(array $row) : string {

    if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

        return sprintf("Россия, %s, %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

    }

    return sprintf("Россия, %s, %s-й %s %s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202908'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202900']);

  }
}