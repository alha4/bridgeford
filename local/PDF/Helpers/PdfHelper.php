<?php

namespace PDF\Helpers;

\CModule::IncludeModule("iblock");

trait PdfHelper {

protected function enumValue(int $value_id, string $code) : string {

  $entityResult = \CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_DEAL", "FIELD_NAME" => $code));
  $entity = $entityResult->Fetch();
  $enumResult = \CUserTypeEnum::GetList($entity);

  while($enum = $enumResult->GetNext()) {

      if($enum['ID'] == $value_id) {

         return $enum['VALUE'];

      }

  }

  return '';

 }

 protected function IblockEnumValue(string $value_id) : string {

    return \CIBlockElement::GetByID((int)$value_id)->Fetch()['NAME'] ? : "";

 }

 protected function getPhone(int $user_id) : string {

    if(!$user_id) return false;

    $order = array('id' => 'asc');
    $sort = 'id';

    $filter = array("ID" => $user_id);

    $rsUsers = \CUser::GetList($order, $sort, $filter, ["SELECT" => array("PERSONAL_PHONE") ]);

    return substr($rsUsers->Fetch()['PERSONAL_PHONE'],1);

 }

 protected function getUserFullName(int $user_id) : string {

   if(!$user_id) return false;

   $order = array('id' => 'asc');
   $sort = 'id';

   $filter = array("ID" => $user_id);

   $rsUsers = \CUser::GetList($order, $sort, $filter, ["SELECT" => array("NAME","LAST_NAME")]);

   $user = $rsUsers->Fetch();

   return $user['NAME'].' '.$user['LAST_NAME'];

 }

 protected function getPrice(int $price, string $currency) : string {

    return \SaleFormatCurrency($price, self::CURRENCY_CODE[$currency]);

 }

 protected function getBroker(?int $ID) : array {

  if(!$ID) return [];
  
  $user = \CUser::GetList($sort = 'ID', $order = 'desc', ['ID' => $ID], ['SECECT' => ['NAME','LAST_NAME','EMAIL','PERSONAL_PHONE'] ]);

  $arUser = $user->Fetch();

  return [

     'FULL_NAME' => sprintf("%s %s", $arUser['NAME'], $arUser['LAST_NAME']),
     'EMAIL' => $arUser['EMAIL'],
     'PHONE' => $arUser['PERSONAL_PHONE']

  ];
 
 }

 protected function getCeilingPrefix(int $value) : string {

  if($value < 2) {

      return 'р';

  }
  
  return $value % 2 == 0 || $value == 3 ? 'ра' : 'ров';

 }

 protected function getLocationMap(int $file_id) : string {

  $file = \CFile::GetFileArray($file_id);

  return sprintf("%s", $file['SRC']);

 }

 protected function getAddress(array $row) : string {

  if($row['UF_CRM_1540202889'] == self::STREET_TYPE) {

      return sprintf("Россия, %s, %s д.%s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $row['UF_CRM_1540202908']);

  }

  return sprintf("Россия, %s, %s %s д.%s",$row['UF_CRM_1540202817'], $row['UF_CRM_1540202900'], $this->enumValue((int)$row['UF_CRM_1540202889'],'UF_CRM_1540202889'), $row['UF_CRM_1540202908']);

 }

 protected function getImages(array $data) : string {

  $html_img = '';

  foreach($data as $k=>$file_id) {


    $html_img.= sprintf("%s<td class='obj_img'><img src='%s' width='310' height='210'>", ($k % 2 == 0  ?  "<tr>" : '')  ,\CFile::GetPath($file_id));


  }

  return $html_img;

 }

 protected function getContactFullName(int $user_id) : string {
 
   $order = array('ID' => 'DESC');

   $filter = array("ID" => $user_id, "CHECK_PERMISSIONS" => "N");

   $rsUsers = \CCrmContact::GetList($order, $filter, ["NAME","LAST_NAME"]);

   $user = $rsUsers->Fetch();

   return $user['NAME'].' '.$user['LAST_MAE'];

 }

 protected function getMultiField(int $id, string $type) : string {

   $arField = \CCrmFieldMulti::GetList(array(), array("ENTITY_ID"=>"CONTACT","TYPE_ID" => $type,"ELEMENT_ID" => $id)); 

   return $arField->Fetch()['VALUE'];

 }
}