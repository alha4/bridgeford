<?php

namespace PDF;

use PDF\TemplateFactory;

final class RentTemplate extends TemplateFactory {

  protected $withOutHeaderPath = '/RentBuilding/pom_ar03.html';

  protected $onlyHeaderPath = '/RentBuilding/pom_ar02.html';

  protected $brokerHeaderPath = '/RentBuilding/pom_ar01.html';

  protected $withBrokerPath = '/RentBuilding/pom_ar04.html';

}