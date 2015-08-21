<?php

/***
 * iadd.php - add incident
 *
 * Created by Scott Sakai (ssakai@sdsc.edu)
 * 
 * March 2008
 ***/
require_once("irtauth.php");
require_once("irtdb.php");


// connect to the database
$irtdb = new irtdb();


// check form data
$i = Array(); // incident
$h = Array(); // hotlist
$s = Array(); // status
$errors = '';
if(isset($_POST['submit'])){

    // name (manditory)
    if(!isset($_POST['name']) || trim($_POST['name']) == ''){
        $errors .= "\"Name\" is a manditory field<br>";
    }else $i['name'] = trim($_POST['name']);

    // description (manditory)
    if(!isset($_POST['description']) || trim($_POST['description']) == ''){
        $errors .= "\"Description\" is a manditory field<br>";
    }else $i['description'] = trim($_POST['description']);

    // site (also manditory)
    if(!isset($_POST['site']) || trim($_POST['site']) == ''){
        $errors .= "\"Site\" is a manditory field<br>";
    }else $i['site'] = trim($_POST['site']);
   
    // initialts (manditory, formatting restrictions)
    if(!isset($_POST['initialts']) || trim($_POST['initialts']) == ''){
        $errors .= "\"Date and time of initial discovery\" is a manditory field<br>";
    }else{
        $ts = strtotime(trim($_POST['initialts']));
        if($ts == FALSE || $ts == -1){
            $errors .= "\"Date and time of initial discovery\" is in an unknown date and time format<br>";
        }else{
            $i['initialts'] = $ts;
        }
    }

    // initial msg (guessed it: manditory)
    if(!isset($_POST['initialmsg']) || trim($_POST['initialmsg']) == ''){
        $errors .= "\"Initial notification message\" is a manditory field<br>";
    }else{
        $i['initialmsg'] = trim($_POST['initialmsg']);
    }

    // status (duh. manditory)
    if(!isset($_POST['status']) || trim($_POST['status']) == ''){
        $errors .= "\"Status\" is a manditory field<br>";
    }else{
        $s['body'] = trim($_POST['status']);
        $s['type'] = "status";
    }

    // hotlist (optional)
    if(isset($_POST['hotlist']) && trim($_POST['hotlist']) != ''){
        // this is special -- need to create a followup.
        $h['body'] = trim($_POST['hotlist']);
        $h['type'] = "hotlist";
    }

    $incidentid = 0;

    // no errors?
    if($errors == ''){
      // fill in a couple more things
      $i['reportinguser'] = $_SERVER['PHP_AUTH_USER'];
      $i['reportingip'] = getenv('REMOTE_ADDR');
      $incidentid = $irtdb->addIncident($i);
      if($incidentid > 0){
          if(!empty($h)){
              $h['reportinguser'] = $_SERVER['PHP_AUTH_USER'];
              $h['reportingip'] = getenv('REMOTE_ADDR');
              $irtdb->addFollowup($incidentid,$h);
          }

          if(!empty($s)){
              $s['reportinguser'] = $_SERVER['PHP_AUTH_USER'];
              $s['reportingip'] = getenv('REMOTE_ADDR');
              $irtdb->addFollowup($incidentid,$s);
          }

      }
      header("REFRESH: 0;idetail.php?i=$incidentid");
    }  
}



// here's our fake incident
/*$i['name'] = 'test incident 2';
$i['description'] = 'another test incident';
$i['initialmsg'] = 'youve got warez!';
$i['initialts'] = time() - 36000;
$i['reportinguser'] = 'whee';
$i['reportingip'] = getenv('REMOTE_ADDR');
$irtdb->addIncident($i);
*/
// list incidents
//print_r($irtdb->listIncidents());


// we'll use this to display 'default' values
function dv($field){
    if(!isset($_REQUEST[$field])) return;
    echo htmlentities($_REQUEST[$field]);
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>IR Tracker Prototype - Add an Incident</title>
  <style type="text/css">
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
<h3>Add An Incident</h3>
<form method="post">

<?php
    if($errors != ''){
      printf("<div class=\"errors\">%s</div>\n",$errors);
    }
?>

<fieldset>
  <legend>Incident - Naming</legend>

    <div class="inputblock">
      <label for="name">Incident Name (short)</label>
      <br>
      <input type="text" size="20" name="name" id="name" value="<?dv('name')?>">
    </div>

    <div class="inputblock">
      <label for="desc">Description (long)</label>
      <br>
      <input type="text" size="80" name="description" id="desc" value="<?dv('description')?>">
    </div>

</fieldset>

<fieldset>
  <legend>Incident - Discovery</legend>

    <div class="inputblock">
      <label for="site">Discovery site</label>
      <br>
      <input type="text" size="10" name="site" id="site" value="<?dv('site')?>">
    </div>
    
    <div class="inputblock">
      <label for="its">Date and time of initial discovery (ex: "Mar 18 2008 22:43 PDT")</label>
      <br>
      <input type="text" size="40" name="initialts" id="its" value="<?dv('initialts')?>">
    </div>
    
    <div class="inputblock">
      <label for="imsg">Initial notificaton message (what got your attention?)</label>
      <br>
      <textarea cols="80" rows="10" name="initialmsg" id="imsg" wrap="virtual"><?dv('initialmsg')?></textarea>
    </div> 

</fieldset>

<fieldset>
  <legend>Incident - Initial Details</legend>

  <div class="inputblock">
    <label for="status">Status (ex: Still looking at logs)</label>
    <br>
    <input type="text" size="80" name="status" id="status" value="<?dv('status')?>">
  </div>


  <div class="inputblock">
    <label for="hotlist">Hotlist (stuff to watch for + short desc) - one item per line, optional</label>
    <br>
    <textarea cols="80" rows="10" name="hotlist" id="hotlist" wrap="off"><?dv('hotlist')?></textarea>
  </div>

 </fieldset>

  <div class="inputblock">
  <input type="submit" value="Add Incident" name="submit">
  </div>


</form>
</body>
</html>
