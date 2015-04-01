<?php
/* File: newsie.php
 * Info: Newsletter Generator. Fetch a list of subscribers in the database and this week's newsletter stored in the database.
 * Author: Jason Charney (jrcharneyATgmailDOTcom)
 * Date: 31 March 2015
 * Version: 0.1a
 * Status: ALPHA! VERY ALPHA!
 * License: MIT License.  
 */

/* TODO: Can we automate the date to run on a specific date, like a cron job?  Probably, but that's your decision.
 * TODO: What if you don't have a newsletter for this week?  If there is none, it shouldn't run.  At least I hope it won't.
 * TODO: What if there is more than one article posted this week?  This version was designed for one article.  But I could do that for the next version in the near future.
 */
$date = "date";		// run this program for the news letter to be posted on this date.
/* Connect to the database to fetch the mailing list and the message to send.
 * Would rather use MySQLi than PDO (Persistant Data Objects).
 * PDO sounds "high tech" but it's really a pain in the butt.
 * MySQLi is KISSable (KISS = Keep It Simple Stupid).
 */
$servername = "localhost";		// the URL or IP address of the MySQL server.
$username   = "username";   	// DO NOT USE "admin" or "root"!
$password   = "password";   	// DO NOT USE "password" as the password. In fact, there's a dozen things you should not use!
$database   = "mailinglist";

// TODO: Think of some CRUD (Create-Read-Update-Destroy) plan.
$conn = mysqli_connect($servername,$username,$password);
if(!$conn){
 die("Connection failed: " . mysqli_connect_error());		// I need to think of something better
}
// successful connections would go on to the next part of this content.

/* PHP's mail function! Makes sending mail super easy!
 * $subscriber = Who to send the message too. Pull it from a database! SO EASY!
 * $subject    = subject of the email.  What if we saved our emails to database?  email.subject
 * $message    = message of the email.  What if we saved our emails to database?  email.message
 * $headers    = Extra fields. Who shall we call ourselves? Who can we reply to?
 */
/* $subject = ${email.subject}; $message = ${email.message}; */

/* Func: make_message
 * Info: Load the information into this email template!
 * TODO: make this a class later.  Maybe make it a loadable module if you want to swap it out to use a different template?
 */ 
function make_message($name,$email,$message){
$lw = "\r\n"	// What shall we end our lines with?
//$ww = 80		// Word wrap limit, if we choose to use one.
// $message = wordwrap($message, $ww, $lw);

$newsletter = <<<EOM
<html>
 <head>
  <title>$subject</title>
  <style type="text/css">
body{}
#layout{}
#header{
 text-align: center;
}
#nav{}

#side{}
#main{}
.clear{clear:both;}
#footer{
 text-align: center
}
  </style>
 </head>
 <body>
  <div id="layout">
   <div id="header">
    <!-- header -->
	<!-- company logo -->
	<!-- stock photo! -->
   </div>
   <div id="nav">
    <!-- nav bar. We can comment this out if you want. -->
	<!-- could put the links you normally see in the website navbar here -->
   </div>
   <div id="side">
    <!-- sidebar content, sidebar is on the left in this case. -->
	<!-- could put sidebar widgets and link list here like you see on the website. But no external widgets like twitter or facebook. -->
   </div>
   <div id="main">
    <!-- TODO: Date? -->
    <p>${name}</p><!-- TODO: Full name or First name? -->
    <!-- main content -->
	<!-- The message we stored on the database will be posted here -->
    ${message}
   </div>
   <!-- We could put the side bar over here to put the sidebar on the right instead of the left. Your decision. -->
   <div class="clear"></div><!-- This is manditory if you use the two column format, otherwise the footer looks weird. -->
   <div id="footer">
    <!-- footer content -->
	<!-- copy right data, fine print, "click here to opt out" stuff, links to social media -->
   </div>
  </div>
 </body>
</html>
EOM;
return $newsletter;
}

$headers = 'From: nobody@scrubsandbeyond.com'     . $lw
         . 'Reply-To: nobody@scrubsandbeyond.com' . $lw
		 . 'X-Mailer: PHP/' . phpversion();

/* Get the mailing list */
$sql_ppl = "SELECT id, first_name, last_name, email_address FROM customers WHERE receive_newsletter = 'yes'";
$result_ppl = $conn->query($sql_ppl);

/* Get this week's newsletter */
$sql_news = "SELECT id, subject, body FROM newsletter WHERE post_date = '$date'";
$result_news = $conn->query($sql_news);

/* TODO: Should result_news->num_rows == 1 exactly? */
if($result_ppl->num_rows > 0  && $result_news->num_rows > 1){
 $news = $result_news->fetch_assoc();
 while($row = $result_ppl->fetch_assoc()){
  message = make_message($row["first_name"] . " " . $row["last_name"],$row["email_address"],$news["message"]);
  mail($row["email_address"],$news["subject"],$message,$headers); 			// PHP HAS BUILT IN MAIL! ^_^ yay!
 }
} else {
 echo "0 results";
}

$conn->close();
?>