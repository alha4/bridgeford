<?php

namespace XML\Helpers;

use \Semantic\SemanticFactory;

trait Description {

  protected $cache = [];

  private static $OZS = 76;

  private static $MOSKOW = 26;

  private static $HIGHLY_LIQUID_OBJECT = 322;

  private static $LEAST_YEAR = 8;

  private static $NEW_BUILDING = 82;

  private static $METRO = 'UF_CRM_1543406565';

  private static $MAIN_TITLE_ADVERTISING = ['UF_CRM_1540384807664','UF_CRM_1540202667','UF_CRM_1540371938','UF_CRM_1540371455'];

  protected function getDescription(int $category, array &$semantics, array &$arFields) : string {

    $path = SemanticFactory::create($category);

    $arSemantic = &$this->loadSemantic($path);

    $auto_text = '';

    foreach($arSemantic as $code => $semantic) {

      /**
       * поля раздела семантики
       */
     if(is_numeric($code) && in_array($code, $semantics)) {

        /**
         * поле семантики с логикой
         */
       if(is_array($arSemantic[$code]) && array_key_exists("LOGIC", $arSemantic[$code])) {

          $logics = $arSemantic[$code]['LOGIC'];
  
          foreach($logics as $logic) {
  
            foreach($logic as $field => $condition) {
  
              if(in_array($field, $semantic) !== false) {
  
                $logic_text = $this->parse($arSemantic[$code]['TEXT'], $arFields);
  
                if($logic_text) {
                   $auto_text.= $logic_text;
                   $auto_text.= ',';
                }
             } 
           }
         }
       } else {
        
        /**
         * 
         * Высоколиквидный объект
         * UF_CRM_1544431330 - окупаемость
         */
       if($code == self::$HIGHLY_LIQUID_OBJECT) {
  
         if(strpos($arFields['UF_CRM_1544431330'],'лет') !== false) {
  
            $year = (int)$arFields['UF_CRM_1544431330'];
  
            if($year > self::$LEAST_YEAR) {
  
                continue;
  
            }
          }
       }

       $semantic_text = $this->parse($arSemantic[$code], $arFields);

       if($semantic_text) {

          $auto_text.= $semantic_text;
          $auto_text.= ',';

        }
      } 
      /**
      * остальные польз-е поля (не из семантики) 
      */
     } elseif(array_key_exists($code, $arFields))  {
        
        /**
         * составной текст из множества полей
         */
       if(is_array($arSemantic[$code])) {

         $type = false;

         if($arFields['UF_CRM_1540371261836'] == self::$OZS && $code == 'UF_CRM_1540202667' && 
            $arFields['UF_CRM_1540371938'] == 0) {

      
            $code = 'UF_CRM_1540202667';

            $type = 'ОЗС';

         } elseif($arFields['UF_CRM_1540371261836'] == self::$OZS && $code == 'UF_CRM_1540371938' &&
           $arFields['UF_CRM_1540371938'] == 1) {

           $code = 'UF_CRM_1540371938';

           $type = 'ОЗС + особняк';

           
         } elseif($arFields['UF_CRM_1540371261836'] != self::$OZS && $code == 'UF_CRM_1540371455' &&
           $arFields['UF_CRM_1540371938'] == 0 && $arFields['UF_CRM_1540371455'] == self::$NEW_BUILDING) {

           $code == 'UF_CRM_1540371455';

           $type = 'Новостройка';
            
         } elseif($arFields['UF_CRM_1540371261836'] != self::$OZS && $code == 'UF_CRM_1540384807664' &&
           $arFields['UF_CRM_1540371938'] == 0 && $arFields['UF_CRM_1540371455'] != self::$NEW_BUILDING) {

           $code = 'UF_CRM_1540384807664';

           $type = 'Тип здания';

         } 

         if(!$type && in_array($code, self::$MAIN_TITLE_ADVERTISING)) {

             continue;

         } 

         $arText = $arSemantic[$code];

         foreach($arText as $index => $milti_text) {

          if($index == self::$METRO) {

            $row_value = iblockValue($arFields[$index]);

            $auto_text.= str_replace($index, $row_value, $milti_text);
            $auto_text.= ',';

          } else {
                
            if($arFields['UF_CRM_1540202889'] == self::STREET_TYPE && $index == 'STREET') {

              foreach($arText['STREET'] as $key=>$location) {

                $row_value = enumValue((int)$arFields[$key], $key) ? : $arFields[$key];
                $auto_text.= str_replace($key, $row_value , $location);
             
              }

              $auto_text.= ',';
     
            } elseif($arFields['UF_CRM_1540202889'] != self::STREET_TYPE && $index == 'PLACE') {

              foreach($arText['PLACE'] as $key=>$location) {

                $row_value = enumValue((int)$arFields[$key], $key) ? : $arFields[$key];
                $auto_text.= str_replace($key, $row_value, $location);

              }

              $auto_text.= ',';
               
            } else {

              /**
               * если регион москва, город не выводим
               */

              if($arFields['UF_CRM_1540202667'] == self::$MOSKOW && $index == 'UF_CRM_1540202817') {

                continue;

              }

              /**
               * если регион не москва, округ не выводим
               */

              if($arFields['UF_CRM_1540202667'] != self::$MOSKOW && $index == 'UF_CRM_1540203111') {

                  continue;

              }

              $row_value = enumValue((int)$arFields[$index], $index) ? : $arFields[$index];

              if($row_value) {
                
                $auto_text.= str_replace($index, $this->regionMorphology($row_value), $milti_text);
                $auto_text.= ',';

              }
            }
          } 
        }
       } else {

        /**
         * множественное пользо-е поле 
         */
         if(is_array($arFields[$code])) {

            $auto_text.= $arSemantic[$code];
            $auto_text.= implode(',', array_map(function($item) use($code) {

                return enumValue($item, $code);

             }, $arFields[$code]));

             $auto_text.= ', ';

         } else {

          $text = $arSemantic[$code];

          $text_value = enumValue((int)$arFields[$code], $code) ? : $arFields[$code];

          if($text_value) {

              #echo $code,' ',$arFields['ID'],' ',  $text,' ,',$text_value,'<br>';

             if(strpos($text, $code) !== false) {
 
                $auto_text.= str_replace($code, $text_value, $text);
 
             } else {
 
                $auto_text.= $text;
             } 

             if($text_value) {

                $auto_text.= ',';

             }
          }
        } 
      }   
    }
   }
  
   return substr($auto_text,0,-1);

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

  protected function regionMorphology(?string $value) : string {

    $in = ['Москва','Новая'];

    $out = ['Москве','Новой'];

    return str_replace($in, $out, $value);

  }

}