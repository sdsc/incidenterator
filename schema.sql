CREATE TABLE followups(
followupid INTEGER PRIMARY KEY,
incidentid INTEGER,
type TEXT,
body TEXT,
analysis TEXT,
site TEXT,
reportinguser TEXT,
reportingip TEXT,
ctime INTEGER);
CREATE TABLE incidents(
incidentid INTEGER PRIMARY KEY,
name TEXT,
description TEXT,
site TEXT,
initialmsg TEXT,
initialts INTEGER,
reportinguser TEXT,
reportingip TEXT,
ctime INTEGER,
mtime INTEGER);
CREATE TRIGGER followups_ts_insert AFTER INSERT on followups
BEGIN
update followups set ctime = strftime('%s','now') where followupid = new.followupid;
END;
CREATE TRIGGER incidents_insert_ts AFTER INSERT on incidents
BEGIN
update incidents set ctime = strftime('%s','now'), mtime = strftime('%s','now') where incidentid =NEW.incidentid;
END;
CREATE TRIGGER incidents_update_ts AFTER INSERT on incidents
BEGIN
update incidents set mtime = strftime('%s','now') where incidentid = NEW.incidentid;
END;
