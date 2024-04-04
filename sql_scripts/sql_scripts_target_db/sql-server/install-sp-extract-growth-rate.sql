CREATE PROCEDURE [dbo].[sp_dbims_extract_growth_rate] 
 @type_cmd varchar(10) 
AS 
IF @type_cmd = 'backup' 
BEGIN 
IF OBJECT_ID('tempdb..##check_back_up_size') IS NOT NULL 
BEGIN 
    DROP TABLE ##check_back_up_size 
END; 

CREATE TABLE ##check_back_up_size 
(
 database_name varchar(100),
 Year int, 
 Month int,
 BackupSizeGB decimal(10,2), 
 deltaNormal decimal(10,2),
 CompressedBackupSizeGB decimal(10,2),
 deltaCompressed decimal(10,2) 
); 

DECLARE database_backup_check CURSOR  FOR select name from sys.databases; 
DECLARE @db_name varchar(100); 
Declare @insert_table table (rn int, Year int, Month int, BackupSizeGB decimal(10,2), CompressedBackupSizeGB decimal(10,2));

OPEN database_backup_check; 

FETCH NEXT FROM database_backup_check INTO @db_name; 

WHILE @@FETCH_STATUS = 0  
    BEGIN 
	insert into @insert_table 
SELECT TOP 1000
      rn = ROW_NUMBER() OVER (ORDER BY DATEPART(year,[backup_start_date]) ASC, DATEPART(month,[backup_start_date]) ASC)
    , [Year]  = DATEPART(year,[backup_start_date])
    , [Month] = DATEPART(month,[backup_start_date])
    , [Backup Size GB] = CONVERT(DECIMAL(10,2),ROUND(AVG([backup_size]/1024/1024/1024),4))
    , [Compressed Backup Size GB] = CONVERT(DECIMAL(10,2),ROUND(AVG([compressed_backup_size]/1024/1024/1024),4)) 
FROM 
    msdb.dbo.backupset 
WHERE 
    [database_name] = @db_name 
AND [type] = 'D' 
AND backup_start_date BETWEEN DATEADD(mm, - 13, GETDATE()) AND GETDATE() 
GROUP BY  
    [database_name] 
    , DATEPART(yyyy,[backup_start_date]) 
    , DATEPART(mm, [backup_start_date]) 
ORDER BY [Year],[Month]; 

insert into  ##check_back_up_size (database_name, [Year], [Month], BackupSizeGB,  deltaNormal, CompressedBackupSizeGB, deltaCompressed) 
SELECT 
	@db_name, 
   b.Year, 
   b.Month, 
   b.BackupSizeGB, 
   0 AS deltaNormal, 
   b.CompressedBackupSizeGB, 
   0 AS deltaCompressed 
FROM @insert_table b 
WHERE b.rn = 1 
UNION 
SELECT 
	@db_name, 
   b.Year, 
   b.Month, 
   b.BackupSizeGB, 
   b.BackupSizeGB - d.BackupSizeGB AS deltaNormal, 
   b.CompressedBackupSizeGB, 
   b.CompressedBackupSizeGB - d.CompressedBackupSizeGB AS deltaCompressed 
FROM @insert_table b 
CROSS APPLY ( 
   SELECT bs.BackupSizeGB,bs.CompressedBackupSizeGB
   FROM @insert_table bs
   WHERE bs.rn = b.rn - 1
) AS d; 

DELETE FROM @insert_table; 

FETCH NEXT FROM database_backup_check INTO @db_name; 
END; 
	

CLOSE database_backup_check; 

DEALLOCATE database_backup_check; 

END; 

IF @type_cmd = 'data'
BEGIN
IF OBJECT_ID('tempdb..##check_data_size') IS NOT NULL 
BEGIN 
    DROP TABLE ##check_data_size 
END; 

IF OBJECT_ID('tempdb..#trace_tbl') IS NOT NULL 
BEGIN 
    DROP TABLE #trace_tbl
END;  

DECLARE @current_tracefilename VARCHAR(500); 
DECLARE @0_tracefilename VARCHAR(500); 
DECLARE @indx INT; 
SELECT @current_tracefilename = path 
FROM sys.traces 
WHERE is_default = 1; 


SET @current_tracefilename = REVERSE(@current_tracefilename); 
SELECT @indx = PATINDEX('%\%', @current_tracefilename); 
SET @current_tracefilename = REVERSE(@current_tracefilename); 
SET @0_tracefilename = LEFT(@current_tracefilename, LEN(@current_tracefilename) - @indx) + '\log.trc'; 

select * into #trace_tbl from ::fn_trace_gettable(@0_tracefilename, DEFAULT)

SELECT DatabaseName, 
       te.name, 
       Filename, 
       CONVERT(DECIMAL(10, 3), Duration / 1000000e0) AS TimeTakenSeconds, 
       StartTime, 
       EndTime, 
       (IntegerData * 8.0 / 1024) AS 'ChangeInSize MB', 
       ApplicationName, 
       HostName, 
       LoginName
into  ##check_data_size	   
FROM #trace_tbl t INNER JOIN sys.trace_events AS te ON t.EventClass = te.trace_event_id
WHERE(trace_event_id >= 92 AND trace_event_id <= 95) 
ORDER BY t.StartTime; 
END; 