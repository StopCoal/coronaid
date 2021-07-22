<?php

if(isset($_POST['save'])){
    $ids = egc_split_ids($_POST["event_list"]);
    egc_db_store($ids);
    egc_header();
    egc_notify("Created Matches using submitted Events List!");
}
if(isset($_POST['per_camp'])){
    egc_csv_infected_camp();
}
if(isset($_POST['match1'])){
    $ids = egc_split_ids($_POST["id"]);
    foreach ($ids as $id) {
        $matches = egc_match1($id);
        if ($matches !="No matches known!") {
            $matches = egc_deduplicate_matches($matches);
            egc_header();
            egc_table_head();
            foreach ($matches as $match) {
                ?>
                <tr>
                <?php
                echo "<td>" . $match['id'] . "</td><td>" . $match['from'] . "</td><td>" . $match['to'] . "</td>";
                ?>
                </tr>
                <?php
            }
        }
        else {
            egc_alert($matches);
        }
        ?>
        </tbody>
        </table>
        <?php
    }
}
if(isset($_POST['camp_view'])){
    $camp = egc_split_ids($_POST["camp"]);
    $inhs = egc_get_inhabitants($camp[0]);
    egc_header();
    egc_table_head_inhs();
    foreach ($inhs as $inh) {
        if ($inh['positive']) {
            $inf_status = "Infected!";
        }
        else {
            $inf_status = "No infection known!";
        }
        ?>
        <tr>
        <?php
        echo "<td>" . $inh['id'] . "</td><td>" . $inf_status . "</td><td>" . $inh['camp'] . "</td>";
        ?>
        </tr>
        <?php
    }
        ?>
        </tbody>
        </table>
        <?php
}
if(isset($_POST['camp_list'])){
    $ids = egc_split_ids($_POST["camp_ids"]);
    $camp = $_POST["camp"];
    foreach ($ids as $id) {
        egc_set_to_camp($id, $camp);
    }
}
if(isset($_POST['match1_csv'])){
    $ids = egc_split_ids($_POST["id"]);
    foreach ($ids as $id) {
        $matches = egc_match1($id);
        egc_csv_matches($id,$matches);
    }
}
if(isset($_POST['update'])){
    egc_db_update();
}
if(isset($_POST['set_positive'])){
    $ids = egc_split_ids($_POST["id"]);
    foreach ($ids as $id) {
        egc_set_infected($id, true);
        egc_notify("Setted " . $id . " as infected!");
    }
}
if(isset($_POST['set_negative'])){
    $ids = egc_split_ids($_POST["id"]);
    foreach ($ids as $id) {
        egc_set_infected($id, false);
        egc_notify("Setted " . $id . " as healthy!");
    }
}

if(isset($_POST['match1_csv_all'])){
    $infected_ids = egc_get_infected();
    if ($infected_ids) {
        $all_matches = array();
        foreach ($infected_ids as $infected_id) {
            $matches = egc_match1($infected_id);
            if (count($all_matches) >= 1) {
                array_merge($all_matches, $matches);
            }
            else {
                $all_matches = $matches;
            }
        }
        egc_csv_matches("all",egc_deduplicate_matches($all_matches));
        
    }
}
/*
*   egx split_ids   // Splits text input into array by using various dividers
*   params: $event list (List of Atendees)
*   returns: $ids array[String] (Array of Atendees)
*/
function egc_split_ids($event_list) {
    
    $ids = preg_split ("/[\s,;\/]+/", $event_list);
    return $ids;
}
/*
*   egx egc_db_store   // Stores List of Event Atendees into Matching Database
*   params: $ids (Array of Atendees)
*   returns:
*/
function egc_db_store($ids) {
    
    if(isset($_POST['date'])){
        $date = $_POST['date'];
    } else {
        $date = egc_get_date();
    }
    foreach ($ids as $id) {
        $matched_ids = $ids;
        unset($matched_ids[$id]);
        foreach($matched_ids as $matched_id) {
            egc_sql_matches($id , $matched_id , $date );
        }
    }
}
function egc_get_inhabitants($camp) {
    $sql = "SELECT * FROM atendees WHERE camp=?";
    $conn = egc_connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $camp);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        $inh['id'] = $row['ID'];
        $inh['positive'] = $row['positive'];
        $inh['camp'] = $row['camp'];
        $inhs[] = $inh;
    }
    return $inhs;
}
function egc_set_infected($id, $status) {
    if ($status == true) {
        $sql = " UPDATE `atendees` SET `positive`=true WHERE ID=?";
    }
    if ($status == false) {
        $sql = " UPDATE `atendees` SET `positive`=false WHERE ID=?";
    }
    $conn = egc_connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

function egc_db_update() {
    $added_ids = 0;
    $row = 1;
    if (($handle = fopen("corona_ids.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            $id = $data[0];
            if (count($data) > 1) {
                $camp = $data[1];
            }
            else {
                $camp = "n/a";
            }
            //Check if already registred
            $sql = "SELECT * FROM atendees WHERE id=? LIMIT 1";
            $conn = egc_connect_db();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if( ($result->fetch_array(MYSQLI_NUM)) == NULL ) {
                //Register
                $sql = "INSERT INTO `atendees` (`ID`, `positive`, `camp`) VALUES (?, '0', ?)";
                $conn = egc_connect_db();
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $id, $camp);
                $stmt->execute();
                $added_ids++;
            }
        }
        egc_notify("added ". $added_ids . " IDs to database!");
        fclose($handle);
    }
}
/*
*   egx egc_get_date   // Returns Friendly Date
*   params: 
*   returns: $date (String)
*/
function egc_get_date() {
    return date('d.m.Y');
}
/*
*   egx egc_sql_matches  // Inputs Atendee-IDs into DB
*   params: $id, $matched_id, $date
*   returns: 
*/
function egc_sql_matches($id, $matched_id, $date) {
    if ($id != $matched_id) {
        if ($id==NULL || $matched_id==NULL) {
            return false;
        }
        //Check if id exists at all
        $sql = "SELECT * FROM atendees WHERE id=? LIMIT 1";
        $conn = egc_connect_db();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if( ($result->fetch_array(MYSQLI_NUM)) == NULL ) {
            egc_alert($id . " is not registred as atendee yet!");
        }

        //Check, if duplicate exists
        $sql = "SELECT * FROM `matches` WHERE persona=? AND personb=? AND date=? LIMIT 1";
        $conn = egc_connect_db();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $id, $matched_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        if( ($result->fetch_array(MYSQLI_NUM)) != NULL ) {
            return false;
        }

        //Check, if reverse duplicate exists
        $stmt->bind_param("sss", $matched_id, $id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        if( ($result->fetch_array(MYSQLI_NUM)) != NULL ) {
            return false;
        }

        $sql = 'INSERT INTO matches (persona, personb, date) VALUES (?, ?, ?)';
        $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $id, $matched_id, $date);
            $stmt->execute();
            $stmt->close();
    }
}
/*
*   egc_set_to_camp   // Stores Atendee as inhabitant to camp x
*   params: $id (Atendee), $camp (Camp)
*   returns: 
*/
function egc_set_to_camp($id, $camp) {
    $sql = 'INSERT IGNORE INTO atendees SET id=?, positive = 0, camp=?';
    $conn = egc_connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $id, $camp);
    $stmt->execute();
}
/*
*   egx connect_db  // Connects to mariaDB via php mysqli
*   params: 
*   returns: $mysqli Object
*/
function egc_connect_db() {
    $mysqli = new mysqli("localhost", "****", "***", "****");

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    return $mysqli;
}
function egc_alert($error) {
    ?><div class="alert alert-danger" role="alert"><?php
        echo $error;
    ?></div><?php
}
function egc_notify($string) {
    ?><div class="alert alert-primary" role="alert"><?php
        echo $string;
    ?></div><?php
}
function egc_deduplicate_matches($matches) {
    $key = 0;
    $ids_index = array();
    $deduplicated_matches = array();
    foreach ($matches as $match) {
        if(!in_array($match['id'], $ids_index)) {
            $ids_index[] = $match['id'];
            $deduplicated_matches[] = $match;
        }
        else {
            $key_found = array_search($match['id'], $deduplicated_matches);
            $found_from = new DateTime($deduplicated_matches[$key_found]['from']);
            $found_to = new DateTime($deduplicated_matches[$key_found]['to']);
            $this_from = new DateTime($match['from']);
            $this_to = new DateTime($match['to']);
            if ($this_from < $found_from) {
                $matches[$key_found]['from'] = $deduplicated_matches[$key_found]['from'];
            }
            if ($this_to > $found_to) {
                $matches[$key_found]['to'] = $deduplicated_matches[$key_found]['to'];
            }
        }
        $key++;
    }
    return $deduplicated_matches;
}


/*
*   egx egc_match1  // Finds all first degree matches for id
*   params: $id
*   returns: $match_id(Array[String])
*/
function egc_match1($id) {
    $sql = "SELECT * FROM matches WHERE persona=? OR personb=?";
    $conn = egc_connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $matches=false;
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        if ($row['persona'] == $id) {
            $new_match['id'] = $row['personb'];
            $new_match['date'] = $row['date'];

        }
        if ($row['personb'] == $id) {
            $new_match['id'] = $row['persona'];
            $new_match['date'] = $row['date'];
        }
        $matches[] = $new_match;
    }
    if (!$matches) {
        return "No matches known!";
    }
    $rmatches = $matches;
    foreach ($matches as $match) {
        $return_match['id'] = $match['id'];
        $return_match['from'] = $match['date'];
        $return_match['to'] = $match['date'];
        $id = $match['id'];
        $date = new DateTime($match['date']);

        foreach ($rmatches as $rmatch) {
            $rmatch_date = new DateTime($rmatch['date']);
            if (($rmatch['id'] == $id) && ($date != $rmatch_date)) {
                //$return_match['from'] = "Gibt Match an anderem Tag für die beiden";
                //ist er noch früher?
                if ($date < $rmatch_date) {
                    $return_match['to'] = $rmatch_date;
                }
                //oder ist er noch später?
                if ($date > $rmatch_date) {
                    $return_match['from'] = $rmatch_date;
                }
            }
        }
        if (!(is_string($return_match['from']) )) {
            $return_match['from'] = $return_match['from']->format('d.m.Y');
        }
        if (!(is_string($return_match['to']) )) {
            $return_match['to'] = $return_match['to']->format('d.m.Y');
        }
        $return_matches[] = $return_match;
    }

    $return_matches = array_map("unserialize", array_unique(array_map("serialize", $return_matches)));
    return $return_matches;

}
/*
*   egc_get_infected   // Gets all Infected IDs
*   params: 
*   returns: $infected_ids (Array of Atendees IDs)
*/
function egc_get_infected() {
    $sql = "SELECT * FROM atendees WHERE positive=1";
    $conn = egc_connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        $infected_ids[] = $row['ID'];
    }
    return $infected_ids;
}

/*
*   egx egc_csv_matches  // Outputs matches array to CSV
*   params: $id, $matches
*   returns: 
*/
function egc_csv_matches($id, $matches) {
    $html = false;
    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' .$id .  '_matches.csv');
    }
	$output = fopen('php://output', 'w');
    if (!headers_sent()) {
        fputcsv($output, array('matched_ID','from', 'to'),';');
    }
	foreach ($matches as $match) {
        fputcsv($output, array( $match['id'],
                                $match['from'],
                                $match['to']
								),';');							
    }
    fclose($output);
}

function egc_table_head() {
    ?>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
            <th>ID</th>
            <th>from</th>
            <th>to</th>
            </tr>
        </thead>
        <tbody>
        <?php
}

function egc_table_head_inhs() {
    ?>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
            <th>ID</th>
            <th>Status</th>
            <th>Camp</th>
            </tr>
        </thead>
        <tbody>
        <?php
}

function egc_header() {
    ?>
    <head>
        <meta charset="utf-8">
        <title>EG Corona DB</title>
        <link rel="stylesheet" href="bootstrap.min.css" />
        <link rel="stylesheet" href="app.css" />
        <link rel="stylesheet" type="text/css" href="datatables.min.css"/>
    </head>
    <?php
}

function egc_csv_infected_camp() {

    $sql = "SELECT * FROM atendees WHERE positive=1";
    $conn = egc_connect_db();
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        $infected_ids[] = $row['ID'];
        $infected_ids_camp[] = $row['camp'];
    }
    $infected_camps = array();
    $infected_in_camp;
    $i = 0;
    foreach ($infected_ids as $infected_id) {
        if (!in_array($infected_ids_camp[$i], $infected_camps)) {
            $infected_camps[] = $infected_ids_camp[$i];
            $infected_in_camp[] = 1;
        } else {
            $key = array_search($infected_ids_camp[$i], $infected_camps);
            $infected_in_camp[$key] = $infected_in_camp[$key] + 1;
        }
        $i++;
    }
    //var_dump($infected_camps);
    //var_dump($infected_in_camp);

    $i = 0;

    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=infected_by_camps.csv');
    }
	$output = fopen('php://output', 'w');
    if (!headers_sent()) {
        fputcsv($output, array('ID','Count of Infected in same Camp'),';');
    }

    foreach ($infected_camps as $infected_camp) {
        $inhs = egc_get_inhabitants($infected_camp);
        //var_dump($inhs);
        foreach($inhs as $inh) {
            //var_dump($inh['id']);
            fputcsv($output, array( $inh['id'], $infected_in_camp[$i]), ';');
        }
        $i++;
    }
    fclose($output);

}
/*
CREATE TABLE `egc`.`atendees` ( `ID` VARCHAR(25) NOT NULL , `positive` BOOLEAN NOT NULL , `camp` VARCHAR(25) NOT NULL ) ENGINE = InnoDB;

*/
if (!headers_sent()) {
?>

<html>
    <?php egc_header();?>
    <body>
    <div id="container">
        <form method="post">
            <div class="form-group"> 
                <label id="first">Event List</label><br/>
                <textarea rows="15" class="form-control" placeholder="Enter List of IDs" name="event_list"><?php if (isset($_POST["event_list"])) {echo $_POST["event_list"];} ?></textarea><br/>
                <input type="text" name="date" placeholder="enter Date"><br />
            </div>
            <button class="btn btn-primary" type="submit" name="save" formtarget="_blank">Create Matches for this Event</button>
        </form>
        <form method="post"> 
            <div class="form-group">
                <label id="first">Camp inhabitants list</label><br/>
                <textarea class="form-control" rows="10" type="text" name="camp_ids" placeholder="Enter List of IDs that Live in this Camp"></textarea><br/>
                <input  class="form-control" type="text" name="camp" placeholder="Enter Name of Camp"><br/>
                <button class="btn btn-primary" type="submit" name="camp_list">Set to camp</button>
                <button class="btn btn-primary" type="submit" name="camp_view">View list of inhabitants</button>
            </div>
        </form>
        <form method="post"> 
            <label id="sec">View Data</label><br/>
            <input type="text" name="id"  placeholder="Enter ID(s)" <?php if (isset($_POST["id"])) {echo "value=\"".$_POST["id"]."\"";} ?>><br/>
            <button class="btn btn-primary" type="submit" name="match1" formtarget="_blank">Get Matches for ID</button>
            <button class="btn btn-primary" type="submit" name="match1_csv">Get CSV Matches for ID</button><br/>
            <button class="btn btn-primary" type="submit" name="set_positive">Set ID as infected</button>
            <button class="btn btn-primary" type="submit" name="set_negative">Unset ID as infected</button><br/>
            <button class="btn btn-primary" type="submit" name="match1_csv_all">Get ALL Matches with Infected Persons!</button>
        </form>

        <form method="post"> 
            <label id="sec">Update ID List (place corona_ids.csv in working directory)</label><br/>
            <button class="btn btn-primary" type="submit" name="update">Update List of IDs</button>
        </form>
        <form method="post"> 
            <button class="btn btn-primary" type="submit" name="per_camp">Get Infected in Camps</button>
        </form>
    </div>
    </body>
</html>

<?php
}