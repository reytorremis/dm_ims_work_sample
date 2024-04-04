CREATE PROCEDURE [dbo].[sp_dbims_extract_log_and_data_info] 
AS 
IF OBJECT_ID('tempdb.dbo.#space') IS NOT NULL
BEGIN
 DROP TABLE #space;
END

IF OBJECT_ID('tempdb.dbo.##extract_log_and_data') IS NOT NULL
BEGIN
 DROP TABLE ##extract_log_and_data;
END


CREATE TABLE #space (
      database_id INT PRIMARY KEY
    , data_used_size DECIMAL(18,2)
    , log_used_size DECIMAL(18,2)
);

DECLARE @SQL NVARCHAR(MAX);

SELECT @SQL = STUFF((
    SELECT '
    USE [' + d.name + ']
    INSERT INTO #space (database_id, data_used_size, log_used_size)
    SELECT
          DB_ID()
        , SUM(CASE WHEN [type] = 0 THEN space_used END)
        , SUM(CASE WHEN [type] = 1 THEN space_used END)
    FROM (
        SELECT s.[type], space_used = SUM(FILEPROPERTY(s.name, ''SpaceUsed'') * 8. / 1024)
        FROM sys.database_files s
        GROUP BY s.[type]
    ) t;'
    FROM sys.databases d
    WHERE d.[state] = 0
    FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 2, '');

EXEC sys.sp_executesql @SQL;

declare @tabx table (database_id bigint, log_size bigint, data_size bigint, total_size bigint) 

insert into @tabx (database_id, log_size, data_size, total_size) 
SELECT
          database_id
        , log_size = CAST(SUM(CASE WHEN [type] = 1 THEN size END) * 8. / 1024 AS DECIMAL(18,2))
        , data_size = CAST(SUM(CASE WHEN [type] = 0 THEN size END) * 8. / 1024 AS DECIMAL(18,2))
        , total_size = CAST(SUM(size) * 8. / 1024 AS DECIMAL(18,2))
    FROM sys.master_files
    GROUP BY database_id 


SELECT
      d.database_id
    , d.name
    , d.state_desc
    , d.recovery_model_desc
    , t.total_size as total_size_mb
    , t.data_size as data_size_mb
    , s.data_used_size as data_used_size_mb
    , t.log_size as log_size_mb
    , s.log_used_size as log_used_size_mb 
	into ##extract_log_and_data
FROM @tabx t 
JOIN sys.databases d ON d.database_id = t.database_id 
LEFT JOIN #space s ON d.database_id = s.database_id 
ORDER BY t.total_size DESC;