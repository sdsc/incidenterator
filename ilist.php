<?php

/***
 * ilist.php - list incidents
 *
 * Created by Scott Sakai (ssakai@sdsc.edu)
 * 
 * March 2008
 ***/
require_once("irtauth.php");
require_once("irtdb.php");


// connect to the database
$irtdb = new irtdb();


// get a list of incidents
$ilist = $irtdb->listIncidents();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>IR Tracker Prototype - Incident List</title>
  <style type="text/css">
.odd{
  background-color: #f0f0f0;
  color: black;
}
.even{
  background-color: #ffffff;
  color: black;
}
table.ilist{
  width: 95%;
  border: 1px solid black;
  border-collapse: collapse;
  margin: auto;
}

table.ilist td{
  padding: 0.5em;
  text-align: left;
  vertical-align: top;
}

th{
  text-align: left;
  background-color: black;
  color: white;
  padding: 0.5em;
}

.idate{
  width: 10em;
}
.iid{
  width: 1em;
}
.isite{
  width: 5em;
}

h4{
  margin: 0em;
  font-variant: small-caps;
  font-size: 120%;
}
 
.idesc{
  font-size: 85%;
  margin-bottom: 1em;
} 

.istatus{
  font-style: italic;
}
  </style>
</head>

<body>
<a href="iadd.php">Add New Incident</a>

<h3>Incidents</h3>

<table class="ilist">
<tr>
  <th>ID</th>
  <th>Date Added</th>
  <th>Site</th>
  <th>Incident</th>
</tr>

<?php
    $zebra = 0;

    // here we gooo!
    if(!empty($ilist)) foreach($ilist as $i){

        if($zebra++ % 2) $zstr = "class=\"odd\"";
        else  $zstr = "class=\"even\"";
 
        echo("<tr $zstr>\n");
        printf("  <td class=\"iid\"><a href=\"idetail.php?i=%d\">%s</a></td>\n",$i['incidentid'],$i['incidentid']);
        printf("  <td class=\"idate\">%s</td>\n",strftime("%a %b %d %Y<br>%R %Z",$i['ctime']));
        printf("  <td class=\"isite\">%s</td>\n",$i['site']);
        printf("  <td class=\"iblock\"><h4>%s</h4><div class=\"idesc\"> %s</div><div class=\"istatus\">Status: %s</div></td>\n",$i['name'],$i['description'],
        $i['status']);
        echo("</tr>\n");
    }
?>

</table>
<br>
<a href="iadd.php">Add New Incident</a>

</body>

</html>

