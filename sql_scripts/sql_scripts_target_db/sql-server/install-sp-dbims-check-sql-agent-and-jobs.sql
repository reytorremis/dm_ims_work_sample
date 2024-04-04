CREATE PROCEDURE [dbo].[sp_dbims_check_sql_agent_and_jobs] 
@check_param varchar(10) 
AS 
IF @check_param = 'agent'
BEGIN
	IF OBJECT_ID('tempdb..##temp_agent_chk') IS NOT NULL 
	BEGIN 
	DROP TABLE ##temp_agent_chk 
	END; 
	
	DECLARE @agent NVARCHAR(512); 
	DECLARE @check_status table (agent_job_status varchar(100)); 

	SELECT @agent = COALESCE(N'SQLAgent$' + CONVERT(SYSNAME, SERVERPROPERTY('InstanceName')), 
	  N'SQLServerAgent'); 

	insert into @check_status(agent_job_status) 
	EXEC master.dbo.xp_servicecontrol 'QueryState', @agent; 

	select @@SERVERNAME AS SQL_Server_Instance, @@version as SQLServerVersion, UPPER(REPLACE(agent_job_status, '.', '')) as agent_status into ##temp_agent_chk from @check_status; 
END 
IF @check_param = 'history' 
BEGIN 
		SELECT name AS [Job Name] 
         ,CONVERT(VARCHAR,DATEADD(S,(run_time/10000)*60*60 /* hours */  
          +((run_time - (run_time/10000) * 10000)/100) * 60 /* mins */  
          + (run_time - (run_time/100) * 100)  /* secs */ 
           ,CONVERT(DATETIME,RTRIM(run_date),113)),20) AS [Time Run] 
         ,CASE WHEN enabled=1 THEN 'Enabled'  
               ELSE 'Disabled' 
          END [Job Status], 
         CASE WHEN SJH.run_status=0 THEN 'Failed' 
                     WHEN SJH.run_status=1 THEN 'Succeeded' 
                     WHEN SJH.run_status=2 THEN 'Retry' 
                     WHEN SJH.run_status=3 THEN 'Cancelled' 
               ELSE 'Unknown' 
          END [Job Outcome], 
		  message as run_message 
FROM   msdb.dbo.sysjobhistory SJH 
JOIN   msdb.dbo.sysjobs SJ 
ON     SJH.job_id = sj.job_id 
ORDER BY name,run_date,run_time; 

END 
IF @check_param = 'status' 
BEGIN 
SELECT sj.Name, 
    CASE 
        WHEN sja.start_execution_date IS NULL THEN 'Not running' 
        WHEN sja.start_execution_date IS NOT NULL AND sja.stop_execution_date IS NULL THEN 'Running' 
        WHEN sja.start_execution_date IS NOT NULL AND sja.stop_execution_date IS NOT NULL THEN 'Not running' 
    END AS 'RunStatus', 
	CASE WHEN enabled=1 THEN 'Enabled' 
               ELSE 'Disabled' 
          END [Job Status], 
		  convert(varchar(20), date_created, 20) as job_creation_date, 
		  convert(varchar(20), date_modified, 20) as job_modified_date 
FROM msdb.dbo.sysjobs sj 
JOIN msdb.dbo.sysjobactivity sja 
ON sj.job_id = sja.job_id 
WHERE session_id = ( 
    SELECT MAX(session_id) FROM msdb.dbo.sysjobactivity); 
END 
