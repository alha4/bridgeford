<?php

namespace XML\Helpers;

use \Semantic\SemanticFactory;

trait Description {

  protected $cache = [];

  protected function getDescription(int $category, array &$semantic, array &$arFields, string $description, bool $is_autotext) : string {

    if($is_autotext) {

      $path = SemanticFactory::create($category);

      $arSemantic = $this->loadSemantic($path);

      #echo $path,'<br>';

      $auto_text = '';

      /**
       * Cемантика
       */

      foreach($semantic as $value) {

         $text  = $this->parse($arSemantic[$value], $arFields);

         $auto_text.= $text;

         if($text) {

           $auto_text.= ',';

         }

       }

       /**
        * Остальные поля
        */

       foreach($arSemantic as $code=>$value) {

         #echo $code,'<br>';

         if(array_key_exists($code, $arFields)) {

           /**
            *  множественное
            */

           if(is_array($arSemantic[$code])) {

              $multi_text = $arSemantic[$code];

              foreach($multi_text as $index => $text) {

                 #echo $index,' ', $text, '<br>';
                 #echo $arFields[$index],'<br>';

                 $row_value = enumValue($arFields[$index], $index) ? : $arFields[$index];

                 $auto_text.= str_replace($index, $row_value , $text);
                 $auto_text.= ',';

              }

           } else {

            if(is_array($arFields[$code])) {

              // echo $code,'<br>';

               $auto_text.= $value;
               $auto_text.= implode(',', array_map(function($item) use($code) {
                
                 return enumValue($item, $code);
              
               }, $arFields[$code]));
               

             } else {

              $text_value = enumValue($arFields[$code], $code) ? : $arFields[$code];

              if(strpos($value, $code) !== false) {

                 $auto_text.= str_replace($code, $text_value, $value);

              } else {

                $auto_text.= $value;

              }
                
              $auto_text.= ',';

             }
          }
         }
       }


      return substr($auto_text,0,-1);

   }

   return $description;

  }

  protected function loadSemantic(string $path) : array {

    if(!file_exists($path)) {

        throw new \Error('файл семантики не найден!');

    }

    if(!in_array($path, $this->cache)) {

       $this->cache[$path] = require($path);

    }

    return  $this->cache[$path];

  }

  protected function parse(?string $text, &$arFields) : ?string {

    foreach($arFields as $code=>$value) {

      if(strpos($text, $code) !== false) {

         $semantic_value = enumValue($arFields[$code], $code) ? : $arFields[$code];

         $text = str_replace($code, $semantic_value, $text);

      }

    }

    return $text;

  }

}