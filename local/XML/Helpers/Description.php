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

  /**
   * $MAIN_TITLE_ADVERTISING - коды полей для главного первого абзаца объявления
  */

  private static $MAIN_TITLE_ADVERTISING = ['UF_CRM_1540384807664','UF_CRM_1540202667','UF_CRM_1540371938','UF_CRM_1540371455'];

  private static $PRICES = ["UF_CRM_1540456417","UF_CRM_1540554743072","UF_CRM_1541072013901","UF_CRM_1541072151310"];

  private static $ADDRESS = ["UF_CRM_1540384807664","UF_CRM_1540202667","UF_CRM_1540371938","UF_CRM_1540371455"];

  /**
   * @param int $category - ид направления
   * @param array &$semantics - выбранные чекбоксы раздела семантика
   * @param array &$arFields - остальные пользовательские поля 
   */
  protected function getDescription(int $category, array &$semantics, array &$arFields) : string {

    if(\DEBUG_AUTOTEXT == 'Y') {

        return $arFields['UF_CRM_1556017573094'] ? : '';

    }

    $path = SemanticFactory::create($category);

    $arSemantic = &$this->loadSemantic($path);

    $last_semantic_code = array_pop(array_values($semantics));

    $auto_text = '';

    /**
     * @var $arSemantic - массив из файла семантики
     * @var $code - код поля
     */

    foreach($arSemantic as $code => $semantic) {

      /**
       * если поле из раздела семантики
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
                   $auto_text.= '.';
       
               }
             } 
           }
         }

       } else {
        
       /**
       * 
       * $HIGHLY_LIQUID_OBJECT - Высоколиквидный объект
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


       /**
        * ставим точку
        */

       if($semantic_text) {

          $auto_text.= $semantic_text;
          $auto_text.= '. ';

        }
      }
      
      /**
      * если польз-е поле (не из раздела семантики) 
      * UF_CRM_1540371938 - особняк
      * UF_CRM_1540371455 - новостройка
      * 
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

            $type = true;

            //'ОЗС'

         } elseif($arFields['UF_CRM_1540371261836'] == self::$OZS && $code == 'UF_CRM_1540371938' &&
           $arFields['UF_CRM_1540371938'] == 1) {

           $code = 'UF_CRM_1540371938';

           $type = true;
           
           //'ОЗС + особняк'

           
         } elseif($arFields['UF_CRM_1540371261836'] != self::$OZS && $code == 'UF_CRM_1540371455' &&
           $arFields['UF_CRM_1540371938'] == 0 && $arFields['UF_CRM_1540371455'] == self::$NEW_BUILDING) {

           $code == 'UF_CRM_1540371455';

           $type = true;

           //'Новостройка'
            
         } elseif($arFields['UF_CRM_1540371261836'] != self::$OZS && $code == 'UF_CRM_1540384807664' &&
           $arFields['UF_CRM_1540371938'] == 0 && $arFields['UF_CRM_1540371455'] != self::$NEW_BUILDING) {

           $code = 'UF_CRM_1540384807664';

           $type = true;
           
           //'Тип здания'

         } 

         if(!$type && in_array($code, self::$MAIN_TITLE_ADVERTISING)) {

             continue;
             
         } 

         $arText = $arSemantic[$code];

         /**
          * @var $index - код поля
          */

         foreach($arText as $index => $multi_text) {

          if($index == self::$METRO) {

            $row_value = iblockValue($arFields[$index]);

            $auto_text.= str_replace($index, $row_value, $multi_text);
            $auto_text.= ',';

          } else {

            /**
             * UF_CRM_1540202889 - тип адреса (улица,проезд,площадь,проспект..)
             * если улица
             * @var $key - код поля
             */
                
            if($arFields['UF_CRM_1540202889'] == self::STREET_TYPE && $index == 'STREET') {

              foreach($arText['STREET'] as $key=>$location) {

                $row_value = enumValue((int)$arFields[$key], $key) ? : $arFields[$key];
                $auto_text.= str_replace($key, $row_value , $location);
             
              }
     
              /** если тип адреса не улица */
            } elseif($arFields['UF_CRM_1540202889'] != self::STREET_TYPE && $index == 'PLACE') {

              foreach($arText['PLACE'] as $key=>$location) {

                $row_value = enumValue((int)$arFields[$key], $key) ? : $arFields[$key];
                $auto_text.= str_replace($key, $row_value, $location);

              }

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

                /**
                 * если код поля этажность
                 */

                if($index == 'UF_CRM_1540371585') {

                /**
                 * считаем этажи
		 * if($arFields['UF_CRM_1540371585'] == 1 || $arFields['UF_CRM_1540371585'] == 21 || $arFields['UF_CRM_1540371585'] == 31) { $multi_text = добавляем к $arFields['UF_CRM_1540371585'] "-но" }
		 * if($arFields['UF_CRM_1540371585'] > 1 && $arFields['UF_CRM_1540371585'] < 5) { $multi_text = добавляем к $arFields['UF_CRM_1540371585'] "-х" }
		 * if($arFields['UF_CRM_1540371585'] >= 5 && $arFields['UF_CRM_1540371585'] < 21) { $multi_text = добавляем к $arFields['UF_CRM_1540371585'] "-и" }
		 * if($arFields['UF_CRM_1540371585'] > 21 && $arFields['UF_CRM_1540371585'] < 25) { $multi_text = добавляем к $arFields['UF_CRM_1540371585'] "-х" }
		 * if($arFields['UF_CRM_1540371585'] >= 25 && $arFields['UF_CRM_1540371585'] < 31) { $multi_text = добавляем к $arFields['UF_CRM_1540371585'] "-и" }
                 */

                    $multi_text = $this->floorsName( enumValue((int)$arFields['UF_CRM_1555070914'], 'UF_CRM_1555070914'), $multi_text);

                }

                 /**
                 * если код поля Тип здания
                 */

                if($index == 'UF_CRM_1540371261836') {

                  $row_value = $this->buildMorphology($row_value);

                }
                
                $auto_text.= str_replace($index, $this->regionMorphology($row_value), $multi_text);
                $auto_text.= ' ';

              }
            }
          } 
        }

        /**
         * конец составного поля
         */

        if(in_array($code,self::$ADDRESS) ) {

          $auto_text.= '. ';

        } else {

        
          $auto_text.= '.';

        }
   

       } else {

        /**
         * если множественное польз-е поле 
         */
         if(is_array($arFields[$code])) {

            $auto_text.= $arSemantic[$code];
            $auto_text.= implode(',', array_map(function($item) use($code) {

                return ' '.enumValue($item, $code);

             }, $arFields[$code]));

             /**
              * в конце множественного поля ставим точку
             */
             $auto_text.= '. ';

         } else {

          /**
           * если не выбрано UF_CRM_1540384916112 Подвальное помещение пропускаем 
           */
          if($code == 'UF_CRM_1540371585' && $arFields['UF_CRM_1540384916112'] != 1) {

              continue;

          }
          
          $text = $arSemantic[$code];

          $text_value = enumValue((int)$arFields[$code], $code) ? : $arFields[$code];

          if($text_value) {

             if(in_array($code,self::$PRICES)) {

                 $text_value = SaleFormatCurrency($text_value,'RUB');

             }
            
             if($code == 'UF_CRM_1540371585') {

                $text = $this->floorsName(enumValue((int)$arFields['UF_CRM_1555070914'], 'UF_CRM_1555070914'), $text);
                
             }

             if(strpos($text, $code) !== false) {
 
                $auto_text.= str_replace($code, $text_value, $text);
 
             } else {
 
                $auto_text.= $text;
             } 

             /**
              * если код поля не цена, в конце ставим точку
              */

             if($text_value && !in_array($code,self::$PRICES)) {

                $auto_text.= '. ';

             }
          }
        } 
      }   
    }
   }

   if(\XML\DEBUG_AUTOTEXT == 'Y') {



   }
  
   return $auto_text;

  }

  /**
   * загрузка файла семантики
   */
  protected function loadSemantic(string $path) : array {

    if(!file_exists($path)) {

        throw new \Error('файл семантики не найден!');

    }

    if(!is_readable($path)) {
      
        throw new \Error('файл семантики не доступен для чтения!');

    }

    if(!in_array($path, $this->cache)) {

       $this->cache[$path] = require($path);

    }

    return  $this->cache[$path];

  }

  /**
   * @param string $text - текст из файла семантики
   * @param array &$arFields - поля для замены фраз
   */
  private function parse(?string $text, array &$arFields) : ?string {

    foreach($arFields as $code=>$value) {

      if(strpos($text, $code) !== false) {

         $semantic_value = enumValue($arFields[$code], $code) ? : $arFields[$code];

         $text = str_replace($code, $semantic_value, $text);

      }

    }

    return $text;

  }

  private function floorsName(string $prefix, string $text) : string {

     return str_replace('#FLOORS#', $prefix, $text);

  }

  private function regionMorphology(?string $value) : string {

    $in = ['Москва','Новая'];

    $out = ['Москве','Новой'];

    return str_replace($in, $out, $value);

  }

  private function buildMorphology(string $value) : string {

    $in = ['Жилое','Административное','ОСЗ','Бизнес-центр','Торговый центр','Торгово-офисный центр','Производственный комплекс','Складской комплекс'];

    $out = ['жилом доме','административном','отдельно стоящем здании','бизнес центре','торговом центре','торгово-офисном центре','производственном комплексе','складском комплексе'];

    return str_replace($in, $out, $value);

  }

}