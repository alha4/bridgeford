<?php

namespace XML\Parser;

use \Bitrix\Main\Application;

class ParserFactory {

  public function create() : Parser {

    $param = Application::getInstance()->getContext()->getRequest()->get('parser');

    if(!XML_PATH_MAP[$param]) {

       throw new \Error('Не корректный параметр запроса !');

    }

    $objectParser = ObjectParser::instance(); 
    $objectParser->setPath(XML_PATH_MAP[$param]);

    return $objectParser;

  }

  private function __construct() {}

}