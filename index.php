<?php  
    session_start();
    require_once './library/config.php';

    $connection = new createConnection();
    $connection->connectToDatabase();

    $currentDate = date('Ymd');
    $csvDirectory = 'csv/';
    if (!is_dir($csvDirectory)) {
        mkdir($csvDirectory, 0777, TRUE);
    }
    $csvURL = "";
    $csvID = NULL;

    //GET LAST FEED ID
    $query = "SELECT * FROM feed ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($connection->myconn, $query) or die(mysqli_error($connection->myconn));
    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $csvURL = $row['url'];
        $csvID = $row['id'];
    } 

    if($csvURL == ""){
        echo 'Cannot find any url in your database';
        exit;
    }

    //DOWNLOAD CSV FILE
    $source = file_get_contents($csvURL);
    file_put_contents($csvDirectory.$currentDate.'.csv', $source);

    //CHECK FILE AVAILABILITY
    $headerLine = true;
    if(file_exists($csvDirectory.$currentDate.'.csv')){
        $row = 0;
        if (($handle = fopen($csvDirectory.$currentDate.'.csv', "r")) !== FALSE) {

            $date_time_inserted = "";
            $date_time_updated = "";
            $c_id = NULL;
            $c_title = "";
            $c_description = "";
            $c_image = "";

            while (($data = fgetcsv($handle, null, ';')) !== FALSE) {
                $num = count($data);
                if($headerLine) { 
                    $headerLine = false; 
                } else {
                    $row++;

                    $date_time_inserted = mysqli_real_escape_string($connection->myconn, $data[10]);
                    $date_time_updated = mysqli_real_escape_string($connection->myconn, $data[11]);
                    $c_id = mysqli_real_escape_string($connection->myconn, $data[0]);
                    $c_title = mysqli_real_escape_string($connection->myconn, $data[1]);
                    $c_description = mysqli_real_escape_string($connection->myconn, $data[4]);
                    $c_image = mysqli_real_escape_string($connection->myconn, $data[5]);

                    $ckQuery = "SELECT * FROM feed_content WHERE c_id = '$c_id'";
                    $ckResult = mysqli_query($connection->myconn, $ckQuery) or die(mysqli_error($connection->myconn));
                    if ($ckResult->num_rows == 0) {
                        $insertQuery = "INSERT INTO feed_content (feed_id,`status`,date_time_inserted,date_time_updated,c_id,c_title,c_description,c_image) VALUES ('$csvID','','$date_time_inserted','$date_time_updated','$c_id','$c_title','$c_description','$c_image')";
                        $resultQuery = mysqli_query($connection->myconn, $insertQuery) or die(mysqli_error($connection->myconn));
                    } else {
                        $updateQuery = "UPDATE feed_content SET 
                        feed_id = '$csvID', 
                        date_time_inserted = '$date_time_inserted',
                        date_time_updated = '$date_time_updated',
                        c_title = '$c_title',
                        c_description = '$c_description',
                        c_image = '$c_image' WHERE c_id = '$c_id'";
                        $resultUpdateQuery = mysqli_query($connection->myconn, $updateQuery) or die(mysqli_error($connection->myconn));
                    }

                    
                }
            }
            fclose($handle);
        }
    }
    echo 'CSV import process has been completed';