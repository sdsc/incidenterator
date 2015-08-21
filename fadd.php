<?php

/***
 * fadd.php - followup add
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

// no detail? someone is messing with us.
if(empty($i)) die("Bad incident id");

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
      $n['reportinguser'] = $_SERVER['PHP_AUTH_USER'];
      $n['reportingip'] = getenv('REMOTE_ADDR');
      $irtdb->addFollowup($_REQUEST['i'],$n);
      header("REFRESH: 0;idetail.php?i=".$_REQUEST['i']."#latest");
    }  


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
  width: 50em;
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

<h3>Add Follow-up To "<? echo htmlentities($i['name'])?>"</h3>
<form method="post">
  
  <fieldset>
    <legend>Add Follow-up</legend>

<?php
    if($errors != ''){
      printf("<div class=\"errors\">%s</div>\n",$errors);
    }
?>



    <div class="inputblock">
      <label for="site">Applies to site (optional)</label>
      <br>
      <input type="text" size="10" name="site" id="site" value="<? dv('site')?>">
    </div>

    <div class="inputblock">
      <label for="type">Type of follow-up</label>
      <br>
      <select name="type" id="type">
        <option value="finding" <?sv('type','finding')?>>Finding</option>
        <option value="status" <?sv('type','status')?>>Status Update</option>
        <option value="hotlist" <?sv('type','hotlist')?>>Hotlist Addition</option>
      </select>
    </div>
 
    <div class="inputblock">
      <label for="body">Body</label>
      <br>
      <textarea cols="80" rows="10" name="body" id="body"><?dv('body')?></textarea>
    </div>

    <div class="inputblock">
      <label for="analysis">Analysis / Remarks (optional)</label>
      <br>
      <textarea cols="80" rows="10" name="analysis" id="analysis"><?dv('analysis')?></textarea>
    </div>

    <div class="inputblock">
      <input type="submit" value="Add Follow-up" name="submit">
      <input type="hidden" value="<?echo htmlentities($_REQUEST['i'])?>" name="i">
    </div>

  </fieldset>
</form>

</body>

</html>

