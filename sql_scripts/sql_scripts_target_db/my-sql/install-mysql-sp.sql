CREATE DEFINER=`root`@`localhost` PROCEDURE `mysql`.`sp_extract_db_information`(in_param varchar(20))
begin
	drop temporary table if exists mysql.db_ims_extract_tbl;

	if in_param = 'INNODB' then
	create temporary table mysql.db_ims_extract_tbl (
		RIBPS_GB int,
		INNODB_Used_GB decimal(18,6)
	);

	insert into mysql.db_ims_extract_tbl (RIBPS_GB,INNODB_Used_GB)
	select 
	RIBPS as Recommended_INNODB_Size_GB,
	DataGB as INNODB_Used_GB
	from (
	SELECT CEILING(Total_InnoDB_Bytes*1.6/POWER(1024,3)) RIBPS FROM
	(SELECT SUM(data_length+index_length) Total_InnoDB_Bytes
	FROM information_schema.tables WHERE engine='InnoDB') A) x
	cross join (
	SELECT (PagesData*PageSize)/POWER(1024,3) DataGB FROM
	(SELECT variable_value PagesData
	FROM information_schema.global_status
	WHERE variable_name='Innodb_buffer_pool_pages_data') A,
	(SELECT variable_value PageSize
	FROM information_schema.global_status
	WHERE variable_name='Innodb_page_size') B) y;

	elseif in_param = 'DATABASE' then
	
	create temporary table mysql.db_ims_extract_tbl (
		DatabaseName varchar(100),
		SizeMB decimal(10,2)
	);

	insert into mysql.db_ims_extract_tbl(DatabaseName,SizeMB)
	SELECT 
		table_schema AS Database_Name
		,ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS SizeInMB
	FROM information_schema.tables
	GROUP BY table_schema;

	elseif in_param = 'TABLE' then
	
	create temporary table mysql.db_ims_extract_tbl (
		SchemaName varchar(100),
		TableName varchar(100),
		SizeMB decimal(10,2)
	);
	
	insert into mysql.db_ims_extract_tbl (SchemaName, TableName, SizeMB)
	SELECT 
		TABLE_SCHEMA,
		table_name AS TableName
		,coalesce(ROUND(((data_length + index_length) / 1024 / 1024), 2),0) AS SizeInMB
	FROM information_schema.TABLES
	WHERE TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql');

	elseif in_param = 'TABLE-INDEX' then
	
	create temporary table mysql.db_ims_extract_tbl (
		SchemaName varchar(100),
		TableName varchar(100),
		ColumnName varchar(255),
		Indexed varchar(100)
	);
	
	insert into mysql.db_ims_extract_tbl (SchemaName, TableName, ColumnName, Indexed)
	SELECT 
		t.TABLE_SCHEMA
		,t.TABLE_NAME
		,c.COLUMN_NAME
		,IFNULL(kcu.CONSTRAINT_NAME, 'Not indexed') AS Indexed
	FROM information_schema.TABLES as t
	INNER JOIN information_schema.COLUMNS as c
		ON c.TABLE_SCHEMA = t.TABLE_SCHEMA
			AND c.TABLE_NAME = t.TABLE_NAME
			AND c.COLUMN_NAME LIKE '%_id'
	LEFT JOIN information_schema.KEY_COLUMN_USAGE as kcu
		ON kcu.TABLE_SCHEMA = t.TABLE_SCHEMA
			AND kcu.TABLE_NAME = t.TABLE_NAME
			AND kcu.COLUMN_NAME = c.COLUMN_NAME
			AND kcu.ORDINAL_POSITION = 1
	WHERE kcu.TABLE_SCHEMA IS NULL
	AND t.TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql');

	elseif in_param = 'UPTIME' then
	
	create temporary table mysql.db_ims_extract_tbl (
		LastStartUp DateTime,
		CurrentTimestamp DateTime
	);

	insert into mysql.db_ims_extract_tbl(LastStartUp, CurrentTimestamp)
	SELECT DATE_SUB(now(), INTERVAL variable_value SECOND) as LastStartUp,
	now() as CurrentTimestamp
	from information_schema.global_status where variable_name='Uptime';

	elseif in_param = 'PROCESSLIST' then
	
	create temporary table mysql.db_ims_extract_tbl (
		ID Bigint, 
		UserName varchar(100),
		DatabaseName varchar(100),
		Host varchar(100),
		State varchar(100),
		Command varchar(255),
		Info text
	);

	insert into mysql.db_ims_extract_tbl (ID, UserName, DatabaseName, Host, State, Command, Info)
	select ID, user, db, host, state, command, info from INFORMATION_SCHEMA.PROCESSLIST;
	
	elseif in_param = 'CONNECTION' then
	
	create temporary table mysql.db_ims_extract_tbl (
		max_connection Bigint, 
		max_used_connection Bigint
	);

	insert into mysql.db_ims_extract_tbl (max_connection,max_used_connection)
	select
	max_connection,
	max_used_connections from
	(select 
	VARIABLE_VALUE as max_connection
	from information_schema.GLOBAL_VARIABLES gv 
	where VARIABLE_NAME = 'MAX_CONNECTIONS') x cross join 
	(
	select VARIABLE_VALUE as max_used_connections from information_schema.GLOBAL_STATUS 
	where VARIABLE_NAME='max_used_connections'
	) y;
	
	elseif in_param = 'LARGEST' then
	
	create temporary table mysql.db_ims_extract_tbl (
		TotalTableCnt Bigint,
		SchemaName varchar(100),
		TotalRowCnt varchar(255),
		TotalTableSize varchar(255),
		TotalTableIndex varchar(255),
		TotalSize varchar(255)
	);
	
	insert into mysql.db_ims_extract_tbl (TotalTableCnt, SchemaName, TotalRowCnt, TotalTableSize, TotalTableIndex, TotalSize)
	SELECT
	COUNT(*) AS TotalTableCount
	,table_schema
	,CONCAT(coalesce(ROUND(SUM(table_rows)/power(1000,2),2),0),' MB') AS TotalRowCount
	,CONCAT(coalesce(ROUND(SUM(data_length)/power(1024,2),2),0),' MB') AS TotalTableSize
	,CONCAT(coalesce(ROUND(SUM(index_length)/power(1024,2),2),0),' MB') AS TotalTableIndex
	,CONCAT(coalesce(ROUND(SUM(data_length+index_length)/power(1024,2),2),0),' MB') TotalSize	
	FROM information_schema.TABLES
	GROUP BY table_schema
	ORDER BY SUM(data_length + index_length) 
	DESC LIMIT 10;
	
end if;
	
end