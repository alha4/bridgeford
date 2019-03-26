<?php

namespace XML\Helpers;

use \Semantic\SemanticFactory;

trait Description {

  protected $cache = [];

  private static $OZS = 76;

  protected function getDescription(int $category, array &$semantic, array &$arFields) : string {

    $path = SemanticFactory::create($category);

    $arSemantic = $this->loadSemantic($path);

    $semantic_code = self::SEMANTIC_CODE[$category];

    $auto_text = '';

     /**
     * Cемантика
     */
    foreach($semantic as $value) {
    
      if(is_array($arSemantic[$value]) && array_key_exists("LOGIC", $arSemantic[$value])) {

        $logics = $arSemantic[$value]['LOGIC'];

        foreach($logics as $logic) {

          foreach($logic as $field => $condition) {

            if(in_array($field, $semantic) !== false) {

              #echo $index,' ',$field,' - <br>';

              $text = $this->parse($arSemantic[$value]['TEXT'], $arFields);

              $auto_text.= $text;

              if($text) {

                $auto_text.= ',';

             }
          
           } 
         }
       }
      } else {

      if($value == 322) {

        if(strpos($arFields['UF_CRM_1544431330'],'лет') !== false) {

           $year = (int)$arFields['UF_CRM_1544431330'];

           if($year > 8) {

              echo 'пропуск';

              continue;

           }


        }

      }

      #echo $value,'<br>';

      $text  = $this->parse($arSemantic[$value], $arFields);

      if($text) {

         $auto_text.= $text;
         $auto_text.= ',';

      }
     }
    }

    /**
    * Остальные поля
    */

    foreach($arSemantic as $code=>$value) {

      if(array_key_exists($code, $arFields)) {


        if($arFields['UF_CRM_1540371261836'] != self::$OZS && $code == 'UF_CRM_1540202667') {

          #echo $arFields['UF_CRM_1540371261836'],'<br>';

          continue; 

        } 
        
        if($arFields['UF_CRM_1540371938'] == 0 && $arFields['UF_CRM_1540371261836'] != self::$OZS
      
                 && $code == 'UF_CRM_1540371938') {

          continue; 
        
        }

         /**
          *  множественное
          */

         if(is_array($arSemantic[$code])) {

            $multi_text = $arSemantic[$code];

            foreach($multi_text as $index => $text) {

               #echo $index,' ', $text, '<br>';
               #echo $arFields[$index],'<br>';

               if($index  == 'UF_CRM_1543406565') {

                  $row_value = iblockValue($arFields[$index]);

                    #echo  $row_value,'<br>';

                } else {
                  
                  if($index == 'STREET' || $index == 'PLACE') {

                      #echo $index,' ', print_r($multi_text[$index],1);

                    if($arFields['UF_CRM_1540202889'] == self::STREET_TYPE) {

                        foreach($multi_text['STREET'] as $key=>$location) {

                          #print_r([$key,$location]);

                          $row_value = enumValue((int)$arFields[$key], $key) ? : $arFields[$key];

                          #echo $key,' ',$row_value,'<br>';

                          $auto_text.= str_replace($key, $row_value , $location);

                          $auto_text.= ',';

                        }

                     } else {

                      foreach($multi_text['PLACE'] as $key=>$location) {

                         #print_r([$key,$location]);

                         $row_value = enumValue((int)$arFields[$key], $key) ? : $arFields[$key];

                         #echo $key,' ',$row_value,'<br>';

                         $auto_text.= str_replace($key, $row_value , $location);

                         $auto_text.= ',';

                      }
                     }

                  } else {

                    $row_value = enumValue((int)$arFields[$index], $index) ? : $arFields[$index];
                    $auto_text.= str_replace($index, $row_value , $text);
                    $auto_text.= ',';

                }
              }
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