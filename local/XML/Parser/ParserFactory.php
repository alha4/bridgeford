<?php

namespace XML\Parser;

use \Bitrix\Main\Application;

class ParserFactory {

  public function create() : Parser {

     $parser = Application::getInstance()->getContext()->getRequest()->get('parser');

     switch($parser) {

       case 1 : 

       return Rent::instance(); 

       break;


     }



  }

  private function __construct() {}

}