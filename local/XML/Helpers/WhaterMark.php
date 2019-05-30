<?php

namespace XML\Heplers;

trait WhaterMark {

 public function createWhaterMark(int $fileID) : string {

  $arFile = \CFile::GetFileArray($fileID);

  $arWaterMark = [

    array(
        "name"     => "watermark",
        "position" => "center", // Положение
        "type"     => "image",
        "size"     => "big",
        "file"     => $_SERVER["DOCUMENT_ROOT"].'/upload/qmnew.png', // Путь к картинке
       /* "alpha_level" => 0.5*/
    )

  ];

  return \CFile::ResizeImageGet(
      $fileID,
      array(
        "width"  => $arFile['WIDTH'], 
        "height" => $arFile['HEIGHT']
      ),
      BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
      true,
      $arWaterMark
    )['src'];

 }

}
