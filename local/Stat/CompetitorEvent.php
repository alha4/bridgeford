<?php

namespace Stat;

use \Cian\Logger;

final class CompetitorEvent {

  private const EXISTS = 'SELECT ID FROM bf_stat_event WHERE DEAL_ID = %d';

  private const GET_DATA  = 'SELECT DATA FROM bf_stat_event WHERE DEAL_ID = %d ORDER BY DEAL_ID DESC';

  private const GET_EVENTS = 'SELECT * FROM bf_stat_event WHERE DEAL_ID = %d ORDER BY DATE_UPDATE DESC';

  private const CREATE_EVENT = "INSERT INTO bf_stat_event (DEAL_ID, DATA, EVENT, LINK) VALUES(%d, '%s', '%s', '%s')";

  public function dispatch(int $deal_id, array &$data) : bool {

    if(!$this->exists($deal_id)) {

       return $this->create($deal_id, $data, 'ADD');

    }

    $currentData = $this->getData($deal_id);

    /*
    if($deal_id == 7) {

       unset($data[array_rand($data)]);

       $data[] = ['URL' => time(), 'ID'=> time(), 'TITLE' => 'Новый тест', 'PRICE' => 70000000];
      
    }*/

    $currentIds = array_column($this->getData($deal_id), 'ID');

    $dataIds    = array_column($data, 'ID');

    if($this->allDelete($deal_id, $data)) {

       return true;

    }

    if($this->addOne($deal_id, $currentIds, $dataIds, $data)) {

         
    }

    if($this->oneDelete($deal_id, $currentIds, $dataIds, $currentData)) {


    }


    return true;

  }

  private function addOne(int $deal_id, array &$current, array &$new, array &$data) : bool {

    if($diff = array_diff($new, $current)) {
 
      foreach($diff as $key => $value) {

          $index = array_search($value, $new);

          if($index !== false) {

            if(!$this->create($deal_id, $data, 'NEW', $data[$index]['URL'])) {


            } 
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

          if(!$this->create($deal_id, $data, 'ONE_DELETE', $data[$index]['URL'])) {

             //  echo 'удалён один конкурент <br>';

          }
        }
      }

      return true;

    }

    return false;
  }

  private function allDelete(int $deal_id, array &$data) : bool {

    if(count($data) == 0) {

       if(!$this->create($deal_id, $data = [], 'ALL_DELETE')) {

          return false;

       }

       return true;

    }

    return false;

  }

  private function create(int $deal_id, array &$data, string $event = '', string $link = '') : bool {

    global $DB;

    $sql   = sprintf(self::CREATE_EVENT, $deal_id, json_encode($data,JSON_UNESCAPED_UNICODE), $event, $link);
    $query = $DB->Query($sql, false, $err_mess.__LINE__);

    if($query->AffectedRowsCount() > 0) {


        return true;

    }

    return false;

  }

  public function getEvents(int $deal_id) : array {

    global $DB;

    $sql   = sprintf(self::GET_EVENTS, $deal_id);

    $query = $DB->Query($sql, false, $err_mess.__LINE__);

    $data = [];

    while($row = $query->Fetch()) {

         $data[] = $row;

    }

    return $data;

  }

  private function getData(int $id) : array {

    global $DB;

    $sql   = sprintf(self::GET_DATA, $id);

    $query = $DB->Query($sql, false, $err_mess.__LINE__);

    return json_decode($query->Fetch()['DATA'], 1);

  }

  private function exists(int $deal_id) : bool {

    global $DB;

    $sql   = sprintf(self::EXISTS, $deal_id);
    $query = $DB->Query($sql, false, $err_mess.__LINE__);

    if($query->SelectedRowsCount() > 0) {

       return true;

    }

    return false;
    

  }

  private function query(string $sql)  {



  }
}