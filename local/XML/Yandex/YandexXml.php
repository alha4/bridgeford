<?php
namespace XML\Yandex;

use XML\ExportBase;

class YandexXml extends ExportBase {

  protected $fileName = '/yandex_commerc.xml';

  protected static $AGENT_PHONE = '+7(495)127-31-29';

  protected static $AGENT_SITE = 'bridgeford.ru';

  private const TYPE = [

                   '0' => 'аренда',
                   '1' => 'продажа',
                   '2' => 'продажа'

                ];

  private const BUILDING_TYPE = [

                  '74' => 'residential building',
                  '75' => 'detached building',
                  '76' => 'detached building',
                  '77' => 'business center',
                  '78' => 'shopping center',
                  '79' => 'shopping center',
                  '80' => 'warehouse',
                  '81' => 'warehouse'

                ];
               
	private const BUILDING_TYPE_COMERCIAL = [  //значения из тип здания
                   
                 '88' => 'retail',
                 '89' => 'free purpose',
                 '90' => 'office',
                 '91' => 'public catering',
                 '92' => 'retail',
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
                 "151" => 'НДС'
             ];

  private const EMPTY_VAT = 468;

  private const INPUTTYPE = [

                 "96"  =>  "common",
                 "95"  =>  "separate"

               ];

  private const METRO_NOT_SELECT = 159;

  private const NOT_ACTUAL_LOCALITY = 288;

  private const UTILITY_INCLUDE = 282;

  private const NOT_ACTUAL_DISTRICT = 'не актуально';

  /**
   * 
   * bool UF_CRM_1543834597 - Реклама яндекс
   * 
  */

  protected function buildXml() : string {

    $sort   = ["UF_CRM_1545199624" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1543834597" => 1];

    $select = ["OPPORTUNITY","UF_CRM_1540977409431","UF_CRM_1540371261836", "UF_CRM_1540202817",
               "UF_CRM_1540202667","UF_CRM_1540203111","UF_CRM_1540202889","UF_CRM_1540202900",
               "UF_CRM_1540886934", "UF_CRM_1540456473","UF_CRM_1540456608","UF_CRM_1541076330647",
               "UF_CRM_1540532330","UF_CRM_1540471409","UF_CRM_1540384963","UF_CRM_1540385040",
               "UF_CRM_1540385112","UF_CRM_1540301873849","UF_CRM_1541056221","UF_CRM_1540385060",
               "UF_CRM_1543406565","UF_CRM_154020301","UF_CRM_1540384807664","UF_CRM_1540202908",
               "UF_CRM_1540203015","UF_CRM_1540202807","UF_CRM_1540202766","UF_CRM_1540974006",
               "UF_CRM_1544172451","UF_CRM_1544172560","UF_CRM_1552294499136","UF_CRM_1540371938",
              "UF_CRM_1540202817","UF_CRM_1540456737395","UF_CRM_1540384944","UF_CRM_1540392018",
              "UF_CRM_1540456417","UF_CRM_1540554743072","UF_CRM_1540371585","UF_CRM_1541072013901",
              "UF_CRM_1541072151310","UF_CRM_1540371455","UF_CRM_1541055237379","UF_CRM_1544431330",
              "UF_CRM_1541056313","UF_CRM_1540371802","UF_CRM_1545649289833","DATE_CREATE","UF_CRM_1545906357580",
              "UF_CRM_1556017573094","UF_CRM_1559649507"];

    $date_create = gmdate('c');

    $date_update = new \DateTime();
    $date_update->sub(new \DateInterval("P1D"));
    $date_update = $date_update->format("c");

    $xml_string = '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">';

    $xml_string.= sprintf("<generation-date>%s</generation-date>", $date_create);

    $object = \CCrmDeal::GetList($sort, $filter, $select);

    $iter = 0;

    while($row = $object->Fetch()) {

      if(\USE_LIMIT == 'Y' && $iter == \LIMIT) {

          break;

      }

      $iter++;

      $category_id = \CCrmDeal::GetCategoryID($row['ID']);

      $semantic_code = self::SEMANTIC_CODE[$category_id];

      $semantic = (array)$row[$semantic_code];

      $xml_string.= sprintf('<offer internal-id="%s">', $row['ID']);
      $xml_string.= sprintf('<type>%s</type>', self::TYPE[ $category_id ]);
      $xml_string.='<category>commercial</category>';
      $xml_string.='<quality>отличное</quality>';

      if(array_key_exists($row['UF_CRM_1540384807664'], self::BUILDING_TYPE_COMERCIAL)) {

         $xml_string.= sprintf('<commercial-type>%s</commercial-type>', self::BUILDING_TYPE_COMERCIAL[$row['UF_CRM_1540384807664']] );

      }

      if(array_key_exists($row['UF_CRM_1540371261836'], self::BUILDING_TYPE)) {

		  $xml_string.= sprintf('<commercial-building-type>%s</commercial-building-type>', self::BUILDING_TYPE[$row['UF_CRM_1540371261836']] );  // из тип здания

      }

      $date_create = new \DateTime($row['DATE_CREATE']);

      $xml_string.= sprintf('<creation-date>%s</creation-date>', $date_create->format("c"));
      $xml_string.= sprintf('<last-update-date>%s</last-update-date>', $date_update);
 
      if($row['UF_CRM_1540977409431']) {

         $xml_string.= sprintf('<vas>%s</vas>', $this->getVasType($row['UF_CRM_1540977409431']));

      }

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

           $metroTime = $this->enumValue((int)$row['UF_CRM_1540203015'],'UF_CRM_1540203015');

           if($this->isTransportMetro($metroTime)) {

              $xml_string.= sprintf('<time-on-transport>%s</time-on-transport>', (int)$metroTime);

           } else {

              $xml_string.= sprintf('<time-on-foot>%s</time-on-foot>', (int)$metroTime);

           }

        }

        $xml_string.= '</metro>';

      }

      $xml_string.= '</location>';

      $xml_string.= '<sales-agent>';
      $xml_string.= sprintf('<phone>%s</phone>', static::$AGENT_PHONE);
      $xml_string.= '<category>agency</category>';
      $xml_string.= '<organization>Bridgeford Capital</organization>';
      $xml_string.=  sprintf('<url>%s</url>' , static::$AGENT_SITE);
      $xml_string.= '<email>info@bridgeford.ru</email>';
      $xml_string.= '<photo>https://bridgeford.ru/logo.jpg</photo>';
      $xml_string.= '</sales-agent>';

      $xml_string.= '<price>';
      $xml_string.= sprintf('<value>%s</value>', (int)$row['UF_CRM_1545649289833']);
      $xml_string.= sprintf('<currency>%s</currency>', self::CURRENCY[$row['UF_CRM_1540456473']]);

      if($row["UF_CRM_1540456608"] != '' && $row["UF_CRM_1540456608"] != self::EMPTY_VAT) {

         $xml_string.= sprintf('<taxation-form>%s</taxation-form>',  $this->getVatType($row["UF_CRM_1540456608"]));

      }

      $xml_string.= '</price>';

      $xml_string.= '<area>';
      $xml_string.= sprintf('<value>%s</value>', $row['UF_CRM_1541076330647']);
      $xml_string.='<unit>кв. м</unit>';
      $xml_string.= '</area>';

      $xml_string.= $this->getPhotos($row['UF_CRM_1559649507']);

      if($row['UF_CRM_1540371585'] > 0 ) {

         $xml_string.= sprintf('<floors-total>%s</floors-total>', $row['UF_CRM_1540371585']);

      }

      if($row['UF_CRM_1540384963'] > 0) {

         $xml_string.= sprintf('<floor>%s</floor>', $row['UF_CRM_1540384963']);

      }

      $xml_string.= sprintf('<ceiling-height>%s</ceiling-height>', $row['UF_CRM_1540385060']);

      if(in_array($row["UF_CRM_1540385040"], array_keys(self::INPUTTYPE))) {

         $xml_string.= sprintf('<entrance-type>%s</entrance-type>', self::INPUTTYPE[$row["UF_CRM_1540385040"]]);
        
      } else {

        $xml_string.= sprintf('<entrance-type>%s</entrance-type>', self::INPUTTYPE["95"]);

      }

      if($row['UF_CRM_1540385112'] > 0 ) {

         $xml_string.= sprintf('<electric-capacity>%s</electric-capacity>', $row['UF_CRM_1540385112']);

      }


      if($category_id == self::TYPE_DEAL['RENT_BUSSINES']) {

         /**
          * bool UF_CRM_1545906357580 - значение окупаемость в описание 
          * bool UF_CRM_1552294499136 - автотекст в xml
          */
         if((bool)$row['UF_CRM_1545906357580']) {

            $xml_string.= sprintf('<description>АРЕНДНЫЙ БИЗНЕС, ОКУПАЕМОСТЬ %s ПРЯМОЙ КОНТАКТ С СОБСТВЕННИКОМ. %s</description>', 
            strtoupper($row['UF_CRM_1544431330']),
            (bool)$row['UF_CRM_1552294499136'] ?  $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));

         } else {

            $xml_string.= sprintf('<description>%s</description>', 
            (bool)$row['UF_CRM_1552294499136'] ? 
            $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));

         }


      } else {

        $xml_string.= sprintf('<description>%s</description>', (bool)$row['UF_CRM_1552294499136'] ? 
        $this->getDescription($category_id, $semantic, $row) : $this->escapeEntities($row['UF_CRM_1540471409']));

      }

      if($row['UF_CRM_1540301873849']) {
      
         $xml_string.= sprintf('<parking-places>%s</parking-places>', $row['UF_CRM_1540301873849']);

      }

      if($category_id == self::TYPE_DEAL['RENT']) {

        $xml_string.= '<deal-status>direct rent</deal-status>';

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

  private function getVasType(?string $variant = '0') : string {

     return self::SERVICE_TYPE[$variant] ? : '';

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



  private function getVatType(?string $type = ' ') : ?string {

    return self::VATTYPE[$type];

  }

  private function getPhotos(array &$data = []) : string {

    $xml_photo = '';
 
    foreach($data as $file_id) {
      

       $fileSrc = \CFile::GetFileArray($file_id)['SRC'];

       $xml_photo.= sprintf("<image>%s%s</image>", self::HOST,  $fileSrc);
 
 
    }
 
    return $xml_photo;
 
  }
}