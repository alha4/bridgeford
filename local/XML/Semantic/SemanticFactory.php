<?

namespace Semantic;


final class SemanticFactory  {

 private static $base_name = 'semantic.php';

 public static function create(int $type) : string {

     switch($type) {


       case 0 :

          return __DIR__.'/Rent/'.self::$base_name;

       break;

       case 1 :

          return __DIR__.'/Sale/'.self::$base_name;

       break;


       case 2 :

          return __DIR__.'/RentBussines/'.self::$base_name;

       break;


       default :

       throw new Error('не корректный тип семнатики');

     }


 }

}