CREATE PROCEDURE [dbo].[sp_dbims_extract_backup_info] 
AS
 SELECT
 Server, 
 database_name,
 Status,
 full_last_date,
 full_size_mb,
 log_last_date,
 log_size,
 DATEDIFF(hh, full_last_date, GETDATE()) AS back_up_age_hrs 
from (
  SELECT
		 CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server, 
          database_name,
		   case 
						when convert (varchar(20), DATABASEPROPERTYEX(b.name,'Status')) != 'ONLINE' then convert (varchar(20), DATABASEPROPERTYEX(b.name,'Status')) 
						when convert (varchar(20), DATABASEPROPERTYEX(b.name,'UserAccess')) != 'MULTI_USER' then convert (varchar(20), DATABASEPROPERTYEX(b.name,'UserAccess'))
						when convert (varchar(20), DATABASEPROPERTYEX(b.name,'IsInStandBy')) != '0' then 'Is In StandBy'
						when convert (varchar(20), DATABASEPROPERTYEX(b.name,'IsMergePublished')) != '0' then 'Is Merge Published'
						when convert (varchar(20), DATABASEPROPERTYEX(b.name,'IsPublished')) != '0' then 'Is Published'
						when convert (varchar(20), DATABASEPROPERTYEX(b.name,'IsSubscribed')) != '0' then 'Is Subscribed'
						else 'ONLINE'  end Status
        , full_last_date = MAX(CASE WHEN [type] = 'D' THEN backup_finish_date END)
        , full_size_mb = MAX(CASE WHEN [type] = 'D' THEN backup_size END)
        , log_last_date = MAX(CASE WHEN [type] = 'L' THEN backup_finish_date END)
        , log_size = MAX(CASE WHEN [type] = 'L' THEN backup_size END)
    FROM (
        SELECT
              s.database_name
            , s.[type]
            , s.backup_finish_date
            , backup_size =
                        CAST(CASE WHEN s.backup_size = s.compressed_backup_size
                                    THEN s.backup_size
                                    ELSE s.compressed_backup_size
                        END / 1048576.0 AS DECIMAL(18,2))
            , RowNum = ROW_NUMBER() OVER (PARTITION BY s.database_name, s.[type] ORDER BY s.backup_finish_date DESC)
        FROM msdb.dbo.backupmediafamily  f
   INNER JOIN msdb.dbo.backupset s ON f.media_set_id = s.media_set_id 
        WHERE s.[type] IN ('D', 'L')
    ) f join master..sysdatabases b on f.database_name = b.name
    WHERE f.RowNum = 1
    GROUP BY f.database_name, b.name
) x 