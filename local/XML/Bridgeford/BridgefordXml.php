<?php
namespace XML\Bridgeford;

use XML\ExportBase;

final class BridgefordXml extends ExportBase {

  protected $fileName = '/bridgeford_commerc.xml';

  private const NEW_BUILDING = 82;

  private const TYPE = [

    '0' => 'Помещение в аренду',
    '1' => 'Помещение на продажу',
    '2' => 'Арендный бизнес'

  ];

  private const BUILDING_TYPE = [

    '74' => 'жилой',
    '75' => 'административный',
    '76' => 'отдельно стоящее здание',
    '77' => 'бизнес центр',
    '78' => 'торговый центр',
    '79' => 'торгово-офисный центр',
    '80' => 'производственный комплекс',
    '81' => 'складской комплекс'
  ];

  private const FACILITY_TYPE = [
                   
    '88' => 'торговое',
    '89' => 'ПСН',
    '90' => 'офисное',
    '91' => 'общепит',
    '92' => 'под бытовые услуги',
    '93' => 'производственное помещение',
    '94' => 'склад'
 
   ];

   private const INPUTTYPE = [

    "96"  =>  "общий",
    "95"  =>  "отдельный"

  ];

  /**
   * 
   * bool UF_CRM_1545648767661 - Реклама сайт
   * 
   */

  private const DEFAULT_REGION = 'Москва';

  protected function buildXml() : string {

    $sort   = ["UF_CRM_1545648767661" => "DESC"];

    $filter = ["CHECK_PERMISSIONS" => "N", "UF_CRM_1545199624" => self::STATUS_OBJECT, "UF_CRM_1545648767661" => 1];

    $select = ["OPPORTUNITY","UF_CRM_1541004853118","UF_CRM_1540371261836","UF_CRM_1540384807664",
               "UF_CRM_1540202667","UF_CRM_1540202766","UF_CRM_1540202747","UF_CRM_1540202817",
               "UF_CRM_1540203111","UF_CRM_1540203144","UF_CRM_1540202889","UF_CRM_1540202900",
               "UF_CRM_1543406565","UF_CRM_1540203015","UF_CRM_1541072151310","UF_CRM_1541056049",
               "UF_CRM_1541055405","UF_CRM_1541055672","UF_CRM_1541055237379","UF_CRM_1541055274251",
               "UF_CRM_1541056258","UF_CRM_1541056313","UF_CRM_1540456608","UF_CRM_1540384916112",
               "UF_CRM_1540381545640","UF_CRM_1540384944","UF_CRM_1540471409","UF_CRM_1540532330",
               "UF_CRM_1540385060","UF_CRM_1540385112","UF_CRM_1540384963","UF_CRM_1540371585",
               "UF_CRM_1540385040","UF_CRM_1540385262","UF_CRM_1540202908","UF_CRM_1540895685",
               "UF_CRM_1544524903217","UF_CRM_1540895373","ASSIGNED_BY_ID","UF_CRM_1540392018",
               "UF_CRM_1540974006","UF_CRM_1544172451","UF_CRM_1544172560","UF_CRM_1552294499136",
               "UF_CRM_1555070914","UF_CRM_1552493240038","UF_CRM_1540371802","UF_CRM_1560505660340",
               "UF_CRM_1540371455","UF_CRM_1545649289833","UF_CRM_1556017573094","UF_CRM_1540532459",
               "UF_CRM_1540202807","UF_CRM_1563276840"];
    
    $object = \CCrmDeal::GetList($sort, $filter, $select);

    $xml_string.= '<offers>';

    while($row = $object->Fetch()) {

      $category_id = \CCrmDeal::GetCategoryID($row['ID']);

      $semantic_code = self::SEMANTIC_CODE[$category_id];

      $semantic = (array)$row[$semantic_code];

      $xml_string.= sprintf('<offer internal-id="%s">', $row['ID']);
      $xml_string.= sprintf('<type>%s</type>', self::TYPE[ $category_id ]);

      if($category_id == self::SALE || $category_id == self::RENT_BUSSINES) {

        $xml_string.= sprintf('<private-sale>%s</private-sale>', $row['UF_CRM_1541004853118'] ? 'YES' : 'NO');

      }

      $xml_string.= sprintf('<building-type>%s</building-type>', self::BUILDING_TYPE[$row['UF_CRM_1540371261836']]);
      $xml_string.= sprintf('<facility-type>%s</facility-type>', self::FACILITY_TYPE[$row['UF_CRM_1540384807664']]);

      if($row['UF_CRM_1540371455'] == self::NEW_BUILDING) {

        $xml_string.= '<is-new-construction>Yes</is-new-construction>';
        $xml_string.= sprintf('<jk>%s</jk>', $row['UF_CRM_1552493240038']);
        $xml_string.= sprintf('<construction-date>%s</construction-date>', $row['UF_CRM_1540371802']);


      }

      $region = $this->enumValue((int)$row['UF_CRM_1540202667'],'UF_CRM_1540202667');
      $xml_string.= sprintf('<region>%s</region>', $region );

      if($region != self::DEFAULT_REGION) {

         $xml_string.= sprintf('<district>%s</district>', $row['UF_CRM_1540202766'] );
         $xml_string.= sprintf('<town-type>%s</town-type>', $this->enumValue((int)$row['UF_CRM_1540202807'],'UF_CRM_1540202807'));
         $xml_string.= sprintf('<town>%s</town>', $row['UF_CRM_1540202817']);


      }

      $xml_string.= sprintf('<best-offer>%s</best-offer>', $row['UF_CRM_1560505660340'] == 1 ? 'Yes' : 'No');

      if($region == self::DEFAULT_REGION) {


        $xml_string.= sprintf('<Moscow-area>%s</Moscow-area>',$this->enumValue((int)$row['UF_CRM_1540203111'],'UF_CRM_1540203111'));


      }

      if($region == self::DEFAULT_REGION) {

  
        $xml_string.= sprintf('<Moscow-ring>%s</Moscow-ring>', $this->enumValue((int)$row['UF_CRM_1540203144'],'UF_CRM_1540203144'));

      }

      $xml_string.= sprintf('<street-type>%s</street-type>', $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'));
      $xml_string.= sprintf('<street-name>%s</street-name>', $row['UF_CRM_1540202900']);
      $xml_string.= sprintf('<building-number>%s</building-number>', $row['UF_CRM_1540202908']);
      $xml_string.= sprintf('<subway>%s</subway>', $this->IblockEnumValue($row['UF_CRM_1543406565']));
     
      $metroTime = $this->enumValue((int)$row['UF_CRM_1540203015'],'UF_CRM_1540203015');

      if($this->isTransportMetro($metroTime)) {

         $xml_string.= sprintf('<subway-time-transport>%s</subway-time-transport>',  $metroTime);

      } else {
     
         $xml_string.= sprintf('<subway-time-feet>%s</subway-time-feet>',  $metroTime);

      }
     
      $xml_string.= sprintf('<price>%s</price>',(int)$row['UF_CRM_1545649289833']);
      $xml_string.= sprintf('<is-basement>%s</is-basement>', $row['UF_CRM_1540384916112'] ? 'YES' : 'NO');
      $xml_string.= sprintf('<is-mansion>%s</is-mansion>',   $row['UF_CRM_1540371938'] ? 'YES' : 'NO');
      $xml_string.= sprintf('<whole-building>%s</whole-building>',   $row['UF_CRM_1556020811397'] ? 'YES' : 'NO');
		  $xml_string.= sprintf('<description>%s</description>', $this->escapeEntities($row['UF_CRM_1540471409']));   // Описание объекта в UF_CRM_1540471409  было UF_CRM_1556017573094
      $xml_string.= sprintf('<photo>%s</photo>', $this->getPhotos((array)$row['UF_CRM_1540532330']));
      $xml_string.= sprintf('<photo-scheme>%s</photo-scheme>',  $this->getPhotos((array)$row['UF_CRM_1540532459']));
      
      if($row['UF_CRM_1563276840']) {
         $xml_string.= sprintf('<pdf>%s%s</pdf>', self::HOST,\CFile::GetPath($row['UF_CRM_1563276840']));
      }

      $xml_string.= sprintf('<ceiling>%s</ceiling>', $row['UF_CRM_1540385060']);
      $xml_string.= sprintf('<electricity>%s</electricity>', $row['UF_CRM_1540385112']);
      $xml_string.= sprintf('<floor>%s</floor>', $row['UF_CRM_1540384963']);
      $xml_string.= sprintf('<floors-total>%s</floors-total>', $row['UF_CRM_1540371585']);
      $xml_string.= sprintf('<entrance>%s</entrance>', self::INPUTTYPE[$row["UF_CRM_1540385040"]]);
      $xml_string.= sprintf('<renovation>%s</renovation>',$this->enumValue((int)$row['UF_CRM_1540385262'],'UF_CRM_1540385262'));
      $xml_string.= $this->getOwner((int)$row['UF_CRM_1540895685']);
      $xml_string.= sprintf('<actualization-date>%s</actualization-date>', $row['UF_CRM_1544524903217']);
      $xml_string.= sprintf('<actualization-manager>%s</actualization-manager>', $this->getActualityUser($row['UF_CRM_1540895373']));
      $xml_string.= sprintf('<broker>%s</broker>',  $this->getActualityUser($row['ASSIGNED_BY_ID']));

      if($category_id == self::TYPE_DEAL['RENT_BUSSINES']) {

        $xml_string.= sprintf('<monthly-lease>%s</monthly-lease>', (int)$row['UF_CRM_1541072151310']);
        $xml_string.= sprintf('<annual-index>%s</annual-index>', $row['UF_CRM_1541056049']);
        $xml_string.= sprintf('<leaseholder-type-1>%s</leaseholder-type-1>', $this->enumValue((int)$row['UF_CRM_1541055405'],'UF_CRM_1541055405'));
        $xml_string.= sprintf('<leaseholder-type-2>%s</leaseholder-type-2>', $this->enumValue((int)$row['UF_CRM_1541055672'],'UF_CRM_1541055672'));
        $xml_string.= sprintf('<leaseholder-standard-name>%s</leaseholder-standard-name>', $this->enumValue((int)$row['UF_CRM_1541055237379'],'UF_CRM_1541055237379'));
        $xml_string.= sprintf('<leaseholder-name>%s</leaseholder-name>', $row['UF_CRM_1541055274251']);
        $xml_string.= sprintf('<lease-date>%s</lease-date>', $row['UF_CRM_1541056258']);
        $xml_string.= sprintf('<lease-duration>%s</lease-duration>', $row['UF_CRM_1541056313']);
        $xml_string.= sprintf('<taxation>%s</taxation>',  $this->enumValue((int)$row['UF_CRM_1540456608'],'UF_CRM_1540456608'));
        $xml_string.= sprintf('<space>%s</space>', $row['UF_CRM_1540384944']);

      } else {
 
        $xml_string.= sprintf('<space>%s</space>', $row['UF_CRM_1540384944']);
        $xml_string.= sprintf('<object-purpose>%s</object-purpose>', $this->getDestination((array)$row['UF_CRM_1540392018']));

      }

      $xml_string.= sprintf('<description-standardized>%s</description-standardized>', $this->getSemantic($semantic, $semantic_code));

      $xml_string.= '</offer>';
    }

    $xml_string.= '</offers>';
 
    #echo $xml_string;
    
    return $xml_string;

  }

  private function getPhotos(array $data = []) : string {

    $xml_photo = '';
 
    foreach($data as $file_id) {
 
       $file = \CFile::GetFileArray($file_id);
 
       $xml_photo.= sprintf("<image>%s%s</image>", self::HOST, $file['SRC']);
 
    }
 
    return $xml_photo;
 
  }

  private function getDestination(array $data) : string {

    $destination = '';

    foreach($data as $value_id) {

      $destination.= sprintf('<object-purpose-parameter>%s</object-purpose-parameter>',  $this->enumValue($value_id,'UF_CRM_1540392018')); 

    }

    return $destination;

  }


  private function getOwner(int $user_id) : string {
 
    $order = array('ID' => 'DESC');

    $filter = array("ID" => $user_id, "CHECK_PERMISSIONS" => "N");

    $rsUsers = \CCrmContact::GetList($order, $filter, ["NAME","LAST_NAME"]);
  
    $owner = '';

    while($user = $rsUsers->Fetch()) {

      $owner.= sprintf('<owner-name>%s %s</owner-name>',$user['NAME'], $user['LAST_MAE']);
      $owner.= sprintf('<owner-email>%s</owner-email>',$this->getMultiField($user_id, 'EMAIL'));
      $owner.= sprintf('<owner-phone>%s</owner-phone>',$this->getMultiField($user_id,'PHONE'));

    }

    return $owner;

  }

  private function getActualityUser(int $user_id) : string {

    if(!$user_id) return false;

    $order = array('id' => 'asc');
    $sort = 'id';

    $filter = array("ID" => $user_id);

    $rsUsers = \CUser::GetList($order, $sort, $filter, ["SELECT" => array("NAME","LAST_NAME")]);

    $user = $rsUsers->Fetch();

    return $user['NAME'].' '.$user['LAST_NAME'];

  }

  private function getSemantic(array $values, string $code) : string {

    $strSemantic = '';

    foreach($values as $value_id) {

      $strSemantic.= sprintf("<description-parameter>%s</description-parameter>",  $this->enumValue($value_id, $code) );

    }

    return $strSemantic;

  }

}