<?php
/**
 * PLUGIN NAME: wide_to_long.php
 * DESCRIPTION: This plugin outputs the data from repeated sections in a given project to a csv file, transforming the data from
 *              wide (i.e. different fields for each occurrence) to long (i.e. one record for each occurrence)
 * VERSION:     1.0
 * AUTHOR:      Sue Lowry - University of Minnesota
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../redcap_connect.php";

if (!isset($_GET['pid'])) {
    exit("Project ID is required");
}
if (!isset($_GET['set'])) {
    exit("Set ID is required");
}
$pid = $_GET['pid'];
$set = $_GET['set'];
$pid_ok = 0;
if ($pid == 1164 and $set == 'aes') { # Losartan Protocol CRFs - AEs
    $pid_ok = 1;
    $ptitle = 'Losartan Protocol CRFs';
    $ftitle = 'Adverse Events';
    $fname = 'adverse_events';
    $a_prefixes = array('ae_1', 'ae_2', 'ae_3', 'ae_4', 'ae_5');
    $a_suffixes = '';
    $a_fields = array('event', 'desc', 'start_date', 'start_time', 'end_date', 'end_time', 'grade', 
                      'relatedness', 'act_tak_stu_int', 'action_exp', 'oth_act_tak', 'oth_action_exp', 'outcome', 'sae', 'sae_exp');
}

if ($pid_ok == 0) {
    exit("Project # " . $_GET['pid'] . " has not been set up for the set " . $_GET['set'] . " for this plugin");
}

$flds = '';
foreach($a_prefixes as $prefix) {
    $a_flds[$prefix] = '';
    foreach ($a_fields as $fld) {
        $flds .= ", '$prefix$fld'";
        $a_flds[$prefix] .= ", '$prefix$fld'";
    }
    $a_flds[$prefix] = substr($a_flds[$prefix], 2);
}
foreach($a_suffixes as $suffix) {
    $a_flds[$suffix] = '';
    foreach ($a_fields as $fld) {
        $flds .= ", '$suffix$fld'";
        $a_flds[$suffix] .= ", '$suffix$fld'";
    }
    $a_flds[$suffix] = substr($a_flds[$suffix], 2);
}
$flds = substr($flds, 2);

// OPTIONAL: Your custom PHP code goes here. You may use any constants/variables listed in redcap_info().

if (!SUPER_USER) {
    $sql = sprintf( "
            SELECT p.app_title
              FROM redcap_projects p
              LEFT JOIN redcap_user_rights u
                ON u.project_id = p.project_id
             WHERE p.project_id = %d AND (u.username = '%s' OR p.auth_meth = 'none')",
                     $_REQUEST['pid'], $userid);

    // execute the sql statement
    $result = $conn->query( $sql );
    if ( ! $result )  // sql failed
    {
        die( "Could not execute SQL: <pre>$sql</pre> <br />" .  mysqli_error($conn) );
    }

    if ( mysqli_num_rows($result) == 0 )
    {
        die( "You are not validated for project # $project_id ($app_title)<br />" );
    }
}

$sql = sprintf( "
    SELECT m.field_name
      FROM redcap_metadata m
     WHERE m.project_id = %d
       AND m.field_order = 1",
             $pid);
#print "<br/>sql: $sql<br/><br/><br/>";

// execute the sql statement
$records_result = $conn->query( $sql );
if ( ! $records_result )  // sql failed
{
      die( "Could not execute SQL: <pre>$sql</pre> <br />" .  mysqli_error($conn) );
}
$first_field_name = '';
while ($rrec = $records_result->fetch_assoc( ))
{
  $first_field_name = $rrec['field_name'];
}

$a_out_flds[] = $first_field_name;
$a_out_flds[] = 'set';
foreach ($a_fields as $fld) {
    $a_out_flds[] .= $fld;
}

$sql = sprintf( "
    SELECT distinct d.record
      FROM redcap_data d
     WHERE d.project_id = %d
       AND d.field_name in (%s)
     ORDER BY d.record",
             $pid, $flds);
#print "<br/>sql: $sql<br/><br/><br/>";

// execute the sql statement
$records_result = $conn->query( $sql );
if ( ! $records_result )  // sql failed
{
      die( "Could not execute SQL: <pre>$sql</pre> <br />" .  mysqli_error($conn) );
}
while ($rrec = $records_result->fetch_assoc( ))
{
    $recs[] = $rrec['record'];
}
#print "<br/><br/>recs: ";
#print_r($recs);
#print "<br/><br/>";

// Set file name and path
$filename = APP_PATH_TEMP . "w2l_" . date("YmdHis") . '_' . PROJECT_ID . "_" . $set . '.csv';
#print "filename: $filename<br/><br/>";

// Begin writing file from query result
$fp = fopen($filename, 'w');

if ($fp)
{
    // Write headers to file
    fputcsv($fp, $a_out_flds);

    foreach($recs as $this_rec) {
        if ($this_rec == '') { continue; }

        foreach($a_flds as $flds_key => $flds_val) {
            $sql = sprintf( "
                SELECT '%s' as record, m.field_order, m.field_name, d.value
                  FROM redcap_metadata m
                  LEFT JOIN redcap_data d
                    ON d.project_id = m.project_id
                   AND d.field_name = m.field_name
                   AND d.record = '%s'
                 WHERE m.project_id = %d
                   AND m.field_name in (%s)
                 ORDER BY m.field_order",
                         $this_rec, $this_rec, $pid, $flds_val);
            #print "<br/>sql: $sql<br/><br/><br/>";

            // execute the sql statement
            $records_result = $conn->query( $sql );
            if ( ! $records_result )  // sql failed
            {
                  die( "Could not execute SQL: <pre>$sql</pre> <br />" .  mysqli_error($conn) );
            }
            $a_vals[] = $this_rec;
            $a_vals[] = $flds_key;
            $output = '';
            $any_found = 'N';
            while ($rrec = $records_result->fetch_assoc( ))
            {
                $a_vals[] = $rrec['value'];
                $output .= $rrec['field_name'].": ".$rrec['value']."<br/>";
                if ($rrec['value'] > '') { $any_found = 'Y'; }
            }
            if ($any_found == 'Y') {
                // Write to file
                fputcsv($fp, $a_vals);
            }
            unset($a_vals);
        }
    }
    // Close file for writing
    fclose($fp);

    // Close file for writing
    fclose($fp);
    db_free_result($result);

    // Open file for downloading
    $download_filename = camelCase(html_entity_decode($app_title, ENT_QUOTES)) . "_".$set."_" . date("Y-m-d_Hi") . ".csv";
    header('Pragma: anytextexeptno-cache', true);
    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=$download_filename");

    // Open file for reading and output to user
    $fp = fopen($filename, 'rb');
    print fread($fp, filesize($filename));

    // Close file and delete it from temp directory
    fclose($fp);
    unlink($filename);
}