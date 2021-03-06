<?php
/**
 * PLUGIN NAME: wide_to_long.php
 * DESCRIPTION: This plugin outputs the data from repeated sections in a given project to a csv file, transforming the data from
 *              wide (i.e. different fields for each occurrence) to long (i.e. one record for each occurrence)
 * VERSION:     1.0
 *              1.1 - Added pid 3910 and longitudinal and checkbox functionality
 *              1.2 - Added pid 4298
 *              1.3 - I missed documenting a few changes
 *              1.4 - Added 10 more AEs for Losartan project
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
    #$fname = 'adverse_events';
    $a_prefixes = array('ae_1', 'ae_2', 'ae_3', 'ae_4', 'ae_5', 'ae_6', 'ae_7', 'ae_8', 'ae_9', 'ae_10', 'ae_11', 'ae_12', 'ae_13', 'ae_14', 'ae_15');
    $a_suffixes = '';
    $a_fields = array('event', 'desc', 'start_date', 'start_time', 'end_date', 'end_time', 'grade', 
                      'relatedness', 'act_tak_stu_int', 'action_exp', 'oth_act_tak', 'oth_action_exp', 'outcome', 'sae', 'sae_exp');
}
if ($pid == 2011 and $set == 'saes') { # CENIC Project 2 - Visit Data - SAEs
    $pid_ok = 1;
    $ptitle = 'CENIC Project 2 - Visit Data';
    $ftitle = 'Serious Adverse Events';
    #$fname = 'serious_adverse_event';
    $a_prefixes = array('sae_1', 'sae_2', 'sae_3', 'sae_4');
    $a_suffixes = '';
    $a_fields = array('exists', 'de_init', 'ae_num', 'date_reported', 'visit', 'date_resolution', 'texta', 'rnd', 'rnd_code', 
                      'start_prod', 'end_prod', 'textb', 'date', 'code', 'end_date', 'duration', 'dur_unit', 'desc', 'age', 
                      'gender', 'wt', 'relevant', 'int_init', 'category', 'death_date', 'expect', 'relationship', 'risk', 
                      'criteria', 'rev_date', 'action', 'report_date', 'report_ann', 'report_cc', 'lmp_date', 'pi_date');
}
if ($pid == 2011 and $set == 'tlfb') { # CENIC Project 2 - Visit Data - Timeline Followback
    $pid_ok = 1;
    $ptitle = 'CENIC Project 2 - Visit Data';
    $ftitle = 'Timeline Followback';
    #$fname = 'multiple forms';
    $a_prefixes = '';
    $a_suffixes = array('1', '2', '3', '4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25',
                        '26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50',
                        '51','52','53','54','55','56');
    $a_fields = array('tf_day', 'tf_s_cig', 'tf_u_cig', 'tf_nu_cig', 'tf_alcohol', 'tf_over_alcohol', 'tf_pot', 'tf_blunt_pot', 'tf_cigar', 
                      'tf_cigarillo', 'tf_littlecigar', 'tf_chew', 'tf_snus', 'tf_hookah', 'tf_bidis', 'tf_e_cig', 'tf_other', 'tf_21patch', 'tf_14patch', 
                      'tf_7patch', 'tf_patch', 'tf_2gum', 'tf_4gum', 'tf_gum', 'tf_2loz', 'tf_4loz', 'tf_loz', 'tf_spray', 
                      'tf_inhaler', 'tf_other_nic');
}
if ($pid == 3910 and $set == 'aes') { # Power to Quit II - Final - AEs
    $pid_ok = 1;
    $ptitle = 'Power to Quit II - Final';
    $ftitle = 'Adverse Events';
    #$fname = 'adverse_events';
    $a_prefixes = array('ae1_', 'ae2_', 'ae3_', 'ae4_', 'ae5_', 'ae6_', 'ae7_', 'ae8_', 'ae9_', 'ae10_', 'ae11_', 'ae12_', 'ae13_', 'ae14_', 'ae15_', 'ae16_', 'ae17_', 'ae18_', 'ae19_', 'ae20_');
    $a_suffixes = '';
    $a_fields = array('id', 'staff', 'date', 'study_week', 'product', 'product_type', 'begin', 'end', 'description', 'medication', 
                      'action', 'other', 'unanticipated', 'risk', 'comments', 'physician_signature', 'signature_date', 'contacts');
}
if ($pid == 4298 and $set == 'aes') { # COMET, Project 4C Visit Data
    $pid_ok = 1;
    $ptitle = 'COMET, Project 4C Visit Data';
    $ftitle = 'Adverse Events';
    #$fname = 'adverse_events';
    $a_prefixes = array('ae_1', 'ae_2', 'ae_3', 'ae_4', 'ae_5', 'ae_6', 'ae_7', 'ae_8', 'ae_9', 'ae_10', 'ae_11', 'ae_12', 'ae_13', 'ae_14', 'ae_15', 'ae_16', 'ae_17', 'ae_18');
    $a_suffixes = '';
    $a_fields = array('exists', 'event', 'code', 'start_date', 'end_date', 'trt', 'ongoing', 'intensity', 'outcome', 'relationship', 
                      'relation', 'week', 'init');
}
if ($pid == 4298 and $set == 'pds') { # COMET, Project 4C Visit Data
    $pid_ok = 1;
    $ptitle = 'COMET, Project 4C Visit Data';
    $ftitle = 'Protocol Deviation Log';
    #$fname = 'protocol_deviation_log';
    $a_prefixes = '';
    $a_suffixes = array('_1', '_2', '_3', '_4', '_5', '_6', '_7', '_8', '_9', '_10');
    $a_fields = array('pd_exists', 'pd_date', 'pd_visit', 'pd_code', 'pd_description');
}

if ($pid_ok == 0) {
    exit("Project # " . $_GET['pid'] . " has not been set up for the set " . $_GET['set'] . " for this plugin");
}

$flds = '';
foreach($a_prefixes as $prefix) {
    $a_flds[$prefix] = '';
    foreach ($a_fields as $fld) {
        $flds .= ", '$prefix$fld'";
	// Handle exceptions where the field was not named correctly
        if ($pid == 1164 and $prefix == 'ae_3' and $fld == 'oth_action_exp' ) {
            $a_flds[$prefix] .= ", 'ae_3oth_action_exp2_987'";
        } elseif ($pid == 1164 and $prefix == 'ae_8' and $fld == 'oth_action_exp' ) {
            $a_flds[$prefix] .= ", 'ae_8oth_action_exp2_987'";
        } else {
            $a_flds[$prefix] .= ", '$prefix$fld'";
        }
    }
    $a_flds[$prefix] = substr($a_flds[$prefix], 2);
}
foreach($a_suffixes as $suffix) {
    $a_flds[$suffix] = '';
    foreach ($a_fields as $fld) {
        $flds .= ", '$fld$suffix'";
        $a_flds[$suffix] .= ", '$fld$suffix'";
    }
    $a_flds[$suffix] = substr($a_flds[$suffix], 2);
}
$flds = substr($flds, 2);
#print "<br/><br/>a_flds: ";
#print_r($a_flds);
#print "<br/><br/>";

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
if ($longitudinal) { $a_out_flds[] = 'event'; }
if ($pid == 1164 and $set == 'aes') {$a_out_flds[] = "lv_randomization_number"; }
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
        $sql = sprintf( "
            select distinct d.event_id, em.day_offset, em.descrip
              from redcap_data d, redcap_events_metadata em 
             where d.project_id = %d 
               and d.field_name in (%s)
               and d.record = '%s'
               and em.event_id = d.event_id
              order by em.day_offset, em.descrip",
                     $pid, $flds, $this_rec);
        #print "<br/>sql: $sql<br/><br/><br/>";

        // execute the sql statement
        $events_result = $conn->query( $sql );
        if ( ! $events_result )  // sql failed
        {
              die( "Could not execute SQL: <pre>$sql</pre> <br />" .  mysqli_error($conn) );
        }
        while ($erec = $events_result->fetch_assoc( ))
        {
            $evnts[] = $erec['event_id'];
            $evnt_names[$erec['event_id']] = $erec['descrip'];
        }
        #print "<br/><br/>evnts: ";
        #print_r($evnts);
        #print "<br/><br/>evnt_names: ";
        #print_r($evnt_names);
        #print "<br/><br/>";

        foreach($evnts as $this_evnt) {
            if ($this_rec == '') { continue; }

            if ($pid == 1164 and $set == 'aes') {$extra_flds = ", 'lv_randomization_number'"; } else { $extra_flds = ""; }
            if ($pid == 1164 and $set == 'aes') {$evt_ck = "AND (d.event_id = $this_evnt or m.field_name = 'lv_randomization_number')"; } else { $evt_ck = "AND d.event_id = $this_evnt"; }

            foreach($a_flds as $flds_key => $flds_val) {
                $sql = sprintf( "
                    SELECT distinct '%s' as record, m.field_order, m.field_name, d.value, %d as event_id, '%s' as event_name
                      FROM redcap_metadata m
                      LEFT JOIN redcap_data d
                        ON d.project_id = m.project_id
                       AND d.field_name = m.field_name
                       AND d.record = '%s'
                       %s
                     WHERE m.project_id = %d
                       AND m.field_name in (%s %s)
                     ORDER BY m.field_order",
                             $this_rec, $this_evnt, $evnt_names[$this_evnt], $this_rec, $evt_ck, $pid, $flds_val, $extra_flds);
                #print "<br/>sql: $sql<br/><br/><br/>";

                // execute the sql statement
                $records_result = $conn->query( $sql );
                if ( ! $records_result )  // sql failed
                {
                      die( "Could not execute SQL: <pre>$sql</pre> <br />" .  mysqli_error($conn) );
                }
                $a_vals[] = $this_rec;
                $a_vals[] = trim($flds_key,"_");
                $output = '';
                $first_fld = 1;
                $any_found = 'N';
                $prev_fld = '****';
                while ($rrec = $records_result->fetch_assoc( ))
                {
                    #print "<br/><br/>rrec: ";
                    #print_r($rrec);
                    #print "<br/>";
                    if ($longitudinal and $first_fld) { $a_vals[] = $rrec['event_name']; }
                    if ($rrec['field_name'] == $prev_fld) {
                        $a_vals[count($a_vals)-1] .= ";".$rrec['value'];
                        $output .= ";".$rrec['value'];
                    } else {
                        $a_vals[] = $rrec['value'];
                        $output .= "<br/>".$rrec['field_name'].": ".$rrec['value'];
                    }
                    if ($pid == 1164 and $set == 'aes') {
                        if ($rrec['value'] > '' and $rrec['field_name'] <> 'lv_randomization_number') { $any_found = 'Y'; }
                    } else { 
                        if ($rrec['value'] > '') { $any_found = 'Y'; }
                    }
                    $first_fld = 0;
                    $prev_fld = $rrec['field_name'];
                }
                if ($any_found == 'Y') {
                    #print "<br/><br/>output: $output<br/>";
                    #print "<br/><br/>a_vals: ";
                    #print_r($a_vals);
                    #print "<br/><br/>";
                    // Write to file
                    fputcsv($fp, $a_vals);
                }
                unset($a_vals);
            }
        }
        unset($evnts);
        unset($evnt_names);
    }
    // Close file for writing
    fclose($fp);

    // Close file for writing
    fclose($fp);
    db_free_result($result);
#exit;

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
