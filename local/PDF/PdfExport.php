<?php

namespace PDF;

use \Mpdf\Mpdf;

abstract class PdfExport {

  protected $filePrefix;

  protected const OBJECT_TYPE = [

                    '0' => 'Аренда',
                    '1' => 'Продажа',
                    '2' => 'Аренда'
                  ];

  protected const RENT_TYPE = [

                  '0' => ' ',
                  '1' => 'закрытая продажа,'
               
                 ];
               
  protected const INPUTTYPE = [
               
                   "96"  =>  "общий",
                   "95"  =>  "отдельный"
               
                 ];
               
  protected const CURRENCY =  [
               
                   "144" => 'руб.',
                   "145" => 'usd',
                   "146" => 'eur'
               
                 ];
               
  protected const CURRENCY_CODE =  [
               
                   "144" => 'RUB',
                   "145" => 'USD',
                   "146" => 'EUR'
               
                 ];

  protected const STREET_TYPE = 37;

  use \PDF\Helpers\PdfHelper;


  public function export(int $doc_id, TemplateFactory $templateFactory) : void {
   
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf([

     'fontdata' => $fontData + [

      'firasanscondensed' => [
        'R' => 'firasanscondensedregular.ttf',
        'I' => 'firasanscondenseditalic.ttf',
        'B' => 'firasanscondensedbold.ttf',
      ],

      'default_font' => 'firasanscondensed'

    ]]);

    $mpdf->AddFontDirectory($_SERVER['DOCUMENT_ROOT'].'/local/mpdf/fonts/');

    $mpdf->useSubstitutions = true;

    $mpdf->CSSselectMedia = 'mpdf';

    $mpdf->WriteHTML($this->getMacros($doc_id, $templateFactory));

    $dateFile = date("d.m.Y-H:i:s");

    $mpdf->Output(sprintf("%s_%s_%s.pdf",$this->filePrefix, $doc_id, $dateFile), \Mpdf\Output\Destination::DOWNLOAD);

  }
  
  public function instance() : PdfExport {

     return new static();

  }

  private function getMacros(int $doc_id, TemplateFactory $templateFactory) : string {

    $fileTemplate = $templateFactory->getTemplate();

    if(!file_exists($fileTemplate)) {

      throw new \Exception('Не найден файл pdf шаблона');
    
    }
    
    $arFields = $this->buildMacros($doc_id);

    $macros   = array_keys($arFields);

    $replaced = array_values($arFields);

    $template = file_get_contents($fileTemplate);

    return str_replace($macros, $replaced, $template);

  }

  protected function getData(int $doc_id, array &$select) : array {

    $sort = ["ID" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "ID" => $doc_id];

    $object = \CCrmDeal::GetList($sort, $filter, $select);
    
    return $object->Fetch() ? : [];

  }
  
  abstract protected function buildMacros(int $doc_id) : array;

}