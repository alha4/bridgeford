<?php

namespace XML\Heplers;

class WhaterMark {

 private $watermark;

 private $logger;

 private const MIN_WIDTH = 400;

 private const MIN_HEIGHT = 400;

 public function setPath(string $filepath) : void {

    $filepath = $_SERVER["DOCUMENT_ROOT"].$filepath;

    if(!is_file($filepath) || !file_exists($filepath)) {

      throw new \Error('Файл не найден');

    } elseif(!\CFile::IsImage(basename($filepath))) {

      throw new \Error('Файл не является изображением');

    }

    $this->watermark = $filepath;

 }

 private function getPath() : string {

    return $this->watermark;

 }

 public function setLogger(\Log\Logger $logger) : void {

    $this->logger = $logger;

 }

 public function createWhaterMark(int $fileID) : string {

  $arFile = \CFile::GetFileArray($fileID);

  if($arFile['WIDTH'] < self::MIN_WIDTH) {

    $originWidth = $arFile['HEIGHT'];
 
    $arFile['WIDTH'] = self::MIN_WIDTH;

    $this->logger->info([ 'file' => $arFile['ORIGINAL_NAME'], 'msg' => 'картинка меньше минимальной ширины', 'WIDTH' => $originWidth]);

  }

  if($arFile['HEIGHT'] < self::MIN_HEIGHT) {
 
    $originHeight = $arFile['HEIGHT'];

    $arFile['HEIGHT'] = self::MIN_HEIGHT;

    $this->logger->info([ 'file' => $arFile['ORIGINAL_NAME'], 'msg' => 'картинка меньше минимальной высоты', 'HEIGHT' => $originHeight ]);

    unset($originHeight);

  }

  $arWaterMark = [

    array(
        "name"     => "watermark",
        "position" => "center", // Положение
        "type"     => "image",
        "size"     => "big",
        "file"     =>  $this->getPath(), // Путь к картинке
       /* "alpha_level" => 0.5*/
    )

  ];

  return \CFile::ResizeImageGet(
      $fileID,
      array(
        "width"  => $arFile['WIDTH'], 
        "height" => $arFile['HEIGHT']
      ),
      \BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
      true,
      $arWaterMark
    )['src'] ? : '';

 }

}
