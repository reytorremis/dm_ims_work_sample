CREATE PROCEDURE [dbo].[sp_dbims_check_installed_sp] 
@proc_cmd varchar(10) 
AS 

IF OBJECT_ID('tempdb..##sp_status_tbl') IS NOT NULL 
BEGIN 
DROP TABLE ##sp_status_tbl 
END; 

CREATE TABLE ##sp_status_tbl
(
 sp_name varchar(100),
 sp_status varchar(50), 
 create_date varchar(20),
 modify_date varchar(20)
); 

declare @stored_proecdures table (sp_name varchar(100), sp_object_id bigint) 
insert into @stored_proecdures(sp_name, sp_object_id) 
values('sp_dbims_extract_memory_capacity', OBJECT_ID('dbo.sp_dbims_extract_memory_capacity')), 
('sp_dbims_display_connection_details', OBJECT_ID('dbo.sp_dbims_display_connection_details')), 
('sp_dbims_extract_growth_rate', OBJECT_ID('dbo.sp_dbims_extract_growth_rate')), 
('sp_dbims_extract_log_and_data_info', OBJECT_ID('dbo.sp_dbims_extract_log_and_data_info')), 
('sp_dbims_extract_backup_info', OBJECT_ID('dbo.sp_dbims_extract_backup_info')),
('sp_dbims_check_sql_agent_and_jobs', OBJECT_ID('dbo.sp_dbims_check_sql_agent_and_jobs')); 
	
insert into ##sp_status_tbl (sp_name, sp_status, create_date, modify_date) 
select 
a.sp_name, 
case when b.name is null then 'Not Installed' Else 'Installed' end as sp_status, 
convert(varchar(20), b.create_date, 20), 
convert(varchar(20), b.modify_date, 20) 
from @stored_proecdures a left join sys.objects b 
on b.type = 'P' and a.sp_object_id = b.OBJECT_ID; 

IF @proc_cmd = 'remove' 
BEGIN 
DECLARE sp_remove_cursor CURSOR  FOR select sp_name from ##sp_status_tbl where sp_status = 'Installed'; 
DECLARE @sp_name varchar(100); 
DECLARE @SQL NVARCHAR(MAX); 

OPEN sp_remove_cursor; 

FETCH NEXT FROM sp_remove_cursor INTO @sp_name 
WHILE @@FETCH_STATUS = 0 
BEGIN 
SET @SQL =  'Drop Procedure dbo.'+ @sp_name + ';' 

EXEC sys.sp_executesql @SQL; 

FETCH NEXT FROM sp_remove_cursor INTO @sp_name; 
END; 

CLOSE sp_remove_cursor; 

DEALLOCATE sp_remove_cursor; 
END 