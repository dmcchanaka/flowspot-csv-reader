<?php  
    session_start();
    require_once './library/config.php';

    $currentDate = date('Ymd');
    $csvDirectory = 'csv/';
    if (!is_dir($csvDirectory)) {
        mkdir($csvDirectory, 0777, TRUE);
    }
    //GET LAST FEED ID
    $query = "SELECT * FROM feed WHERE `status`= 'active' ORDER BY id";
    $result = $dbc->query($query) or die ($dbc->error . " (".$dbc->errno.")");
    if ($result->num_rows > 0) {
        while($row = mysqli_fetch_assoc($result)){

            //DOWNLOAD CSV FILE
            $source = file_get_contents($csvURL);
            file_put_contents($csvDirectory.$currentDate.'.csv', $source);

            //CHECK FILE AVAILABILITY
            $headerLine = true;
            if(file_exists($csvDirectory.$currentDate.'.csv')){
                $count = 0;
                if (($handle = fopen($csvDirectory.$currentDate.'.csv', "r")) !== FALSE) {

                    $date_time_inserted = "";
                    $date_time_updated = "";
                    $c_id = NULL;
                    $c_unique_id = NULL;
                    $c_title = "";
                    $c_description = "";
                    $c_image = "";

                    $csv = array();
                    $columnnames = fgetcsv($handle, null, ';');
                    while (($data = fgetcsv($handle, null, ';')) !== FALSE) {
                        $num = count($data);
                        if($headerLine) { 
                            $headerLine = false; 
                        } else {
                            $count++;
                            $newRow = array_combine($columnnames, $data);
                            $csv[] = $newRow;
                        }
                    }

                    if(sizeof($csv)>0){
                        foreach($csv as $key=>$val){
                            if(isset($row['mapping_c_id']) && $row['mapping_c_id']!="" && isset($val[$row['mapping_c_id']])){
                            $c_id = mysqli_real_escape_string($dbc, $val[$row['mapping_c_id']]);
                            }
                            if(isset($row['mapping_c_title']) && $row['mapping_c_title']!="" && isset($val[$row['mapping_c_title']])){
                            $c_title = mysqli_real_escape_string($dbc, $val[$row['mapping_c_title']]);
                            }
                            if(isset($row['mapping_c_description']) && $row['mapping_c_description']!="" && isset($val[$row['mapping_c_description']])){
                                $c_description = mysqli_real_escape_string($dbc, $val[$row['mapping_c_description']]);
                            }
                            if(isset($row['mapping_c_image']) && $row['mapping_c_image']!="" && isset($val[$row['mapping_c_image']])){
                                $c_image = mysqli_real_escape_string($dbc, $val[$row['mapping_c_image']]);
                            }
                            if(isset($row['mapping_c_unique_id']) && $row['mapping_c_unique_id']!="" && isset($val[$row['mapping_c_unique_id']])){
                                $c_unique_id = mysqli_real_escape_string($dbc, $val[$row['mapping_c_unique_id']]);
                            }
                            $date_time_inserted = date('Y-m-d H:i:s');
                            $date_time_updated = date('Y-m-d H:i:s');
                            
                            $ckQuery = "SELECT * FROM feed_content WHERE c_id = '$c_id'";
                            $ckResult = $dbc->query($ckQuery) or die ($dbc->error . " (".$dbc->errno.")");
                            if ($ckResult->num_rows == 0) {
                                $insertQuery = "INSERT INTO feed_content (feed_id,`status`,date_time_inserted,date_time_updated,c_id,c_title,c_description,c_image,c_unique_id) VALUES ('$csvID','active','$date_time_inserted','$date_time_updated','$c_id','$c_title','$c_description','$c_image','$c_unique_id')";
                                $resultQuery = $dbc->query($insertQuery) or die ($dbc->error . " (".$dbc->errno.")");
                            } else {
                                $updateQuery = "UPDATE feed_content SET 
                                feed_id = '$csvID', 
                                date_time_updated = '$date_time_updated',
                                c_title = '$c_title',
                                c_description = '$c_description',
                                c_unique_id = '$c_unique_id',
                                c_image = '$c_image' WHERE c_id = '$c_id'";
                                $resultUpdateQuery = $dbc->query($updateQuery) or die ($dbc->error . " (".$dbc->errno.")");
                            }
                        }
                    }

                    fclose($handle);
                }
            }
        }
    }
    echo 'CSV import process has been completed';