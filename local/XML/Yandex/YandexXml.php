<?php
namespace XML\Yandex;

use XML\ExportBase;

final class YandexXml extends ExportBase {

  protected $fileName = '/yandex_commerc.xml';

  private const TYPE = [

                   '0' => 'аренда',
                   '1' => 'продажа',
                   '2' => 'аренда'

                ];

  private const BUILDING_TYPE = [

                  '74' => 'hotel',
                  '75' => 'free purpose',
                  '76' => 'free purpose',
                  '77' => 'business',
                  '78' => 'retail',
                  '79' => 'office',
                  '80' => 'manufacturing',
                  '81' => 'warehouse',
                   '0' => 'free purpose'
                ];
               
  private const BUILDING_TYPE_COMERCIAL = [
                   
                 '88' => 'retail',
                 '89' => 'free purpose',
                 '90' => 'office',
                 '91' => 'public catering',
                 '92' => 'free purpose',
                 '93' => 'manufacturing',
                 '94' => 'warehouse'
              
                ];


  private const SERVICE_TYPE = [

                  '210' => 'premium',
                  '211' => 'raise',
                  '212' => 'promotion'

  ];

  private const CURRENCY =  [

                  "144" => 'RUB',
                  "145" => 'USD',
                  "146" => 'EUR'

               ];

  private const VATTYPE = [

                 "150" => 'УСН',
                 "151" => 'НДС',
                 " "   => 'БЕЗ НДС'
             ];

  private const INPUTTYPE = [

                 "96"  =>  "common",
                 "95"  =>  "separate"

               ];

  private const METRO_NOT_SELECT = 159;

  private const NOT_ACTUAL_LOCALITY = 288;

  private const UTILITY_INCLUDE = 282;

  private const NOT_ACTUAL_DISTRICT = 'не актуально';

  protected function buildXml() : string {

    $sort   = ["UF_CRM_1545199624" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1543834597" => 1];

    $select = ["OPPORTUNITY","UF_CRM_1540977409431","UF_CRM_1540371261836", "UF_CRM_1540202817",
               "UF_CRM_1540202667","UF_CRM_1540203111","UF_CRM_1540202889","UF_CRM_1540202900",
               "UF_CRM_1540886934", "UF_CRM_1540456473","UF_CRM_1540456608","UF_CRM_1541076330647",
               "UF_CRM_1540532330","UF_CRM_1540471409","UF_CRM_1540384963","UF_CRM_1540385040",
               "UF_CRM_1540385112","UF_CRM_1540301873849","UF_CRM_1541056221","UF_CRM_1540385060",
               "UF_CRM_1543406565","UF_CRM_154020301","UF_CRM_1540384807664","UF_CRM_1540202908",
               "UF_CRM_1540203015","UF_CRM_1540202807","UF_CRM_1540202766"];

    $date_create = date(\DATE_ISO8601);

    $xml_string = '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">';

    $xml_string.= sprintf("<generation-date>%s</generation-date>", $date_create);

    $object = \CCrmDeal::GetList($sort, $filter, $select);

    while($row = $object->Fetch()) {

      $xml_string.= sprintf('<offer internal-id="%s">', $row['ID']);
      $xml_string.= sprintf('<type>%s</type>', self::TYPE[ \CCrmDeal::GetCategoryID($row['ID']) ]);
      $xml_string.='<category>commercial</category>';
      $xml_string.= sprintf('<commercial-type>%s</commercial-type>', self::BUILDING_TYPE_COMERCIAL[$row['UF_CRM_1540384807664']] );
      $xml_string.= sprintf('<commercial-building-type>%s</commercial-building-type>', $this->getBuildingType($row['UF_CRM_1540371261836']) );
      $xml_string.= sprintf('<creation-date>%s</creation-date>', $date_create);
      $xml_string.= sprintf('<last-update-date>%s</last-update-date>', $date_create);
 
      $xml_string.= sprintf('<vas>%s</vas>', $this->getVasType($row['UF_CRM_1540977409431']));

      $xml_string.= '<location><country>Россия</country>';

      if($row['UF_CRM_1540202766'] != self::NOT_ACTUAL_DISTRICT) {
     
          $xml_string.= sprintf('<district>%s</district>', $row['UF_CRM_1540202766']);

      }

      $xml_string.= sprintf('<region>%s</region>', $this->enumValue( (int)$row['UF_CRM_1540202667'], 'UF_CRM_1540202667') );
      $xml_string.= sprintf('<locality-name>%s</locality-name>', $row['UF_CRM_1540202817']);
      $xml_string.= sprintf('<sub-locality-name>%s</sub-locality-name>', $this->enumValue((int)$row['UF_CRM_1540203111'],'UF_CRM_1540203111'));
      $xml_string.= sprintf('<address>%s</address>', $this->getAddress($row));

      if($row['UF_CRM_1540202807'] && $row['UF_CRM_1540202807'] != self::NOT_ACTUAL_LOCALITY) {

         $xml_string.= sprintf('<locality-name>%s</locality-name>', $this->enumValue((int)$row['UF_CRM_1540202807'],'UF_CRM_1540202807'));

      }
  
      if($row['UF_CRM_1543406565'] != self::METRO_NOT_SELECT) {

        $xml_string.= '<metro>';
       
        $xml_string.= sprintf('<name>%s</name>', $this->IblockEnumValue($row['UF_CRM_1543406565']));

        if($row['UF_CRM_1540203015'] > 0) {

           $xml_string.= sprintf('<time-on-foot>%s</time-on-foot>', $this->enumValue((int)$row['UF_CRM_1540203015'],'UF_CRM_1540203015'));

        }

        $xml_string.= '</metro>';

      }

      $xml_string.= '</location>';

      $xml_string.= '<sales-agent>';
      $xml_string.= sprintf('<phone>+7%s</phone>', $this->getPhone((int)$row['UF_CRM_1540886934']));
      $xml_string. '<category>agency</category>';
      $xml_string. '<organization>bridgeford</organization>';
      $xml_string.= '</sales-agent>';

      $xml_string.= '<price>';
      $xml_string.= sprintf('<value>%s</value>', (int)$row['OPPORTUNITY']);
      $xml_string.= sprintf('<currency>%s</currency>', $this->getCurrency($row['UF_CRM_1540456473']));
      $xml_string.= sprintf('<taxation-form>%s</taxation-form>',  $this->getVatType($row["UF_CRM_1540456608"]));
      $xml_string.= '</price>';

      $xml_string.= '<area>';
      $xml_string.= sprintf('<value>%s</value>', $row['UF_CRM_1541076330647']);
      $xml_string. '<unit>кв. м</unit>';
      $xml_string.= '</area>';

      $xml_string.= $this->getPhotos((array)$row['UF_CRM_1540532330']);

      $xml_string.= sprintf('<floors-total>%s</floors-total>', $row['UF_CRM_1540384963']);
      $xml_string.= sprintf('<floor>%s</floor>', $row['UF_CRM_1540384963']);
      $xml_string.= sprintf('<ceiling-height>%s</ceiling-height>', $row['UF_CRM_1540385060']);
      $xml_string.= sprintf('<entrance-type>%s</entrance-type>', $this->getInputType($row["UF_CRM_1540385040"]));
      $xml_string.= sprintf('<electric-capacity>%s</electric-capacity>', $row['UF_CRM_1540385112']);
      $xml_string.= sprintf('<description>%s</description>', $row['UF_CRM_1540471409']);
      $xml_string.= sprintf('<parking-places>%s</parking-places>', $row['UF_CRM_1540301873849']);

      if(\CCrmDeal::GetCategoryID($row['ID']) == 0 || \CCrmDeal::GetCategoryID($row['ID']) == 1) {

        $xml_string.= '<deal-status>subrent</deal-status>';

        if($row['UF_CRM_1541056221'] > 0) {

            $xml_string.= sprintf('<utilities-included>%s</utilities-included>', $this->getUnilities($row['UF_CRM_1541056221']));

        }

      }

      $xml_string.= '</offer>';
    
      }

      $xml_string.= '</realty-feed>';

      return $xml_string;

  }

  private function getUnilities(string $value) : bool {
   
     return $value == self::UTILITY_INCLUDE ? 1 : 0;

  }

  private function getVasType(?string $varian = '0') : string {

     return self::SERVICE_TYPE[$varian] ? : self::SERVICE_TYPE['212'];

  }

  private function getAddress(array $row) : string {

    if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

      return sprintf("%s %s", $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

    }

    return sprintf("%s-й %s %s", $row['UF_CRM_1540202908'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202900']);

  }

  private function getCurrency(string $currency_id) : string {

    return self::CURRENCY[$currency_id];

  }

  private function getVatType(string $type = ' ') : string {

    return self::VATTYPE[$type];

  }

  private function getPhotos(array $data = []) : string {

    $xml_photo = '';
 
    foreach($data as $file_id) {
 
          $file = \CFile::GetFileArray($file_id);
 
          $xml_photo.= sprintf("<image>%s%s</image>", self::HOST, $file['SRC']);
 
 
      }
 
      return $xml_photo;
 
  }

  private function getInputType(string $type) : string {
 
    return self::INPUTTYPE[$type];

  }

  private function getBuildingType(string $type) : string {

    return self::BUILDING_TYPE[$type];

  }

}