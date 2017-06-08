<?php

namespace Core;

use Illuminate\Database\QueryException as QueryException;
use \Exception;

class Database {

  public static function updateSlug($model, $id, $column_name_for_slug_base){
    // example: 
    // id=1, slug=slug
    // id=2, slug=slug-1
    // id=3, slug=slug-2
    $status_code = STATUS_CODE_FAIL;
    $message = "";
    $data = null;
    $slug = null;
    $slug_increment = null;
    $m_instance = null;

    try{
      if($model){
        // get the slug base from name or title
        $m_instance = $model->find($id);
        $m_current_slug = $m_instance->slug;
        if($m_instance){
          $slug_base_raw = $m_instance->$column_name_for_slug_base;
          if(!empty($slug_base_raw)){
            $pattern = array("!\s+!", "/[^a-zA-Z0-9-]/");
            $replace = array("-", "");
            
            // generate slug base to be searched and incremented
            $slug_base = strtolower(preg_replace($pattern, $replace, $slug_base_raw));

            // check in database
            $query = $model->where("slug", $slug_base)
              ->orWhere("slug", 'LIKE', $slug_base . "-%")
              ->orderBy("id", "desc")
              ->first();

            if($query){
              $last_slug_in_db = $query->slug;
              // case 1: second slug
              if($last_slug_in_db == $slug_base){
                $slug = $slug_base . "-1";
              } else {
                // case 2: >2
                if($last_slug_in_db == $m_current_slug){
                  // case where in database only has ONE slug-id
                  // skip. no need to self-increment.
                  $status_code = STATUS_CODE_SUCCESS;
                  $data = $m_current_slug;
                } else {
                  $slug_in_array = explode("-", $last_slug_in_db);
                  $last_slug = array_pop($slug_in_array);
                  if(is_numeric($last_slug)){
                    $slug_increment = intval($last_slug)+1;
                    $slug = $slug_base . "-". $slug_increment;
                  } else {
                    // error
                    echo "last slug should be integer. $last_slug_in_db.";
                  }
                }
              }
            } else {
              // slug doesn't exist in db. so, it should be the first one.
              $slug = $slug_base;
            }

            if(!empty($slug)){
              $m_instance->slug = $slug;
              $m_instance->save();
              $status_code = STATUS_CODE_SUCCESS;
              $data = $slug;
            }
          } else {
            $message .= "Empty $column_name_for_slug_base. Can't use this to generate slug. ";
          }
        } else {
          $message .= "Can't find m_instance. ";
        }
      } else {
        $message .= "Not model. ";
      }
    } catch (QueryException $e) {
      echo $e->getMessage();
    } catch (Exception $e) {
      echo $e->getMessage();
    }
                                        
    $result = array("status_code"=>$status_code, "message"=>$message, "data"=>$data);
    return $result;
  }
}