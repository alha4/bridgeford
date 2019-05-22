<?php

namespace XML\Helpers;

\CModule::IncludeModule("iblock");

trait ExportHelper {

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

 protected function getContactFullName(int $user_id) : string {
 
   $order = array('ID' => 'DESC');

   $filter = array("ID" => $user_id, "CHECK_PERMISSIONS" => "N");

   $rsUsers = \CCrmContact::GetList($order, $filter, ["NAME","LAST_NAME"]);

   $user = $rsUsers->Fetch();

   return $user['NAME'].' '.$user['LAST_MAE'];

 }

 protected function getMultiField(int $id, string $type) : string {

   $arField = \CCrmFieldMulti::GetList(array(), array("ENTITY_ID"=>"CONTACT","TYPE_ID" => $type,"ELEMENT_ID" => $id)); 

   return $arField->Fetch()['VALUE'] ? : 'нет';

 }

 protected function escapeEntities(string $value) : string {

  return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, "UTF-8");

 }
}