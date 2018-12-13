<?php
namespace Cian;

use \Bitrix\Main\Web\HttpClient,
    \Cian\CrmObject,
    \Cian\Logger;

final class CianPriceMonitoring {

  private const CIAN_API_URL = 'https://api.cian.ru/search-offers/v2/search-offers-desktop/';

  private const CIAN_API_GEOCODED_URL = 'https://www.cian.ru/api/geo/geocoded-for-search';

  private const CIAN_API_CITY_SEARCH_URL = 'https://www.cian.ru/cian-api/site/v1/search-regions-cities/?text=#CITY#';

  private const CIAN_API_REGION_SEARCH_URL = 'https://www.cian.ru/api/geo/geocode-cached?request=#REGION#';

  private const CIAN_API_SUGGEST_URL = 'https://api.cian.ru/geo-suggest/v1/suggest-railways/?query=#Q#&regionId=#R#&offerType=#T#';

  private const CIAN_API_RESPONSE_SUCCESS = 'ok';

  private const DEFAULT_CITY = 'Москва';

  private const SQUARE_PRECENT = 10;

  private const GEO_DATA_LENGTH = 4;

  private $httpClient;

  private $http_headers = ["Host"    => "api.cian.ru",
                           "Origin"  => "https://www.cian.ru",
                           "Referer" => false,
                           "Content-Type" => "application/json",
                           "User-Agent"   => false
                          ];


  private const CIAN_SEARCH_TYPE = [

    '0' => 'commercialrent',  //Помещение в аренду
    '1' => 'commercialsale', //Помещение на продажу 
    '2' => 'commercialrent' //Арендный бизнес

  ];

  /**
   * @const array CIAN_OFFICE_TYPE 
   * [Офис,Торговая площадь,СкладПСН,Общепит,Гараж,Производство,Автосервис,Готовый бизнес,Здание,Бытовые услуги,Коммерческая земля]
   *
   */
  private const CIAN_OFFICE_TYPE = [

    '0' => [1, 2, 3, 5, 4, 9, 10, 7, 6, 11, 12],  //Помещение в аренду
    '1' => [4, 2, 3, 5, 9, 12, 11, 6, 7, 10, 1], //Помещение на продажу 
    '2' => [4, 2, 3, 5, 9, 12, 11, 6, 7, 10, 1] //Арендный бизнес

  ];

  /**
   * @const array CIAN_PRICE_TYPE 
   * dealType - в зависимости от типа предложения поле цены price | priceTotalPerMonthRur
   * sale - Помещение на продажу 
   * rent - Помещение в аренду & Арендный бизнес
   */
  private const CIAN_PRICE_TYPE = [

   'sale' => 'price',
   'rent' => 'priceTotalPerMonthRur'

  ];

  public static function instance() : CianPriceMonitoring {

    $self = new static();
    $self->setHttpClient();

    return $self;

  }
  /**
  * 
  *@param int $object_id ID сделки необязательный параметр
  *
  *@var array $objects список объектов недвижимости
  *
  *@return array
  *
  *@throws Exception - ошибка
  *
  *CATEGORY_ID - направление сделки
  */
  public function run(?int $object_id = 0) : ?array {

    $objects = CrmObject::getAll( $object_id );

    if($objects) {

      foreach($objects as $object) {

        $geodecode = $this->getGeocodedAdress($object);

        if($geodecode) {
   
          $geodecode['CATEGORY_ID'] = $object['CATEGORY_ID'];

          $response = $this->searchOffers($geodecode);

          if(count($response) > 0) {

            $competitors = $this->getOffersList($response);

            if(CrmObject::setCompetitors($object['ID'], $competitors)) {

              $price = $object['MAIN_ANCHOR'] > 0 ? $object['MAIN_ANCHOR'] : $this->getMinPrice($response);

              if($price > 0) {

               $price_step = $object['PRICE_STEP'];
           
               if(CrmObject::setPrice($object['ID'], $price, $price_step)) {
           
                  return ['ID' => $object['ID'], 'status' => 'цена обновлена'];


               } else {

                 throw new \Exception('произошла ошибка обновления цены');

               }

              } else {

                throw new \Exception('произошла ошибка цена не может быть = 0');

             }

           } else {

               throw new \Exception('произошла ошибка обновления списка конкурентов');

           }
         } else {
  
            return ['нет данных'];

         }
       }
      }
   } else {

     return ['проверьте Активировано ли автоматическое ценообразование'];

   }
 }

 /**
  *@param array $data - параметры объекта площадь, город, улица, дом
  *
  *@return array $result - массив ключей запроса для ЦИАН
  * 
  *@var int $square_gte - площадь [от] минус [SQUARE_PRECENT] процент от площади объекта
  *
  *@var string $search_type - тип поиска [Аренда,Продажа,Коммерческая]
  */
 private function buildRequest(array $data) : string {

  $square_gte = round($data['SQUARE'] - (($data['SQUARE']) / 100 * self::SQUARE_PRECENT));

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
                        'lte' => $data['SQUARE']] // до
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

    if($data['CATEGORY_ID'] == 0) {

      $result['jsonQuery']['for_day'] = [

              'type' => 'term',
              'value' => '!1'
  
      ];

    }
 
    return json_encode($result);

 }

 /**
  * @param $address поля адреса из сделки
  *
  *"@return array|bool геокодированные поля адреса
  *
  */
 public function getGeocodedAdress(array $address) : ?array {

  if($address['IS_DECODED'] == 'Y') {

     return  [
      'STREET' => $address['STREET'],
      'HOUSE'  => $address['HOUSE'],
      'CITY'   => $address['CITY'],
      'IS_MOSKOW' => $address['IS_MOSKOW'],
      'SQUARE'    => $address['SQUARE']
     ];

  }

  $full_address = $this->coordinateAdressString($address);

  $boundedBy = $this->coordinate($full_address);

  if($boundedBy) {

   $data = [
     "Lng" => $boundedBy['lng'],
     "Lat" => $boundedBy['lat'],
     "Kind" => "house",
     "Address" => $this->houseAdressString($address)
   ];

   $geoData = $this->geocoded($data);

   if($geoData) {

    if(count($geoData) >= self::GEO_DATA_LENGTH) {
     
     return  [
      'STREET' => $geoData[1]['id'],
      'HOUSE'  => $geoData[3]['id'],
      'CITY'   => $geoData[0]['id'],
      'SQUARE' => $address['SQUARE'],
      'IS_MOSKOW' => $address['IS_MOSKOW']
     ];
    
    }

    return  [
      'STREET' => $geoData[1]['id'],
      'HOUSE'  => $geoData[2]['id'],
      'CITY'   => $geoData[0]['id'],
      'SQUARE' => $address['SQUARE'],
      'IS_MOSKOW' => $address['IS_MOSKOW']
     ];

   }

   return false;

  }

  return false;

 }
 /**
  * @param array $data строка запроса для поиска объектов 
  *
  * @return array результат список предложений
  *
  */
 private function searchOffers(array $data) : array {
  
  $request  = $this->buildRequest($data);

  $response = json_decode($this->httpClient->post(self::CIAN_API_URL, $request), 1);

  Logger::log(['REQUEST' =>  $request, 'RESPONSE' => $response, 'HEADERS' => $this->httpClient->getHeaders()->toArray() ]);

  if($response['status'] == self::CIAN_API_RESPONSE_SUCCESS || $response['data']['offersSerialized']) {

     return $response['data']['offersSerialized'];

  }

  Logger::log(['REQUEST' =>  $request, 'RESPONSE' => $response, 'HEADERS' => $this->httpClient->getHeaders()->toArray() ]);

  return []; 

 }

 /**
  *@param string $search строка адрес объекта 
  *
  *@return array|void долгота и широта адреса 
  *
  */
 private function coordinate(string $search) : ?array {

  $url = str_replace('#REGION#', urlencode($search), self::CIAN_API_REGION_SEARCH_URL);

  $response = json_decode( $this->httpClient->get($url), 1);

  if($response['items']) {

     return ['lng' => $response['items'][0]['coordinates'][0], 
             'lat' => $response['items'][0]['coordinates'][1]];

  }

  return null;

 }

 /**
  * @param array $data координаты и адрес объекта
  *
  * @return array геокодированный адрес 
  *
  */
 private function geocoded(array $data) : ?array {

  $response = json_decode( $this->httpClient->post(self::CIAN_API_GEOCODED_URL, json_encode($data)), 1);

  if($response['isParsed'] == 1) {

     return $response['details'];

  }

  return null;

 }

 /**
  *@method coordinateAdressString  метод формирования строки адреса для метода coordinate
  */

 private function coordinateAdressString(array $address) : string {

  if(strlen($address['CITY']) > 0 && $address['CITY'] != self::DEFAULT_CITY) {

    
     return  "Россия, Москва, {$address['CITY']}, {$address['STREET']}, {$address['HOUSE']}";

  }

  return "Россия, Москва, {$address['STREET']}, {$address['HOUSE']}";

 }

 /**
  *@method houseAdressString метод формирования строки адреса для метода geocoded
  */

 private function houseAdressString(array $address) : string {

  if(strlen($address['CITY']) > 0 && $address['CITY'] != self::DEFAULT_CITY) {

    if($this->isStreet($address['STREET'])) {
  
      return  "Россия, Москва, {$address['CITY']}, {$address['HOUSE']}-я {$address['STREET']}";

    }

    return  "Россия, Москва, {$address['CITY']}, {$address['STREET']}, {$address['HOUSE']}";

  }

  if($this->isStreet($address['STREET'])) {

  
    return  "Россия, Москва, {$address['HOUSE']}-я {$address['STREET']}";
    

  }

  return  "Россия, Москва, {$address['STREET']}, {$address['HOUSE']}";

 }

 private function isStreet(?string $street) : bool {

    return $street && strpos($street, 'улица') ? : false;

 }

 private function isProspekt(?string $street) : bool {

    return $street && strpos($street, 'проспект') ? : false;

 }

 private function getMinPrice(array $data) : float {

  $dealType = $data[0]['dealType'];

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

 private function extractPrice(string $dealType, array $item) : int {

  if($dealType == 'sale') {

     return $item['bargainTerms'][$this->getPriceType($dealType)];

  }

  return  $item[$this->getPriceType($dealType)];
   
 }

 /**
  *@method getOffersList список предложений [название, стоимость, url на циан] 
  */

 private function getOffersList(array $data) : array {

    $result = [];

    foreach($data as $item) {

      $result[] = [
         'TITLE' => $item['geo']['userInput'],
         'PRICE' => $this->extractPrice($item['dealType'], $item),
         'URL'   => $item['fullUrl']
      ];

    }

    return $result;
    
 }

 private function setHttpClient() : void {

  $http_client = new HttpClient();

  foreach($this->http_headers as $name=>$value) {
    
    if($name == 'User-Agent') {

       $value = $this->randomUserAgent();

    }

    if($name == 'Referer') {

      $value = $this->randomReferer();

    }

    $http_client->setHeader($name, $value);

  }

  $this->httpClient = $http_client;

 }

 private function randomReferer() : string {

    $referer[] = "https://www.cian.ru/kupit-kvartiru-1-komn-ili-2-komn/";
    $referer[] = "https://www.cian.ru/cat.php?deal_type=sale&engine_version=2&offer_type=flat&region=1&room1=1&room2=1";

    return $referer[array_rand($referer)];

 }

 private function randomUserAgent()  : string {

	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5";
	$userAgentArray[] = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36 OPR/49.0.2725.47";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36 Edge/16.16299";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:52.0) Gecko/20100101 Firefox/52.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 OPR/49.0.2725.64";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/62.0.3202.94 Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0";
	$userAgentArray[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;  Trident/5.0)";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; rv:52.0) Gecko/20100101 Firefox/52.0";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/63.0.3239.84 Chrome/63.0.3239.84 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0;  Trident/5.0)";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0.1 Safari/604.3.5";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0";
	$userAgentArray[] = "Mozilla/5.0 (iPad; CPU OS 11_1_2 like Mac OS X) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0 Mobile/15B202 Safari/604.1";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:58.0) Gecko/20100101 Firefox/58.0";
	$userAgentArray[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Safari/604.1.38";
	$userAgentArray[] = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";
  $userAgentArray[] = "Mozilla/5.0 (X11; CrOS x86_64 9901.77.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.97 Safari/537.36";


	return $userAgentArray[array_rand($userAgentArray)];
 
 }
                       
 private function __construct() {}
 private function __clone(){}
}





