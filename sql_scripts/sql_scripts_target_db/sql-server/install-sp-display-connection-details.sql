CREATE PROCEDURE [dbo].[sp_dbims_display_connection_details] 
@view_id varchar(10)  
AS 
	SET NOCOUNT ON; 
	IF @view_id = 'max' 
	BEGIN 
	select @@MAX_CONNECTIONS 
	END 

ELSE IF @view_id = 'breakdown' 
	BEGIN 
		SELECT DB_NAME(dbid) AS DBName, 
		COUNT(dbid) AS NumberOfConnections, 
		loginame 
		FROM   sys.sysprocesses 
		GROUP BY dbid, loginame 
		ORDER BY DB_NAME(dbid) 
	END; 
ELSE IF @view_id = 'session' 
	BEGIN 
		SELECT s.session_id, 
		s.login_name, 
		s.login_time, 
		s.last_request_end_time, 
		s.host_name, 
		s.program_name, 
		s.nt_user_name, 
		case s.is_user_process WHEN 1 THEN 'User Process' Else  'System Process'  end  ProcessInfo, 
		DB_NAME(s.database_id) AS [database], 
		s.status, 
		c.net_transport, 
		c.protocol_type, 
		c.client_net_address, 
		c.client_tcp_port 
		FROM sys.dm_exec_sessions s 
		INNER JOIN sys.dm_exec_connections c 
		ON s.session_id = c.session_id
	END; 
ELSE 
	BEGIN 
		SELECT 'NONE' 
	END; 