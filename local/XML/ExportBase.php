<?php
namespace XML;

abstract class ExportBase {

   use \XML\Helpers\CrmHelper;

   protected $fileName;

   protected const RENT = 0;

   protected const SALE = 1;

   protected const RENT_BUSSINES = 2;

   protected const STATUS_OBJECT = 357;

   protected const STREET_TYPE = 37;

   protected const TITLE_ALIAS = [

      '0' => 'Аренда помещения',
      '1' => 'Помещение на продажу',
      '2' => 'Арендный бизнес'

    ];


    protected const TITLE_ALIAS_SYNONYM = [

      '0' => 'Помещение в аренду',
      '1' => 'Продажа помещения'

    ];

   protected const TYPE_DEAL = [

      'RENT' => 0,
      'SALE' => 1,
      'RENT_BUSSINES' => 2 

   ];

   protected const HOST = 'https://crm.bridgeford.ru';
 
   abstract protected function buildXml() : string;

   public function export() : void {

     $xml = new \DomDocument("1.0","UTF-8");

     $xml->encoding = 'UTF-8';
     $xml->preserveWhiteSpace = false;
     $xml->formatOutput = true;
     $xml->validateOnParse = true;

     $xml->loadXML($this->buildXml());

     if($xml->save($_SERVER['DOCUMENT_ROOT'].$this->fileName)) {

        echo 'файл выгружен: ', $_SERVER['DOCUMENT_ROOT'].$this->fileName;

     }

   }

   public static function instance() : ExportBase {

     return new static();

   }

   protected function getTitle(array $row, int $category_id) : string {

      $square = ($category_id == self::RENT_BUSSINES) ? $row['UF_CRM_1541076330647'] : $row['UF_CRM_1540384944'];
  
      $region = $this->enumValue((int)$row['UF_CRM_1540203111'],'UF_CRM_1540203111');
      $region.= ', ';
      
      switch($category_id) {
  
        case self::RENT :
  
        return strtoupper(sprintf("%s: %s, %s %s метров", self::TITLE_ALIAS[$category_id], 
                                                          self::TITLE_ALIAS_SYNONYM[$category_id],$region, $square));
  
        break;
  
        case self::SALE :
  
        return strtoupper(sprintf("%s: %s, %s %s метров", self::TITLE_ALIAS[$category_id], 
                                                          self::TITLE_ALIAS_SYNONYM[$category_id], $region, $square));
  
        break;
  
        case self::RENT_BUSSINES :
  
        return strtoupper(sprintf("%s: %s, окупаемость - %s", self::TITLE_ALIAS[$category_id], 
                                                              self::TITLE_ALIAS[$category_id], $row['UF_CRM_1544431330']));
  
        break;
  
      }
  
    }  
}

