CREATE PROCEDURE [dbo].[sp_dbims_extract_memory_capacity] 
AS
	SELECT distinct 
	volume_mount_point, 
	file_system_type,
	cast(total_bytes/1024/1024/1024. as decimal(10,2)) as total_size_mb, 
	cast(available_bytes/1024/1024/1024. as decimal(10,2)) as free_mb,
	case supports_compression when 1 then 'Y' else 'N' end  as supports_compression,
	case is_compressed when 1 then 'Y' else 'N' end as is_compressed,
	convert(varchar(20), getdate(), 20) as last_check_date 
	from sys.master_files AS f 
	CROSS APPLY sys.dm_os_volume_stats(f.database_id, f.file_id)