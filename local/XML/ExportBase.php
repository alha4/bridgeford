<?php
namespace XML;

const DEBUG_AUTOTEXT = 'Y';

abstract class ExportBase {

   use \XML\Helpers\ExportHelper;
   use \XML\Helpers\Description;

   protected $fileName;

   protected const RENT = 0;

   protected const SALE = 1;

   protected const RENT_BUSSINES = 2;

   protected const STATUS_OBJECT = 357;

   protected const STREET_TYPE = 37;

   protected const TYPE_DEAL = [

      'RENT' => 0,
      'SALE' => 1,
      'RENT_BUSSINES' => 2 

   ];

   protected const SEMANTIC_CODE = [

       '0' => 'UF_CRM_1540974006',
       '1' => 'UF_CRM_1544172451',
       '2' => 'UF_CRM_1544172560'

   ];

   protected const HOST = 'https://bf.angravity.ru';
 
   abstract protected function buildXml() : string;

   public function export() : void {

     $xml = new \DomDocument("1.0","UTF-8");

     $xml->encoding = 'UTF-8';
     $xml->preserveWhiteSpace = false;
     $xml->formatOutput = true;
     $xml->validateOnParse = true;

     $xml->loadXML($this->buildXml());

     if($xml->save($_SERVER['DOCUMENT_ROOT'].$this->fileName)) {

        echo  $_SERVER['SERVER_NAME'].$this->fileName;

     }

   }

   public static function instance() : ExportBase {

     return new static();

   }
}

