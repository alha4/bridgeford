<?php

namespace PDF;

use \Mpdf\Mpdf;

abstract class PdfExport {

  protected $filePrefix;

  protected $templatePath;

  protected const OBJECT_TYPE = [

                       '0' => 'Аренда',
                       '1' => 'Продажа',
                       '2' => 'Аренда'
                  ];

  protected const STREET_TYPE = 37;
  
  public function export(int $doc_id) : void {
   
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf([

     'fontdata' => $fontData + [

     'merriweather' => [
        'I' => 'merriweatherboldIt.ttf',
        'R' => 'merriweatherboldIt.ttf'
      ],

      'firasanscondensed' => [
        'R' => 'firasanscondensedextralight.ttf',
        'I' => 'firasanscondensedextralight.ttf',
      ],

      'default_font' => 'firasanscondensed'

    ]]);

    $mpdf->AddFontDirectory($_SERVER['DOCUMENT_ROOT'].'/local/mpdf/fonts/');

    $mpdf->useSubstitutions = true;

    $mpdf->CSSselectMedia = 'mpdf';

    $mpdf->WriteHTML($this->getMacros($doc_id));

    $dateFile = date("d.m.Y-H:i:s");

    $mpdf->Output(sprintf("%s_%s_%s.pdf",$this->filePrefix, $doc_id, $dateFile), \Mpdf\Output\Destination::DOWNLOAD);

  }
  
  public function instance() : PdfExport {

     return new static();

  }

  private function getMacros(int $doc_id) : string {

    $fileTemplate = $this->templatePath.'/template.html';

    if(!file_exists($fileTemplate)) {

      throw new \Exception('Не найден файл pdf шаблона');
    
    }
    
    $arFields = $this->buildMacros($doc_id);

    $macros   = array_keys($arFields);

    $replaced = array_values($arFields);

    $template = file_get_contents($fileTemplate);

    return str_replace($macros, $replaced, $template);

  }
  
  abstract protected function buildMacros(int $doc_id) : array;

}