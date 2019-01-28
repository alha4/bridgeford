<?php

namespace PDF;

use \Mpdf\Mpdf;

abstract class PdfExport {

  protected $fileName;

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
     'cropMarkMargin' => 4,
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

    $mpdf->WriteHTML($this->buildPdf($doc_id));

    $dateFile = date("d.m.Y-H:i:s");

    $this->fileName = '_'.$doc_id;

    $mpdf->Output(sprintf("%s_%s.pdf",$this->fileName,$dateFile), \Mpdf\Output\Destination::DOWNLOAD);

  }
  
  public function instance() : PdfExport {

    return new static();

  }
  
  abstract protected function buildPdf(int $doc_id) : string;

}