<?php
namespace Cian;

use \Cian\EventInterface;

final class CianPriceMonitoring implements EventInterface {

  use \Cian\CianHelper;

  private const CIAN_API_URL = 'https://api.cian.ru/search-offers/v2/search-offers-desktop/';

  private const CIAN_API_GEOCODED_URL = 'https://www.cian.ru/api/geo/geocoded-for-search';

  private const CIAN_API_CITY_SEARCH_URL = 'https://www.cian.ru/cian-api/site/v1/search-regions-cities/?text=#CITY#';

  private const CIAN_API_REGION_SEARCH_URL = 'https://www.cian.ru/api/geo/geocode-cached?request=#REGION#';

  private const CIAN_API_SUGGEST_URL = 'https://api.cian.ru/geo-suggest/v1/suggest-railways/?query=#Q#&regionId=#R#&offerType=#T#';

  private const CIAN_API_RESPONSE_SUCCESS = 'ok';

  private const DEFAULT_CITY = 'Москва';

  private const SQUARE_PRECENT = 10;

  private const GEO_DATA_MOSKOW_ROWS = 4;

  private const OWNER_COMPANY_NAME = 'Bridgeford Capital';

  private const ERROR_ADRESS_DECODE = 'ошибка декодирования адреса';
  
  private const ERROR_COORDINATE_DECODE = 'ошибка геокодирования широты, долготы';

  private const ERROR_GEO_ENCODE = 'ошибка геокодирования адреса объекта';

  private const ERROR_EMPTY_DATA = 'нет данных';

  private const ERROR_EMPTY_OBJECT = 'нет объектов';

  private const SUCCESS_STATUS = 'цена обновлена';

  private $http_headers = ["Host"    => "api.cian.ru",
                           "Origin"  => "https://www.cian.ru",
                           "Referer" => false,
                           "Content-Type" => "application/json",
                           "User-Agent"   => false
                          ];


  private const DEAL_CATEGORY = [
    
    'TO_RENT'     => 0,        //Помещение в аренду
    'TO_SALE'     => 1,       //Помещение на продажу 
    'TO_RENT_BUSSINESS' => 2 //Арендный бизнес

  ];

  private const CIAN_SEARCH_TYPE = [

    '0' => 'commercialrent',  //Помещение в аренду
    '1' => 'commercialsale', //Помещение на продажу 
    '2' => 'commercialsale' //Арендный бизнес

  ];

  /**
   * @const array CIAN_OFFICE_TYPE 
   * [Офис,Торговая площадь,СкладПСН,Общепит,Гараж,Производство,Автосервис,Готовый бизнес,Здание,Бытовые услуги]
   *
   */
  private const CIAN_OFFICE_TYPE = [

    '0' => [1, 2, 3, 5, 4, 9, 10, 7, 6, 11, 12],  //Помещение в аренду
    '1' => [4, 2, 3, 5, 9, 12, 11, 6, 7, 10, 1], //Помещение на продажу 
    '2' => [4, 2, 3, 5, 9, 12, 11, 6, 7, 10, 1] //Арендный бизнес

  ];

  /**
   * @const array CIAN_PRICE_TYPE 
   * dealType -  тип сделки
   * sale - Помещение на продажу 
   * rent - Помещение в аренду & Арендный бизнес
   */
  private const CIAN_PRICE_TYPE = [

   'sale' => 'price',
   'rent' => 'priceTotalPerMonthRur'

  ];

  private $events = [];

  /**
  * 
  *@param array $objects - список объектов недвижимости
  *
  *@return array - SUCCESS_STATUS статус обработки
  *
  *@throws Exception - ERROR_EMPTY_DATA|ERROR_EMPTY_OBJECT 
  *
  */
  public function find(array &$objects) : array {

    foreach($objects as $object) {

      $object_id = $object['ID'];

      if($object['IS_DECODED'] == 'N') {

         $this->getGeocodedAdress($object);
  
      }

      $offers = $this->searchOffers($object);

      if(!$offers) {

        $this->notify(EventInterface::ON_SAVE_COMPETITORS, $object_id, $competitors = []);

        return ['error' => self::ERROR_EMPTY_DATA];

      }

      $competitors = $this->getOffersList($offers);

      $this->notify(EventInterface::ON_SAVE_COMPETITORS, $object_id, $competitors);

      $price = $this->findMainAnchorPrice($object_id, $object['MAIN_ANCHOR']);

      if($price == 0.0) {

        $price = $this->getMinPrice($offers);

        $this->notify(EventInterface::ON_SAVE_PRICE, $object_id, $price, $object['PRICE_STEP']);

      }
           
      return ['ID' => $object_id, 'status' => self::SUCCESS_STATUS];

    }

    return ['error' => self::ERROR_EMPTY_OBJECT];

 }

 public static function instance() : CianPriceMonitoring {

   return new static();

 } 

 public function listen(string $event, callable $callback) : void {

   $this->events[$event] = $callback;

 }

 public function notify(string $event, ...$args) : void {

  if(!array_key_exists($event,$this->events) && is_callable($this->events[$event])) {
      
    $this->events[$event](...$args);

  }
 }

 /**
  *@param array $data - параметры объекта площадь, город, улица, дом
  *
  *@return array $result - массив ключей запроса для ЦИАН
  * 
  *@var int $square_gte - площадь [от] минус [SQUARE_PRECENT] процент от площади объекта
  *
  *@var int $square_lte - площадь [до] плюс [SQUARE_PRECENT] процент от площади объекта
  *
  *@var string $search_type - тип поиска [Аренда,Продажа,Коммерческая]
  */
 private function buildRequest(array &$data) : array {

  $square_gte = round($data['SQUARE'] - (($data['SQUARE']) / 100 * self::SQUARE_PRECENT));

  $square_lte = round($data['SQUARE'] + (($data['SQUARE']) / 100 * self::SQUARE_PRECENT));

  $search_type = self::CIAN_SEARCH_TYPE[$data['CATEGORY_ID']];

  $result = ['jsonQuery' => [

            'region' => [

              'type' => "terms",
              'value' => [ $data['IS_MOSKOW'] == 1 ? $data['CITY'] : $data['STREET'] ],
            ],

            '_type' => $search_type, // тип поиска

           'engine_version' => [
             'type' => 'term',
             'value' => 2,
           ],

           'office_type' => [

            'type'  => "terms",
            'value' => self::CIAN_OFFICE_TYPE[ $data['CATEGORY_ID'] ],

           ],

           'total_area' => [
            'type' => 'range',
            'value' => ['gte' => $square_gte, // от
                        'lte' => $square_lte] // до
           ],

           'geo' =>  [
            'type' => 'geo',
            'value' => [
              [
              'type' => 'house',
              'id'  => $data['HOUSE']
              ]
            ] 
           ]
         ] 
   ];

   if($data['CATEGORY_ID'] == self::DEAL_CATEGORY['TO_RENT'] || 
      $data['CATEGORY_ID'] == self::DEAL_CATEGORY['TO_RENT_BUSSINESS']) {

      $result['jsonQuery']['for_day'] = [

              'type' => 'term',
              'value' => '!1'
  
      ] ;

   }
 
   return $result;

 }

 /**
  * @param $object - поля сделки
  *
  *"@return array - геокодированные поля адреса
  *
  */
 public function &getGeocodedAdress(array &$object) : array {

   $fullAddress = $this->adressToString($object);

   $boundedBy = $this->coordinate($fullAddress);
 
   $data = [
      "Lng" => $boundedBy['lng'],
      "Lat" => $boundedBy['lat'],
      "Kind" => "house",
      "Address" => $this->prepareAdressString($object)
   ];

   $geoData = $this->geocoded($data);

   $object['STREET'] = $geoData['STREET'];
   $object['HOUSE']  = $geoData['HOUSE'];
   $object['CITY']   = $geoData['CITY'];

   return $object;

 }
 /**
  * @param array - $data ключи запроса для поиска объектов 
  *
  * @return array - список предложений
  *
  */
 private function searchOffers(array &$data) : array {
  
  $request  = json_encode($this->buildRequest($data));

  $response = json_decode($this->httpClient()->post(self::CIAN_API_URL, $request), 1);

  if(REQUEST_LOG == 'Y') {

    $logger = \Log\Logger::instance();
    $logger->setPath('/local/Cian/log.txt'); 

    $logger->info(['REQUEST' => $request, 'RESPONSE' => $response]);

  }

  if($response['status'] == self::CIAN_API_RESPONSE_SUCCESS && $response['data']['offersSerialized']) {

    return array_filter($response['data']['offersSerialized'], function(&$item) {

        if($item['user']['agencyName'] != self::OWNER_COMPANY_NAME) {

           return true;

        }

        return false;

    });

  }

  return []; 

 }

  /**
  *@method array getOffersList - список предложений [ид объекта, название, стоимость, url] 
  */
  private function getOffersList(array &$data) : array {

    $result = [];

    foreach($data as $item) {

      $result[] = [
           'ID'    => $item['id'],
           'TITLE' => $item['geo']['userInput'],
           'PRICE' => $this->extractPrice($item['dealType'], $item),
           'URL'   => $item['fullUrl']
      ];

    }

    return $result;
    
 }

 /**
  *@param string $search -  адрес объекта 
  *
  *@return array - долгота и широта адреса 
  *
  */
 private function coordinate(string $search) : array {

  $url = str_replace('#REGION#', urlencode($search), self::CIAN_API_REGION_SEARCH_URL);

  $response = json_decode( $this->httpClient()->get($url), 1);

  if($response['items']) {

     return ['lng' => $response['items'][0]['coordinates'][0], 
             'lat' => $response['items'][0]['coordinates'][1]];

  }

  throw new \Exception(json_encode([self::ERROR_COORDINATE_DECODE,$response],JSON_UNESCAPED_UNICODE));

 }

 /**
  * @param array $data - координаты и адрес объекта
  *
  * @return array - геокодированный адрес 
  *
  */
 private function geocoded(array &$data) : array {

  $response = json_decode( $this->httpClient()->post(self::CIAN_API_GEOCODED_URL, json_encode($data)), 1);

  if($response['isParsed'] == 1) {

    $geoData = $response['details'];

    $houseKey = count($geoData) >= self::GEO_DATA_MOSKOW_ROWS ? 3 : 2;
     
    return  [
       'STREET' => $geoData[1]['id'],
       'HOUSE'  => $geoData[$houseKey]['id'],
       'CITY'   => $geoData[0]['id']
    ];
    
  }

  throw new \Exception(json_encode([self::ERROR_GEO_ENCODE,$response],JSON_UNESCAPED_UNICODE));

 }

 /**
  *@method adressToString  метод формирования строки адреса для метода coordinate
  *@return string
  */

 private function adressToString(array &$address) : string {

  if(strlen($address['CITY']) > 0 && $address['CITY'] != self::DEFAULT_CITY) {

     return  "Россия, ".self::DEFAULT_CITY.", {$address['CITY']}, {$address['STREET']}, {$address['HOUSE']}";

  }

  return "Россия, ".self::DEFAULT_CITY.", {$address['STREET']}, {$address['HOUSE']}";

 }

 /**
  *@method  prepareAdressString - формирования строки адреса для метода geocoded
  *@param  array &$address - массив с полями адреса
  */
 private function prepareAdressString(array &$address) : string {

  if(strlen($address['CITY']) > 0 && $address['CITY'] != self::DEFAULT_CITY) {

    if($this->isStreet($address['STREET'])) {
  
      return  "Россия, ".self::DEFAULT_CITY.", {$address['CITY']}, {$address['HOUSE']}-я {$address['STREET']}";

    }

    return  "Россия, ".self::DEFAULT_CITY.", {$address['CITY']}, {$address['STREET']}, {$address['HOUSE']}";

  }

  if($this->isStreet($address['STREET'])) {

  
    return  "Россия, ".self::DEFAULT_CITY.", {$address['HOUSE']}-я {$address['STREET']}";
    

  }

  return  "Россия, ".self::DEFAULT_CITY.", {$address['STREET']}, {$address['HOUSE']}";

 }

 private function getMinPrice(array &$data) : float {

  $dealType = current($data)['dealType'];

  if($dealType == 'sale') {

    $data = array_column($data, 'bargainTerms');

  }

  $prices = array_column($data, $this->getPriceType($dealType) );
  $prices = array_unique($prices);
  asort($prices);

  return array_shift($prices);

 }

 private function getPriceType(string $type) : string {

    return self::CIAN_PRICE_TYPE[$type];

 }

 private function extractPrice(string $dealType, array &$item) : int {

  if($dealType == 'sale') {

     return $item['bargainTerms'][$this->getPriceType($dealType)];

  }

  return  $item[$this->getPriceType($dealType)];
   
 }
                  
 private function __construct() {}
 private function __clone(){}
}





