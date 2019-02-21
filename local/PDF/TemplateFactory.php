<?php

namespace PDF;

abstract class TemplateFactory {

  private $templateFolder = __DIR__.'/templates';

  protected $templatePath;

  protected $withOutHeaderPath;

  protected $onlyHeaderPath;

  protected $brokerHeaderPath;

  protected $withBrokerPath;

  private   $templateType;

  private const WITH_OUT_HEADER = 'OUT_HEAD';

  private const ONLY_HEADER = 'ONLY_HEAD';

  private const BROKER_HEADER = 'BROKER_HEAD';

  private const ONLY_BROKER = 'BROKER_ONLY';

  private function __construct(string $templateType) {

     $this->templateType =  $templateType;

  }

  public static function instance(string $templateType) : TemplateFactory {

     return new static($templateType);

  }

  public function getTemplate() : string {

     switch($this->templateType) {

        case self::WITH_OUT_HEADER : 

              return sprintf("%s%s", $this->templateFolder, $this->withOutHeaderPath);

        break;

        case self::ONLY_HEADER : 

              return sprintf("%s%s", $this->templateFolder, $this->onlyHeaderPath);

        break;

        case self::BROKER_HEADER : 

              return sprintf("%s%s", $this->templateFolder, $this->brokerHeaderPath);

        break;

        case self::ONLY_BROKER : 

              return sprintf("%s%s", $this->templateFolder, $this->withBrokerPath);

        break;

    }
    
  }
}