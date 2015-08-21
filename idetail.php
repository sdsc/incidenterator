<?php

/***
 * idetail.php - incident detail
 *
 * Created by Scott Sakai (ssakai@sdsc.edu)
 * 
 * March 2008
 ***/
require_once("irtauth.php");
require_once("irtdb.php");


// connect to the database
$irtdb = new irtdb(); 


// get incident detail
$i = $irtdb->getIncident($_REQUEST['i']);

// process form info, if any
$errors = '';
$n = Array();
if(isset($_POST['submit'])){

    // type (manditory)
    if(!isset($_POST['type']) || trim($_POST['type']) == ''){
        $errors .= "\"Type of follow-up\" is a manditory field<br>";
    }else $n['type'] = trim($_POST['type']);

    // body (manditory)
    if(!isset($_POST['body']) || trim($_POST['body']) == ''){
        $errors .= "\"Body\" is a manditory field<br>";
    }else $n['body'] = trim($_POST['body']);

    // analysis (optional)
    if(isset($_POST['analysis']) && trim($_POST['analysis']) != ''){
      $n['analysis'] = trim($_POST['analysis']);
    }

    // site (optional)
    if(isset($_POST['site']) && trim($_POST['site']) != ''){
      $n['site'] = trim($_POST['site']);
    }


    // no errors?
    if($errors == ''){
      // fill in a couple more things
      $n['reportinguser'] = 'whee';
      $n['reportingip'] = getenv('REMOTE_ADDR');
      //$irtdb->addFollowup($n['incidentid'],$n);
    }  


}

// get incident followup
$followups = $irtdb->getFollowup($_REQUEST['i']);

// get status
$status = htmlentities($irtdb->getStatus($_REQUEST['i']));

// loop through the followups and collect useful info
$hotlist = ''; // this will be the preformatted hotlist
foreach($followups as $f){
    if($f['type'] == 'hotlist'){ $hotlist .= $f['body'] . "\n";}
}

// we'll use this to display values from $i
// neatly, with proper escaped html
function gv($field){
    global $i;

    if(!isset($i[$field])) return;
    echo htmlentities($i[$field]);
}

// we'll use this to display values from $f
// neatly, with proper escaped html
function fv($field){
    global $f;

    if(!isset($f[$field])) return;
    echo htmlentities($f[$field]);
}

// we'll use this to display 'default' values
function dv($field){
    if(!isset($_REQUEST[$field])) return;
    echo htmlentities($_REQUEST[$field]);
}

// emits "selected" if $_REQUEST[$name] has $value
function sv($name,$value){
    if($_REQUEST[$name] == $value) echo ("selected");
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>IR Tracker Prototype - Incident Detail</title>
  <style type="text/css">
.odd{
  background-color: #f0f0f0;
  color: black;
}
.even{
  background-color: #ffffff;
  color: black;
}

.block{
  margin-top: 0em;
  margin-bottom: 2em;
}

h4{
  margin: 0em;
  font-variant: small-caps;
  font-size: 120%;
}
 
.desc{
  font-size: 85%;
  margin-bottom: 1em;
} 

.status{
  font-style: italic;
}

.fuheader, .fusite {
  font-size: 65%;
  margin: 1em;
  margin-top: 0em;
  margin-left: 0em;
}

.followupitem{
  margin-bottom: 2em;
  border: 1px solid #a0a0a0;
  padding: 0.5em;
}
pre{
  margin-top: 0em;
  border: 1px solid #bbbbbb;
  padding: 0.5em;
//  width: 50em;
}

h3{
  margin-bottom: 0em;
}

.idetail, .followup{
  margin-top: 0em;
  padding: 1em;
  border: 1px solid black;
  width: 95%;
}

fieldset{
  padding: 1em;
  margin: auto;
  width: 60em;
  margin-top: 2em;
  margin-bottom: 2em;
}

.inputblock{
  margin-bottom: 2em;
}

.errors{
  padding: 2em;
  border: 3px solid red;
  margin: 2em;
}


  </style>
</head>

<body>

<a href="fadd.php?i=<? echo htmlentities($_REQUEST['i']) ?>">Add Follow-Up</a>
<br>
<a href="#latest">Latest Update</a>
<br>
<h3>Incident Detail</h3>
<div class="idetail">

  <div class="block">
    <h4><? gv('name') ?></h4>
    <div class="desc"><? gv('description') ?></div>
    <div class="status">Status: <? echo $status ?></div>
  </div>

  <div class="block">
    <div class="date">Date of Discovery: <? echo strftime("%a %b %d %Y %R %Z",$i['initialts']) ?></div>
    <div class="site">Site: <? gv('site') ?></div>
  </div>

  <div class="block">
    Initial notification message
    <pre><? gv('initialmsg') ?></pre>
  </div>

  <div class="block">
    Hotlist
    <pre><?echo htmlentities($hotlist)?></pre>
  </div>

</div>

<h3>Follow-Ups</h3>
<div class="followup">
  
  <?php
    $zebra = 0;
    // go through each followup
    foreach($followups as $f){

      if($zebra++ % 2) $zstr = " odd";
      else $zstr = " even";

      echo("<div class=\"followupitem $zstr\">\n");

      printf("  <div class=\"fuheader\">Posted on %s by %s (%s)</div>\n",
      strftime("%a %b %d %Y %R %Z" ,$f['ctime']), htmlentities($f['reportinguser']),
      htmlentities($f['reportingip']));

      if($f['site'] != ''){
        printf("  <div class=\"fusite\">Related site: %s</div>\n",htmlentities($f['site']));
      }
     
      printf("  <div class=\"futype\">%s:</div>\n",htmlentities($f['type']));
      printf("  <div class=\"fubody\"><pre>%s</pre></div>\n",htmlentities($f['body']));
      
      if($f['analysis'] != ''){
        $analysishtml = htmlentities($f['analysis']);
        $analysishtml = preg_replace("/\n/","<br/>\n",$analysishtml);
        printf("  <div class=\"fuanalysis\">Analysis: %s</div>\n", $analysishtml);
      }
      
      echo("</div>\n");
    }
  ?>

</div>
<br>
<a name="latest"></a>
<a href="fadd.php?i=<? echo htmlentities($_REQUEST['i']) ?>">Add Follow-Up</a>
</body>

</html>

