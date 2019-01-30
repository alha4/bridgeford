<?php

namespace Stat;

final class CompetitorEvent {

  private const EXISTS = 'SELECT ID FROM bf_stat_event WHERE DEAL_ID = %d';

  private const GET_DATA  = 'SELECT DATA FROM bf_stat_event WHERE DEAL_ID = %d ORDER BY DEAL_ID DESC';

  private const GET_EVENTS = 'SELECT * FROM bf_stat_event WHERE DEAL_ID = %d ORDER BY DATE_UPDATE DESC';

  private const CREATE_EVENT = "INSERT INTO bf_stat_event (DEAL_ID, DATA, EVENT, LINK) VALUES(%d, '%s', '%s', '%s')";

  public function run(int $deal_id, array $data) : bool {

    if(!$this->exists($deal_id)) {

      $this->create($deal_id, $data, 'ADD');

    }

    $currentData = $this->getData($deal_id);

    /*if($deal_id == 16) {

      unset($data[array_rand($data)]);
      
    }*/

    $currentIds = array_column($this->getData($deal_id), 'ID');

    $dataIds    = array_column($data, 'ID');

    if(count($data) == 0) {

      if($this->create($deal_id, [], 'ALL_DELETE')) {

       // echo 'все удалены <br>';


      } else {

       //echo 'ошибка';

      }

      return true;

    }

    #array_push($dataIds, 12314141);

    if($diff = array_diff($dataIds,$currentIds)) {
 
       foreach($diff as $key => $value) {

            $index = array_search($value, $dataIds);

            if($index !== false) {

               if($this->create($deal_id, $data, 'NEW', $data[$index]['URL'])) {

                  // echo 'добавлен новый конкурент <br>';


               } else {

                //  echo 'ошибка';

               }


            }
         
       }
       

    }
  
    #array_push( $currentIds, 775541145);

    if($diff = array_diff($currentIds, $dataIds)) {

      foreach($diff as $key => $value) {

        $index = array_search($value,  $currentIds);

        if($index !== false) {

           if($this->create($deal_id, $data, 'ONE_DELETE', $currentData[$index]['URL'])) {

             //  echo 'удалён один конкурент <br>';


           } else {

            //  echo 'ошибка';

           }


        }
     
     }

    }

    return false;

  }

  public function create(int $deal_id, array $data, string $event = '', string $link = '') : bool {

    global $DB;

    $sql   = sprintf(self::CREATE_EVENT, $deal_id, json_encode($data,JSON_UNESCAPED_UNICODE), $event, $link);
    $query = $DB->Query($sql, false, $err_mess.__LINE__);

    if($query->AffectedRowsCount() > 0) {


        return true;

    }

    echo $err_mess.__LINE__;

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