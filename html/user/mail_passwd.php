<?php

require_once("../inc/db.inc");
require_once("../inc/util.inc");
require_once("../inc/email.inc");

db_init();

$email_addr = process_user_text(strtolower($_POST["email_addr"]));
if (strlen($email_addr)) {
    $esc_email_addr = escape_pattern("@".$email_addr."_");
    $query = "select * from user where email_addr = '$email_addr' "
        . "or email_addr like '$esc_email_addr%'";
    $result = mysql_query($query);
    $user = mysql_fetch_object($result);
    mysql_free_result($result);
}

if (!$user) {
    page_head("No such user");
    echo "There is no user with email address $email_addr. <br>
        Try reentering your email address.<p>
    ";
} else {
    page_head("Mailed account key");
    if (split_munged_email_addr($user->email_addr, $auth, $orig_email)) {
        $is_auth = false;
        $user->email_addr = $orig_email;
    } else {
        $is_auth = true;
    }
    $retval = send_auth_email($user, false, $is_auth);
    if ($retval) {
        email_sent_message($email_addr);
    } else {
        echo "Can't send email to $user->email_addr: $retval";
    }
}

page_tail();

?>
