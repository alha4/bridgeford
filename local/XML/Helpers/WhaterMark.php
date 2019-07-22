<?php

namespace XML\Heplers;
        
use \Bitrix\Main\UserTable;

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

 public function createWhaterMark(int $fileID, int $id,int $cian_id, ?int $assigned_by_id = 0) : string {

  $arFile = \CFile::GetFileArray($fileID);

  $user = UserTable::getList(["filter" => ['ID' => $assigned_by_id]])->fetch();

  if($arFile['WIDTH'] < self::MIN_WIDTH) {

    $this->logger->info(['user' => sprintf("%s %s %s",$user['LAST_NAME'],$user['NAME'],$user['SECOND_NAME']) ,'id' => $id,'site_id' => $cian_id,'file' => $arFile['ORIGINAL_NAME'], 'msg' => 'картинка меньше минимальной ширины', 'WIDTH' =>  $arFile['WIDTH']]);

    $arFile['WIDTH'] = self::MIN_WIDTH;

  }

  if($arFile['HEIGHT'] < self::MIN_HEIGHT) {
 
    $this->logger->info(['user' => sprintf("%s %s %s",$user['LAST_NAME'],$user['NAME'],$user['SECOND_NAME']), 'id' => $id, 'site_id' => $cian_id,'file' => $arFile['ORIGINAL_NAME'], 'msg' => 'картинка меньше минимальной высоты', 'HEIGHT' => $arFile['HEIGHT'] ]);

    $arFile['HEIGHT'] = self::MIN_HEIGHT;

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
