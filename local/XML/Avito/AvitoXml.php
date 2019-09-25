<?php
namespace XML\Avito;

use XML\ExportBase;

final class AvitoXml extends ExportBase {

  protected $fileName = '/avito_commerc.xml';

  private const PHONE_NUMBER = '4951545354';

  private const COUNTRY = 'Россия';

  private const MOSKOW_REGION = 'Московская область';

  private const SUBMOSKOW = 'Подмосковье';

  private const NEWMOSKOW = 'Новая Москва';

  private const NOT_ACTUAL = 'не актуально';

  private const ADS_TYPE = [

                '0' => 'Сдам',
                '1' => 'Продам',
                '2' => 'Продам'

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

    $filter = ["CHECK_PERMISSIONS" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1543834582" => 1,"UF_CRM_1541572359657" => 1];

    $select = ["OPPORTUNITY","UF_CRM_1540886934","ASSIGNED_BY_ID","UF_CRM_1540202817",
               "UF_CRM_1540202908","UF_CRM_1540202889","UF_CRM_1540202900","UF_CRM_1540471409",
               "UF_CRM_1540381545640","UF_CRM_1540384944","UF_CRM_1540384807664","UF_CRM_1541056338255",
               "UF_CRM_1540532735882","UF_CRM_1540532330","UF_CRM_1540384963","UF_CRM_1540371585","UF_CRM_1541076330647",
               "UF_CRM_1540974006","UF_CRM_1544172451","UF_CRM_1544172560","UF_CRM_1552294499136","UF_CRM_1540203015",
               "UF_CRM_1540385060","UF_CRM_1540385040","UF_CRM_1540385112","UF_CRM_1540392018","UF_CRM_1540456417",
               "UF_CRM_1540554743072","UF_CRM_1540371261836","UF_CRM_1540384916112","UF_CRM_1540456737395","UF_CRM_1543406565",
               "UF_CRM_1540203015","UF_CRM_1540202667","UF_CRM_1540203111","UF_CRM_1540371938","UF_CRM_1541072013901",
               "UF_CRM_1541072151310","UF_CRM_1540371455","UF_CRM_1552493240038","UF_CRM_1540456608","UF_CRM_1541055237379",
               "UF_CRM_1544431330","UF_CRM_1541056313","UF_CRM_1540371802","UF_CRM_1555070914", "UF_CRM_1559649507", "UF_CRM_1545649289833", "UF_CRM_1545906357580", "UF_CRM_1556017573094", "UF_CRM_1540202807"];
    
    $object = \CCrmDeal::GetList($sort, $filter, $select);

    $xml_string.= '<Ads formatVersion="3" target="Avito.ru">';

    while($row = $object->Fetch()) {

      $category_id = \CCrmDeal::GetCategoryID($row['ID']);

      $semantic_code = self::SEMANTIC_CODE[$category_id];

      $semantic = (array)$row[$semantic_code];

      $xml_string.= '<Ad>';

      $xml_string.= sprintf('<Id>%s</Id>', $row['ID']);

      $xml_string.= '<ListingFee>Package</ListingFee>';

		// $xml_string.= sprintf('<AdStatus>%s</AdStatus>','Free');

      $xml_string.= '<AllowEmail>Да</AllowEmail>';

		// $xml_string.= sprintf('<ManagerName>%s</ManagerName>', $this->getUserFullName($row['ASSIGNED_BY_ID']));

      $xml_string.= '<PropertyRights>Собственник</PropertyRights>';

      $xml_string.= sprintf('<ContactPhone>+7%s</ContactPhone>', self::PHONE_NUMBER);

      $xml_string.= sprintf('<Address>%s</Address>', $this->getAddress($row));

      $title = $this->getTitle($row, $category_id);

      $xml_string.= sprintf("<Title>%s</Title>", $title);

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

		//   $xml_string.= sprintf('<Description>%s</Description>', (bool)$row['UF_CRM_1552294499136'] ? 
		//   $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));
     
      $xml_string.= '<Category>Коммерческая недвижимость</Category>';

      $xml_string.= sprintf('<OperationType>%s</OperationType>', self::ADS_TYPE[$category_id]);

      $xml_string.= sprintf('<Price>%s</Price>', (int)$row['UF_CRM_1545649289833']);

      $xml_string.= sprintf('<ObjectType>%s</ObjectType>', self::OBJECT_TYPE[$row['UF_CRM_1540384807664']]);

      $xml_string.= sprintf('<Floor>%s</Floor>',$row['UF_CRM_1540384963']);
      $xml_string.= sprintf('<Floors>%s</Floors>',$row['UF_CRM_1540371585']);

      $xml_string.= '<Images>';
		$xml_string.= $this->getPhotos((array)$row['UF_CRM_1559649507']); // с вотермарками 'UF_CRM_1559649507' без 'UF_CRM_1540532330'
      $xml_string.= '</Images>';

      if($category_id == self::TYPE_DEAL['RENT_BUSSINES']) {

          $xml_string.= sprintf('<Square>%s</Square>', $row['UF_CRM_1540384944']);


      } else {

         $xml_string.= sprintf('<Square>%s</Square>', $row['UF_CRM_1540384944']);

      }

      if($category_id == self::TYPE_DEAL['RENT']) {

          $xml_string.= '<LeaseDeposit>Без залога</LeaseDeposit>';

	  }

		//  $xml_string.= '<LeaseDeposit>Без залога</LeaseDeposit>';

		//  $xml_string.= sprintf('<LeaseType>%s</LeaseType>', self::RENT_TYPE[$row['UF_CRM_1541056338255']]);

		//  $xml_string.= sprintf('<LeaseCommissionSize>%s</LeaseCommissionSize>', $row['UF_CRM_1540532735882']);
      
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


private function getTitle(array $row, int $category_id) : string {

    $square = ($category_id == self::RENT_BUSSINES) ? $row['UF_CRM_1541076330647'] : $row['UF_CRM_1540384944'];

    $region = $this->enumValue((int)$row['UF_CRM_1540203111'],'UF_CRM_1540203111');
    $region.= ', ';
    
    switch($category_id) {

      case self::RENT :

      return sprintf("%s, %s %s",  self::TITLE_ALIAS_SYNONYM[$category_id],$region, $this->getMetre($square));

      break;

      case self::SALE :

      return sprintf("%s, %s %s",  self::TITLE_ALIAS_SYNONYM[$category_id], $region, $this->getMetre($square));

      break;

      case self::RENT_BUSSINES :

      if($row['UF_CRM_1545906357580']) {

          return sprintf("%s, окупаемость - %s", self::TITLE_ALIAS[$category_id],  $row['UF_CRM_1544431330']);

      }

      return sprintf("%s", self::TITLE_ALIAS[$category_id]);

      break;

    }

  } 
  
  /**
   * UF_CRM_1540202900 - улица
   * UF_CRM_1540202908 - дом
   * 
   */

  private function getAddress(array &$row) : string {

  $region = $this->enumValue((int)$row['UF_CRM_1540202667'], 'UF_CRM_1540202667');

  $cityType = $this->enumValue((int)$row['UF_CRM_1540202807'],'UF_CRM_1540202807');

  $streetType = $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889');

  if($cityType == self::NOT_ACTUAL) {
     $cityType = '';
  }

  if($streetType  == self::NOT_ACTUAL) {
     $streetType  = '';
  }

  $city = $region;

  if($city != self::MOSKOW) {

     $city = $row['UF_CRM_1540202817'];

  }

  if($region == self::SUBMOSKOW) {

   if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {
    
      return sprintf("%s, %s, %s %s, %s %s %s",
        self::COUNTRY, self::MOSKOW_REGION, $cityType, $city, $streetType, 
        $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']
      );

   }

   return sprintf("%s, %s, %s %s, %s %s %s",
            self::COUNTRY, self::MOSKOW_REGION, $cityType, $city, $row['UF_CRM_1540202900'], 
            $streetType, $row['UF_CRM_1540202908']
          );

  }

  if($region == self::NEWMOSKOW) {

   if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

     return sprintf("%s, %s, %s %s, %s %s %s",
        self::COUNTRY, self::MOSKOW, $cityType, $city, $streetType, 
        $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']
      );
   }

   return sprintf("%s, %s, %s %s, %s %s %s",
           self::COUNTRY, self::MOSKOW, $cityType, $city, $row['UF_CRM_1540202900'], 
           $streetType, $row['UF_CRM_1540202908']
        );

  }
 
  if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

   return sprintf("%s, %s, %s %s %s",
       self::COUNTRY, $city, $streetType, 
       $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']
    );

  }

  return sprintf("%s, %s, %s %s %s",
           self::COUNTRY, $city, $row['UF_CRM_1540202900'],
           $streetType, $row['UF_CRM_1540202908']
         );
 }
}