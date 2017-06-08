<?php


namespace Controller;

use Core\LoginHelper as LoginHelper;
use Core\Database as DBHelper;

class Base
{
    public static function getAndFilterTagFromReqBody($request, $tag_keyword)
    {
        $unfiltered_tags = array();
        $filtered_tags   = array();
        // get tag keyword to be searched from POST
        // if no keyword being passed. will get project pagination()
        $data = $request->getParsedBody();
        if ($data) {
            if (array_key_exists($tag_keyword, $data)) {
                $tag_from_req = $data[$tag_keyword];
                if (is_array($tag_from_req)) {
                    // INVALID FORM.
                    //return "TAG IS IN ARRAY. SHOULD ONLY BE IN STRING, COMMA SEPARATED"; // TODO:
                    $unfiltered_tags = $tag_from_req;
                } else if (is_string($tag_from_req)) {
                    $trimmed_spaced_string = trim($tag_from_req);
                    $unfiltered_tags       = explode(",", $trimmed_spaced_string);
                }
            }
        }

        // filter for empty string as tag or duplicate tags
        // THERE IS STILL A BUG, if there are space in between tag, it will still count.
        // TRIM doesnt fully functional to remove white space. TODO!!!
        if (!empty($unfiltered_tags)) {
            foreach ($unfiltered_tags as $tag) {
                // SQL Injection Protection
                // Remove any special character
                $pattern      = array("/[^a-zA-Z0-9]/");
                $replace      = array("");
                $tag_filtered = preg_replace($pattern, $replace, $tag);
                if (!empty($tag_filtered) && !in_array($tag_filtered, $filtered_tags)) {
                    array_push($filtered_tags, $tag_filtered);
                }
            }
        }

        return $filtered_tags;
    }

    public static function validateEmail($email)
    {
        $status   = false;
        $ip4_addr = false;
        if ($email && !empty($email)) {
            if (strpos($email, '@') != false) {
                $explode = explode('@', $email);
                if (count($explode) == 2) {
                    $email_address = $explode[0];
                    $host          = $explode[1];
                    if (strpos($host, '.') != false) {
                        $ip4_addr = gethostbynamel($host);
                        if ($ip4_addr != false) {
                            if(intval(getenv("VALIDATE_EMAIL")) == 1){
                                if (checkdnsrr($host, "MX")) {
                                    // check domain + email
                                    $status = true;
                                }
                            } else {
                                // just check domain
                                $status = true;
                            }
                        }
                    }
                }
            }
        }
        return $status;
    }

    // status:
    // 0 = column name exist & editable & update failed
    // 1 = column name exist & editable & update success
    // -1 = column name doesn't exist
    // -2 = column name exist & not editable (e.g id, user_id)
    public static function updateByIDS($id_keyword, $service, $request, $response, $args)
    {
        $data   = $request->getParsedBody(); // in format array;
        $result = array();
        if (!empty($args)) {
            if (array_key_exists($id_keyword, $args)) {
                $id = $args[$id_keyword];
                if ($data) {
                    // verify the user exist
                    $object_status = $service->findByID_M($id);
                    if ($object_status["status_code"] == STATUS_CODE_SUCCESS) {
                        $obj = $object_status["data"];
                        if (is_object($obj)) {
                            // now iterate all the data to be updated
                            $database_changes = array();
                            foreach ($data as $column_name => $new_value) {
                                // check if the column_name exist in database
                                $status = $service->checkColName($column_name);
                                if ($status >= 0) {
                                    // update the database and store to output json status report
                                    // 0 = update failed, 1 = update success , -1 = disabled or column name doesn't exist

                                    $database_changes[$column_name] = $service->updateDB($obj, $column_name, $new_value);
                                } else {
                                    $database_changes[$column_name] = $status;
                                }
                            }
                            $result["db"]      = $database_changes;
                            $result["status"]  = 1;
                            $result["message"] = "Finished Updating Data.";
                        } else {
                            $result["status"]  = 0;
                            $result["message"] = "invalid id: $id to update. it is not a valid object.";
                        }
                    } else {
                        $result["message"] = "ID: $id is not found. ";
                    }
                } else {
                    $result["status"]  = 0;
                    $result["message"] = "missing data";
                }
            } else {
                $result["status"]  = 0;
                $result["message"] = "Args doesn't consist the expected id keyword: $id_keyword";
            }
        } else {
            $result["status"]  = 0;
            $result["message"] = "empty arguments";
        }
        return json_encode($result);
    }

    // status:
    // 0 = column name exist & editable & update failed
    // 1 = column name exist & editable & update success
    // -1 = column name doesn't exist
    // -2 = column name exist & not editable (e.g id, user_id)
    public static function updateBySlugs($slug_keyword, $service, $request, $response, $args)
    {
        $data   = $request->getParsedBody(); // in format array;
        $result = array();
        if (!empty($args)) {
            if (array_key_exists($slug_keyword, $args)) {
                $slug = $args[$slug_keyword];
                if ($data) {
                    // verify the user exist
                    $object_status = $service->findBySlug_M($slug);
                    if ($object_status["status_code"] == STATUS_CODE_SUCCESS) {
                        $obj = $object_status["data"];
                        if (is_object($obj)) {
                            // now iterate all the data to be updated
                            $database_changes = array();
                            foreach ($data as $column_name => $new_value) {
                                // check if the column_name exist in database
                                $status = $service->checkColName($column_name);
                                if ($status >= 0) {
                                    // update the database and store to output json status report
                                    // 0 = update failed, 1 = update success , -1 = disabled or column name doesn't exist
                                    $database_changes[$column_name] = $service->updateSlug($obj, $column_name, $new_value);
                                } else {
                                    $database_changes[$column_name] = $status;
                                }
                            }
                            $result["db"]      = $database_changes;
                            $result["status"]  = 1;
                            $result["message"] = "Finished Updating Data.";
                        } else {
                            $result["status"]  = 0;
                            $result["message"] = "invalid slug: $slug to update. it is not a valid object.";
                        }
                    } else {
                        $result["message"] = "Slug: $slug is not found. ";
                    }
                } else {
                    $result["status"]  = 0;
                    $result["message"] = "missing data";
                }
            } else {
                $result["status"]  = 0;
                $result["message"] = "Args doesn't consist the expected id keyword: $slug_keyword";
            }
        } else {
            $result["status"]  = 0;
            $result["message"] = "empty arguments";
        }
        return json_encode($result);
    }

    public static function isLogin()
    {
        return LoginHelper::getLoginInfo();
    }
    
     public static function clear()
    {
        return LoginHelper::getLoginInfo();
    }

    public static function AuthTokenWithLogin($jwt)
    {
        $result = array("status" => false, "message" => "", "profile_id" => null);
        $login  = LoginHelper::getLoginInfo();
        if ($login["status"] == true) {
            $login_id = $login['profile_id'];
            if ($login_id && $login_id > 0) {
                if ($jwt) {
                    $profile_id_from_jwt = $jwt->data->profile_id;
                    if ($profile_id_from_jwt && $profile_id_from_jwt > 0) {
                        if ($login_id == $profile_id_from_jwt) {
                            $result["status"]     = true;
                            $result["message"]    = "Success. Matched!";
                            $result["profile_id"] = $login_id;
                        } else {
                            $result["message"] = "Mismatch between jwt token profile id and user login id";
                        }
                    } else {
                        $result["message"] = "Valid User logged in but has invalid jwt data payload";
                    }
                } else {
                    $result["message"] = "Valid User logged in but has empty JWT";
                }
            } else {
                $result["message"] = "User has logged in but has invalid Login ID";
            }
        } else {
            $result["message"] = "Please Login First.";
        }
        return $result;
    }

    public static function validateNumber($year_from, $year_to)
    {
        $result = array("status" => false, "message" => "");
        if (!empty($year_from) && !empty($year_to)) {
            if (preg_match("/[1-2]{1}[0-9]{3}/", $year_from)) {
                if (preg_match("/[1-2]{1}[0-9]{3}/", $year_to)) {
                    if ($year_to >= $year_from) {
                        $result["status"] = true;
                    } else {
                        $result["message"] = "Please input ending year more than beginning year!";
                    }
                } else {
                    $result["message"] = "Invalid Year To ($year_to). ";
                }
            } else {
                $result["message"] = "Invalid Year From ($year_from). ";
            }
        } else {
            $result["message"] = "Empty year_from ($year_from) or year_to ($year_to). ";
        }
        return $result;
    }

    public static function isMobile()
    {
        $result = false; // by default is desktop

        if (isset($_SESSION['user_agent']) && ($_SESSION['user_agent'] == USER_AGENT_MOBILE)) {
            $result    = true;
            //$useragent = $_SERVER['HTTP_USER_AGENT'];
            //echo $useragent;
        }
        return $result;
    }

    public static function generateSlug($model, $id, $column_name_for_slug_base){
        return DBHelper::updateSlug($model, $id, $column_name_for_slug_base);
    }
    
    public static function getProfileProperties($id, $model, $field) {
        $result = $service->findProfileProperties_M($model, $id)->$field;
        return $result;
    }
    
    public static function lastVisitURL($url) { 
        return $_SESSION['last_visit'] = $url; 
    }
    
    public static function toAscii($str){
        setlocale(LC_ALL, 'en_US.UTF8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9/_| -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$result = preg_replace("/[/_| -]+/", '-', $clean);

	return $result;
    }
    
    public static function pivot(){
      
       // $P1_K1_GAZINGS="";
        $result = [];
        $month = '08';
        $year = '2016';
        $rows = [];
        $n = 1;

        $kalender = CAL_GREGORIAN;
        $date_range = array_map(function($d) use ($month, $year) {
            return implode('/', [STR_PAD($d, 2, '0', STR_PAD_LEFT), $month, $year]);
        }, range(1, cal_days_in_month($kalender, $month, $year)));
        
              
        $login = Base::isLogin();
        if ($login['status']) {
            $P1_K1_GAZINGS = $this->report_service->P1_K1_GAZINGS();
            $datas = $P1_K1_GAZINGS['data'];
           
            foreach ($datas as $data) {
                $nama = $data->Expr1;
                $group = $data->header;
                $date = $data->date_detail;
                $defect = $data->name;
                $kw3 = $data->kw3;
                $kw4 = $data->kw4;
                $kw5 = $data->kw5;

                if (!isset($result[$group])) {
                    $result[$group] = [];
                }
                if (!isset($result[$group][$defect])) {
                    $result[$group][$defect] = [];
                }

                if (!isset($result[$group][$defect][$nama])) {
                    $result[$group][$defect][$nama]['kw3'] = [];
                    $result[$group][$defect][$nama]['kw4'] = [];
                    $result[$group][$defect][$nama]['kw5'] = [];
                }
                $result[$group][$defect][$nama]['kw3'][$date] = $kw3;
                $result[$group][$defect][$nama]['kw4'][$date] = $kw4;
                $result[$group][$defect][$nama]['kw5'][$date] = $kw5;
            }
          
            $total_per_tanggal = [];
            foreach ($result as $nama_defect => $list_barangs) {
                $rows[$n] = [];
                $rows[$n][] = [
                    'rowspan' => count($list_barangs) * 3,
                    'label' => $nama_defect
                ];

                foreach ($list_barangs as $nama_barang => $list_barang) {
                 //   $rows[$n] = [];
                    $rows[$n][] = [
                        'rowspan' => count($list_barang) * 3,
                        'label' => $nama_barang
                    ];

                    foreach ($list_barang as $defect => $list_defect) {
                        $rows[$n][] = [
                            'rowspan' => 3,
                            'label' => $defect
                        ];
                        foreach ($list_defect as $kw => $list_kw) {
                            $rows[$n][] = [
                                'rowspan' => 1,
                                'label' => $kw
                            ];

                            $total_kw = 0;
                            foreach ($date_range as $tgl) {
                                $nilai = isset($list_kw[$tgl]) ? $list_kw[$tgl] : 0;
                                $rows[$n][] = [
                                    'rowspan' => 1,
                                    'label' => isset($list_kw[$tgl]) ? $list_kw[$tgl] : '-'
                                ];
                                $total_kw += $nilai;

                                if (!isset($total_per_tanggal[$tgl])) {
                                    $total_per_tanggal[$tgl] = 0;
                                }
                                $total_per_tanggal[$tgl] += $nilai;
                            }

                            $rows[$n][] = [
                                'rowspan' => 1,
                                'label' => $total_kw
                            ];

                            $n++;
                        }
                        $n++;
                    }
                    $n++;
                }
            }
           
            $dates = $date_range;
            $data=array(
                'dates' => $dates,
                'rows' => $rows,
                'totals' => $total_per_tanggal
            );
        } 
        return $data;
    }
    
}
