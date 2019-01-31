<?php

namespace Stat;

use \Cian\Logger;

final class CompetitorEvent {

  private const EXISTS = 'SELECT ID FROM bf_stat_event WHERE DEAL_ID = %d';

  private const GET_DATA  = 'SELECT DATA FROM bf_stat_event WHERE DEAL_ID = %d ORDER BY DATE_UPDATE DESC';

  private const GET_EVENTS = 'SELECT * FROM bf_stat_event WHERE DEAL_ID = %d ORDER BY DATE_UPDATE DESC';

  private const CREATE_EVENT = "INSERT INTO bf_stat_event (DEAL_ID, DATA, EVENT, LINK) VALUES(%d, '%s', '%s', '%s')";

  public function dispatch(int $deal_id, array &$data) : bool {

    if(!$this->exists($deal_id)) {

       return $this->create($deal_id, $data, 'ADD');

    }

    $currentData = $this->getData($deal_id);

    $currentIds = array_column($this->getData($deal_id), 'ID');

    $dataIds    = array_column($data, 'ID');

    if($this->allDelete($deal_id, $data)) {

       return true;

    }

    $this->addOne($deal_id, $currentIds, $dataIds, $data);
    $this->oneDelete($deal_id, $currentIds, $dataIds, $currentData);

    return true;

  }

  private function addOne(int $deal_id, array &$current, array &$new, array &$data) : bool {

    if($diff = array_diff($new, $current)) {
 
      foreach($diff as $key => $value) {

          $index = array_search($value, $new);

          if($index !== false) {

             $this->create($deal_id, $data, 'NEW', $data[$index]['URL']);  

          }
       }

      return true;

    }

    return false;
  }

  private function oneDelete(int $deal_id, array &$current, array &$new, array &$data) : bool {

    if($diff = array_diff($current, $new)) {

      foreach($diff as $key => $value) {

        $index = array_search($value,  $current);

        if($index !== false) {

           $this->create($deal_id, $data, 'ONE_DELETE', $data[$index]['URL']);   

        }
      }

      return true;

    }

    return false;
  }

  private function allDelete(int $deal_id, array &$data) : bool {

    if(count($data) == 0) {

        return $this->create($deal_id, $data = [], 'ALL_DELETE');

    }

    return false;

  }

  private function create(int $deal_id, array &$data, string $event = '', string $link = '') : bool {

    global $APPLICATION;

    $sql   = sprintf(self::CREATE_EVENT, $deal_id, json_encode($data,JSON_UNESCAPED_UNICODE), $event, $link);
    $query = $this->query($sql);

    if($query->AffectedRowsCount() > 0) {

        return true;

    }

    $strError = '';

    if($e = $APPLICATION->GetException()) {

        $strError = $e->GetString();

    }

    Logger::log([$deal_id,$event,$data,$strError]);

    return false;

  }

  public function getEvents(int $deal_id) : array {

    $sql   = sprintf(self::GET_EVENTS, $deal_id);
    $query = $this->query($sql);

    $data = [];

    while($row = $query->Fetch()) {

         $data[] = $row;

    }

    return $data;

  }

  private function getData(int $id) : array {

    $sql   = sprintf(self::GET_DATA, $id);
    $query = $this->query($sql);

    return json_decode($query->Fetch()['DATA'], 1);

  }

  private function exists(int $deal_id) : bool {

    $sql   = sprintf(self::EXISTS, $deal_id);
    $query = $this->query($sql);

    if($query->SelectedRowsCount() > 0) {

       return true;

    }

    return false;
    
  }

  private function query(string $sql)  {

    global $DB;
    
    return $DB->Query($sql, false, $err_mess.__LINE__);

  }
}