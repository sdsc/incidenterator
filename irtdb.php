<?php
/***
 * IR tracking thing
 * 
 * irtdb.php - database access routines
 *
 * Created by Scott Sakai (ssakai@sdsc.edu)
 * 
 * March 2008
 ***/

// Change this to the location of your sqlite database, created with
// schema.sql, or a copy of irt-new.sqlite
//
//-----> PLACE THIS FILE OUTSIDE OF YOUR DOCUMENT ROOT!!! <--------
//
define('IRT_DB_DEFAULT_FILE',"/var/www/incidenterator-private/database/irt-demo.sqlite");


class irtdb {

    private $dbh;

    /* "connect" to an sqlite database (open its file) 
        This has to succeed, or it'll die() right here.  
    */
    function __construct($dbfile = IRT_DB_DEFAULT_FILE){
    
        try{
            $this->dbh = new PDO("sqlite:$dbfile",'','');
        } catch(PDOException $e){
            die("Unable to set up database: " . $e->getMessage());
        }

    }



    
    /* return a 2-d array of incidents 
       
       $array[0..n][colname]

       colname is one of:
         incidentid		unique incident ID
         name			name of the incident
         description		brief description
         site			site initially reporting it
         initialmsg		the message that brought the incident to light
         initialts		the date/time of the above message
         reportinguser		username reporting the incident
         reportingip		ip of client reportin the incident
         ctime 			creation time of incident record (human-readable, UTC)
         mtime			modification time of incident record (not used yet)
         status			status/summary
    */
    function listIncidents(){
        
        // the query to retrieve the above columns
        $q = "SELECT incidentid,name,description,site,initialmsg,initialts,reportinguser,reportingip,".
             "ctime,mtime from incidents ORDER BY ctime";
        
        // prepare/execute it
        $res = $this->dbh->query($q);
        $out = Array();
        
        // build the 2d array, get the status too
        if($res) foreach($res as $row){
            $row['status'] = $this->getStatus($row['incidentid']);
            $out[] = $row;
        }
        
        return $out;
    }
 


   
    /* add an incident!
       
       incidentinfo is an assoc array with the following keys:
         name
         description
         site
         initialmsg
         initialts
         reportinguser
         reportingip

       this method must succeed, or it will die()
    */    
    function addIncident($incidentinfo){
    
        // query for prepared statement
        $q = "INSERT INTO incidents (name,description,site,initialmsg,initialts,reportinguser,reportingip)".
             " values(:name, :desc, :site, :im, :it, :ru, :ri)";
        
        // prepare it
        try{
            $sth = $this->dbh->prepare($q);
        }catch(Exception $e){
            die("addIncident() - failed to prepare query: " . $e->getMessage());
        }

        // bind variables
        $sth->bindParam(':name',$incidentinfo['name']);
        $sth->bindParam(':desc',$incidentinfo['description']);
        $sth->bindParam(':site',$incidentinfo['site']);
        $sth->bindParam(':im',$incidentinfo['initialmsg']);
        $sth->bindParam(':it',$incidentinfo['initialts']);
        $sth->bindParam(':ru',$incidentinfo['reportinguser']);
        $sth->bindParam(':ri',$incidentinfo['reportingip']);

        // go.
        try{
            $this->dbh->beginTransaction();
            $rc = $sth->execute();
            if(!$rc){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            $this->dbh->rollBack();
            die("addIncident() - failed to add incident: " . $e->getMessage());
        }
        $this->dbh->commit();
        
        // get the last insert id
        $res = $this->dbh->query("SELECT last_insert_rowid()");
      
        foreach($res as $row){
          return($row[0]);
        }   

    }




 /* return a 1-d array of an incident

       args:
       $incidentid is an incident id (number)

       returns $array[colname]
       colname is one of:
         incidentid		unique incident ID
         name			name of the incident
         description		brief description
         site			site initially reporting it
         initialmsg		the message that brought the incident to light
         initialts		the date/time of the above message
         reportinguser		username reporting the incident
         reportingip		ip of client reportin the incident
         ctime 			creation time of incident record (human-readable, UTC)
         mtime			modification time of incident record (not used yet)
         status			status/summary
    */
    function getIncident($incidentid){
        
        // the query to retrieve the above columns
        $q = "SELECT incidentid,name,description,site,initialmsg,initialts,reportinguser,reportingip,".
             "ctime,mtime from incidents where incidentid = :incidentid";
 
        // prepare it
        try{
            $sth = $this->dbh->prepare($q);
        }catch(Exception $e){
            die("getIncident() - failed to prepare query: " . $e->getMessage());
        }

        // bind variables
        $sth->bindParam(':incidentid',$incidentid);
 
        // query
        try{
            $rc = $sth->execute();
            if(!$rc){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            die("getIncident() - failed to get incident: " . $e->getMessage());
        }

        $res = $sth->fetch();

        if(!empty($res)) return $res;
        return Array();

    }




    /* add followup to an incident
       
       incidentid is an incidentid (number) to attach the followup to

       followup is an assoc array with the following keys:
         type
         body
         analysis
         site
         reportinguser
         reportingip

       this method must succeed, or it will die()
    */    
    function addFollowup($incidentid, $followup){
        
        // we should never get a bad incident ID.. lets check anyway
        $iinfo = $this->getIncident($incidentid);
        if(empty($iinfo)){
            die("addFollowup() - Bad incident id: " . htmlentities($incidentid));
        }

        // query for prepared statement
        $q = "INSERT INTO followups (incidentid,type,body,analysis,site,reportinguser,reportingip)".
             " values(:iid, :type, :body, :anal, :site, :ru, :ri)";
        
        // prepare it
        try{
            $sth = $this->dbh->prepare($q);
            if(!$sth){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            die("addFollowup() - failed to prepare query: " . $e->getMessage());
        }

        // bind variables
        $sth->bindParam(':iid',$incidentid);
        $sth->bindParam(':type',$followup['type']);
        $sth->bindParam(':body',$followup['body']);
        $sth->bindParam(':anal',$followup['analysis']);
        $sth->bindParam(':site',$followup['site']);
        $sth->bindParam(':ru',$followup['reportinguser']);
        $sth->bindParam(':ri',$followup['reportingip']);

        // go.
        try{
            $this->dbh->beginTransaction();
            $rc = $sth->execute();
            if(!$rc){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            $this->dbh->rollBack();
            die("addFollowup() - failed to add followup: " . $e->getMessage());
        }
        $this->dbh->commit();

    }




    /* return a 2-d array of followup to an incident
  
       incidentid = the incident to get the followup for
       
       returns $array[0..n][colname]

       colname is one of:
	 type			type of followup, eg. findings, hotlist, status
         body			some kind of text, depends on type
         analysis		analysis of the body (optional)
         site			site reporting related to the followup
         reportinguser		username reporting the incident
         reportingip		ip of client reportin the incident
         ctime 			creation time of followup (epoch time, UTC)
    */
    function getFollowup($incidentid){
        
        // the query to retrieve the above columns
        $q = "SELECT type,body,analysis,site,reportinguser,reportingip,ctime".
             " from followups WHERE incidentid = :incidentid ORDER BY ctime";
 
        // prepare it
        try{
            $sth = $this->dbh->prepare($q);
            if(!$sth){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            die("getFollowup() - failed to prepare query: " . $e->getMessage());
        }

        // bind variables
        $sth->bindParam(':incidentid',$incidentid);
 
        // query
        try{
            $rc = $sth->execute();
            if(!$rc){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            die("getFollowup() - failed to get incident: " . $e->getMessage());
        }

        return $sth->fetchAll();

    }
 


    /* get (latest) status update 
       param: incidentid = the incident id to get the status for
       returns: string (the latest status update)
    */
    function getStatus($incidentid){
        
        // the query to retrieve the above columns
        $q = "SELECT body,ctime".
             " from followups WHERE incidentid = :incidentid AND type = 'status' ORDER BY ctime DESC LIMIT 1";
 
        // prepare it
        try{
            $sth = $this->dbh->prepare($q);
        }catch(Exception $e){
            die("getStatus() - failed to prepare query: " . $e->getMessage());
        }

        // bind variables
        $sth->bindParam(':incidentid',$incidentid);
 
        // query
        try{
            $rc = $sth->execute();
            if(!$rc){
                $ec = $this->dbh->errorInfo();
                throw new Exception($ec[2]);
            }
        }catch(Exception $e){
            die("getStatus() - failed to get status: " . $e->getMessage());
        }

        $row = $sth->fetch();

        if(empty($row)) return '';

        return sprintf("[%s] %s",strftime("%x %X %z",$row['ctime']),$row['body']);


    }
  
}

?>
