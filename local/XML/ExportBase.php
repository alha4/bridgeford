<?php
namespace XML;

abstract class ExportBase {

   use \XML\Helpers\CrmHelper;

   protected $fileName;

   protected const STATUS_OBJECT = 357;

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
   
}

