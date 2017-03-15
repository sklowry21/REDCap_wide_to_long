# REDCap_wide_to_long
This plugin outputs the data from repeated sections in a given project to a csv file, transforming the data from wide (i.e. different fields for each occurrence) to long (i.e. one record for each occurrence)

It needs to be edited to set it up for each project, providing the field information for that specific project.  For example:
if ($pid == 1164 and $set == 'aes') { # Put name of project here
    $pid_ok = 1;
    $ptitle = 'Put name of project here';
    $ftitle = 'Adverse Events';
    $fname = 'adverse_events';
    $a_prefixes = array('ae_1', 'ae_2', 'ae_3', 'ae_4', 'ae_5');
    $a_suffixes = '';
    $a_fields = array('event', 'desc', 'start_date', 'start_time', 'end_date', 'end_time', 'grade',
                      'relatedness', 'act_tak_stu_int', 'action_exp', 'oth_act_tak', 'oth_action_exp', 'outcome', 'sae', 'sae_exp');
}
In this example, there are 15 fields for each adverse event, and there are fields for up to 5 adverse events.  
The field names are ae_1event, ae_1desc, ae1_start_date, ... ae_1sae_exp, ae_2event, ae2_desc, ....
