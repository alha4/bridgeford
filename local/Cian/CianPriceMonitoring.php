<?php
namespace Cian;

use \Bitrix\Main\Web\HttpClient,
    \Cian\CrmObject;

final class CianPriceMonitoring {

  private const CIAN_API_URL = 'https://api.cian.ru/search-offers/v2/search-offers-desktop/';

  private const CIAN_GEOCODER_SEARCH_URL = 'https://www.cian.ru/api/geo/geocoded-for-search';

  private const CIAN_REGION_SEARCH_URL = 'https://www.cian.ru/cian-api/site/v1/search-regions-cities/?text=#CITY#';

  private const CIAN_GEO_SEARCH_URL = 'https://www.cian.ru/api/geo/geocode-cached?request=#REGION#';

  private const CIAN_SUGGEST_URL = 'https://api.cian.ru/geo-suggest/v1/suggest-railways/?query=#Q#&regionId=1&offerType=flat';

  private const SUCCESS = 'ok';

  private const DEFAULT_CITY = 'Москва';

  private const SQUARE_PRECENT = 15;

  private const GEO_DATA_LENGTH = 4;

  private $http_headers = ["Host"    => "api.cian.ru",
                           "Origin"  => "https://www.cian.ru",
                           "Referer" => "https://www.cian.ru/cat.php?deal_type=sale&engine_version=2&offer_type=flat&region=1&room1=1&room2=1",
                           "Content-Type" => "application/json",
                           "User-Agent"   => "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36"
                          ];


  private const CIAN_SEARCH_TYPE = [
      '0' => 'commercialrent',
      '1' => 'flatsale',
      '2' => 'flatrent'
  ];

 public static function instance() : CianPriceMonitoring {

   return new static();

 }
 /**
  * 
  *@param $object_id ID сделки необязательный параметр
  *
 */
 public function run(?int $object_id = 0) : void {

   $objects = CrmObject::getAll( $object_id );

   if($objects) {

     foreach($objects as $object) {

     $geodecode = $this->getGeocodedAdress($object);

     if($geodecode) {
   
        $geodecode['CATEGORY_ID'] = $object['CATEGORY_ID'];

        $response = $this->searchOffers($geodecode);

        if($response) {

           $competitors = $this->getOffersList($response);

           if(CrmObject::setCompetitors($object['ID'], $competitors)) {

             $price = $object['MAIN_ANCHOR'] > 0 ? $object['MAIN_ANCHOR'] : $this->getMinPrice($response);

             if($price > 0) {

               $price_step = $object['PRICE_STEP'];
           
               if(CrmObject::setPrice($object['ID'], $price, $price_step)) {
           
                  echo json_encode(['ID' => $object['ID'], 'status' => 'цена обновлена'], JSON_UNESCAPED_UNICODE);


               } else {

                  echo json_encode(['произошла ошибка обновления цены'], JSON_UNESCAPED_UNICODE);

               }

             } else {

                echo json_encode(['произошла ошибка цена не может быть = 0'], JSON_UNESCAPED_UNICODE);

             }

           } else {

              echo json_encode(['произошла ошибка обновления списка конкурентов'], JSON_UNESCAPED_UNICODE);

           }
         } else {
  
          echo json_encode(['нет данных',$response],JSON_UNESCAPED_UNICODE);

        }
      }
     }
   }
 }

 /**
  *@param array $data - параметры объекта площадь, город, улица, дом
  *
  *@return array $result - массив ключей запроса для ЦИАН
  * 
  *@var $square_gte - площадь [от] минус [SQUARE_PRECENT] процент от площади объекта
  *
  *@var $search_type - тип поиска [Аренда,Продажа,Коммерческая]
  */
 private function buildRequest(array $data) : array {

  $square_gte = round($data['SQUARE'] - (($data['SQUARE']) / 100 * self::SQUARE_PRECENT));

  $search_type = self::CIAN_SEARCH_TYPE[$data['CATEGORY_ID']];

  $result = ['jsonQuery' => [
           '_type' => $search_type, // тип поиска
           'engine_version' => [
             'type' => 'term',
             'value' => 2,
           ],
          /* 'room' => [

            'type' => 'term',
            'value' => [1,2] // количество комнат

           ],*/
           'geo' =>  [
            'type' => 'geo',
            'value' => [
              [
              'type' => 'house',
              'id'  => $data['HOUSE']
              ]
            ]
           ],
           'office_type' => [

            'type' => 'term',
            'value' => [1, 2, 3, 5, 4, 9, 10, 7, 6, 11, 12], // типы объектов [Офис,Торговая площадь,СкладПСН,Общепит,Гараж,Производство,Автосервис,Готовый бизнес,Здание,Бытовые услуги,Коммерческая земля]

           ],
           'region' => [

            'type' => 'term',
            'value' => [$data['CITY']],

           ],
           'total_area' => [
              'type' => 'range',
              'value' => ['gte' => $square_gte,
                          'lte' => $data['SQUARE']]
           ]
        ]];

        if($search_type == self::CIAN_SEARCH_TYPE[0]) {

            $result['jsonQuery']['for_day'] = [

              'type' => 'term',
              'value' => '!1'
  
            ];

        }

   return $result;

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
      'SQUARE' => $address['SQUARE']
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
      'SQUARE' => $address['SQUARE']
     ];
    
    }

    return  [
      'STREET' => $geoData[1]['id'],
      'HOUSE'  => $geoData[2]['id'],
      'CITY'   => $geoData[0]['id'],
      'SQUARE' => $address['SQUARE']
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
  
  $http_client = $this->httpClient();
  
  $request  = $this->buildRequest($data);

  $response = json_decode( $http_client->post(self::CIAN_API_URL, json_encode($request)), 1);

  if($response['status'] == self::SUCCESS) {

     return $response['data']['offersSerialized'] ? : [];

  }

  return ['RESPONSE' => $response, 'DATA' =>  json_encode($data) /*,'HEADERS' => $http_client->getHeaders()*/];

 }

 /**
  *@param string $search строка адрес объекта 
  *
  *@return array|void долгота и широта адреса 
  *
  */
 private function coordinate(string $search) : ?array {

  $http_client = $this->httpClient();

  $url = str_replace('#REGION#', urlencode($search), self::CIAN_GEO_SEARCH_URL);

  $response = json_decode( $http_client->get($url), 1);

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

  $http_client = $this->httpClient();

  $response = json_decode( $http_client->post(self::CIAN_GEOCODER_SEARCH_URL, json_encode($data)), 1);

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

  $prices = array_column($data, 'priceTotalPerMonthRur');
  $prices = array_unique($prices);
  asort($prices);
 
  return array_shift($prices);

 }

 /**
  *@method getOffersList список предложений [название, стоимость, url на циан] 
  */

 private function getOffersList(array $data) : array {

    $result = [];

    foreach($data as $item) {

      $result[] = [
         'TITLE' => $item['geo']['userInput'],
         'PRICE' => $item['priceTotalPerMonthRur'],
         'URL'   => $item['fullUrl']
      ];

    }

    return $result;
    
 }

 private function httpClient() {

  $http_client = new HttpClient();

  foreach($this->http_headers as $name=>$value) {

    $http_client->setHeader($name, $value);

  }

  return $http_client;

 }
                          
 private function __construct() {}
 private function __clone(){}
}





