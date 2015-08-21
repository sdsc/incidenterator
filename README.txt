Incidenterator - A simplistic incident response tool
Version 0.9

This tool was designed to help track progress during incident response.  Unlike other methods, Incidenterator tries to keep the operational overhead low.  Paste in some findings, leave a remark or analysis, submit. Rinse, wash, repeat.

Incidenterator relies on simple technologies.  Sqlite for the database, web server access controls for authentication and authorization.  

Note that there are no user levels.  A user may view all incidents and submit updates to all incidents.  This is intentional.  If you cannot trust your user, they should not be looking at the content stored in this application.



Quick Start
-----------

1. Configure your web server.
  1a. Incidenterator requires PDO and the Sqlite PDO driver.

  1b. Incidenterator relies on your web server to authenticate and authorize 
      users.  Set this up however you want.  
      .htaccess Basic-Auth is a good start.
      Make sure something meaningful ends up in $_SERVER['PHP_AUTH_USER'].

  1c. Don't forget to add users.  Incidenterator doesn't handle this.

  1d. Configure your web server to use https (SSL).  Pick some good ciphers, 
      disable SSLv3 and SSLv2, disable the export ciphers.  Incidenterator 
      doesn't care if you use plain http, however you probably want to keep
      incident information somewhat confidential.


2. Configure your database.
  2a. Use the supplied irt-new.sqlite sqlite database, or create one from
      scratch using the schema.sql file.  Put this file somewhere outside of
      the document root (so nobody can fetch it directly).  Make sure the
      web server's account can write to it.  Example: mode 0600, owner apache.

  2b. Edit irtdb.php, set IRT_DB_DEFAULT_FILE to the fully-qualified path to
      the sqlite database described in 2a.


3. Hit it.  Point your browser at the URL you dumped the Incidenterator code in.
   The web server should request authentication according to your configuration.
   Add an incident and go.


