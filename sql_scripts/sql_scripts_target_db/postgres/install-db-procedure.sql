create or replace procedure postgres.public.sp_pg_db_operations(stepname character varying)
language plpgsql
as $$
begin 
	if (stepname = 'connect') then 
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			max_conn int,
			used_con int,
			reserve_for_super_user int,
			reserver_for_normal_user int
		);
	
		insert into temp_db_operations_tbl(max_conn, used_con, reserve_for_super_user, reserver_for_normal_user)
		select max_conn,used,res_for_super, max_conn-used - res_for_super res_for_normal 
		from 
		  (select count(*) used from pg_catalog.pg_stat_activity) t1,
		  (select setting::int res_for_super from pg_settings where name='superuser_reserved_connections') t2,
		  (select setting::int max_conn from pg_settings where name='max_connections') t3;

	elseif (stepname = 'session') then
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			database_name varchar(100),
			username varchar(100),
			client_address varchar(100),
			state varchar(100),
			total_connections int,
			query varchar(1000)
		);
	
		insert into temp_db_operations_tbl(database_name, username, client_address, state, total_connections, query)
		SELECT datname as database ,usename as user ,client_addr,state, count(*) as total_connections,query
		FROM pg_stat_activity
		WHERE pid<>pg_backend_pid()
		GROUP BY usename,client_addr,datname,state,query;
	
	elseif (stepname = 'query') then
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			pid int,
			username varchar(100),
			time_running time,
			query varchar(1000)
		);
		
		insert into temp_db_operations_tbl(pid, username, time_running, query)
		select pid,usename, age(clock_timestamp(), query_start)::interval::time without time zone as time_running, substr(query, 0, 75)
		FROM pg_stat_activity WHERE state != 'idle'
		ORDER BY time_running DESC;
	
	elseif (stepname = 'cache') then
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			current_database_name varchar(100),
			cache_hit_ratio_index_percent decimal(10,2),
			cache_hit_ratio_tables_percent decimal(10,2)
		);
	
		insert into temp_db_operations_tbl (current_database_name, cache_hit_ratio_index_percent, cache_hit_ratio_tables_percent)
		 select current_database() , round(t1.cache_hit_ratio_index*100,2) as cache_hit_ratio_index_percent, round(t2.cache_hit_ratio_tables*100,2) as cache_hit_ratio_tables_percent from
 		(select SUM(idx_blks_hit) / (SUM(idx_blks_read) + SUM(idx_blks_hit)) as cache_hit_ratio_index FROM pg_statio_user_indexes) t1,
 		(SELECT SUM(heap_blks_hit)/(SUM(heap_blks_hit) +  SUM(heap_blks_read)) as cache_hit_ratio_tables FROM pg_statio_user_tables) t2;
	
 	elseif (stepname = 'table-memory') then
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			table_name varchar(100),
			schema_owner varchar(100),
			table_size varchar(100),
			index_size varchar(100),
			approximate_rows int
		);
		
		insert into temp_db_operations_tbl(table_name, schema_owner, table_size, index_size, approximate_rows)
		select distinct t.table_name, a.rolname as schema_owner, pg_size_pretty(pg_total_relation_size(c.oid)) as table_size,
	   pg_size_pretty(pg_total_relation_size(c.oid) - pg_relation_size(c.oid)) as index_toast_size,
	   cast(c.reltuples as bigint) as approximate_rows
	   from pg_class c 
	   join pg_authid a on c.relowner = a."oid"
	   join information_schema.tables t on c.relname = t.table_name
	   WHERE table_schema not in ('pg_catalog', 'information_schema') 
	   AND t.table_type='BASE TABLE' and pg_total_relation_size(c.oid) > 0 order by t.table_name, schema_owner asc;
	  
	elseif (stepname = 'vacuum') then
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			table_name varchar(100),
			schema_owner varchar(100),
			last_vacuum timestamp,
			last_autovacuum timestamp,
			num_tup int,
			dead_tup int,
			avg_threshold int,
			expected_av varchar(255)
		);
		
		insert into temp_db_operations_tbl(table_name, schema_owner, last_vacuum, last_autovacuum, num_tup, dead_tup, avg_threshold, expected_av)
		SELECT distinct psut.relname,a.rolname,
	     psut.last_vacuum::timestamp without time zone as last_vacuum,
	     psut.last_autovacuum::timestamp without time zone as last_autovacuum,
	     cast(c.reltuples as int) AS n_tup,
	     cast(psut.n_dead_tup as int) AS dead_tup,
	     cast(CAST(current_setting('autovacuum_vacuum_threshold') AS bigint)
	         + (CAST(current_setting('autovacuum_vacuum_scale_factor') AS numeric)
	            * c.reltuples) as int) AS av_threshold,
	     CASE
	         WHEN CAST(current_setting('autovacuum_vacuum_threshold') AS bigint)
	             + (CAST(current_setting('autovacuum_vacuum_scale_factor') AS numeric)
	                * c.reltuples) < psut.n_dead_tup
	         THEN '*'
	         ELSE ''
	     END AS expect_av
	 FROM pg_stat_user_tables psut
	     JOIN pg_class c on psut.relid = c.oid
	     join pg_authid a on c.relowner = a."oid" ORDER BY 1;
	    
	    
	 elseif (stepname = 'index-cache') then  
	 	drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			index_name varchar(100),
			schema_owner varchar(100),
			cache_hit_ratio_percent decimal(10,2)
		);
	
	 
		insert into temp_db_operations_tbl(index_name, schema_owner, cache_hit_ratio_percent)
	    SELECT relname, schemaname, 
		round((idx_blks_hit::decimal/ (idx_blks_hit + idx_blks_read))*100,2) as cache_hit_ratio_percent
		FROM pg_statio_user_indexes where idx_blks_hit > 0 or  idx_blks_read > 0;
	elseif (stepname = 'table-cache') then 
		
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			table_name varchar(100),
			schema_owner varchar(100),
			cache_hit_ratio_percent decimal(10,2)
		);
	
	 
		insert into temp_db_operations_tbl(table_name, schema_owner, cache_hit_ratio_percent)
		SELECT relname, schemaname, 
		round((heap_blks_hit::decimal/ (heap_blks_hit + heap_blks_read))*100,2) as cache_hit_ratio_percent
		FROM pg_statio_user_tables where heap_blks_hit > 0 or heap_blks_read > 0;
		
	elseif (stepname = 'database-memory') then 
		
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			database_name varchar(100),
			memory varchar(100),
			creation_date timestamp
		);
	
	 
		insert into temp_db_operations_tbl(database_name, memory, creation_date)
		SELECT pg_database.datname as database, 
		pg_size_pretty(pg_database_size(pg_database.datname)) AS size,
		(pg_stat_file('base/'||oid ||'/PG_VERSION')).modification
		FROM pg_database WHERE datistemplate=false AND pg_database_size(pg_database.datname) > 0;
		
	elseif (stepname = 'login') then 
		
		drop table if exists temp_db_operations_tbl;
		
		create temporary table temp_db_operations_tbl(
			obj_id int,
			schema_name varchar(100),
			parent_obj_id int,
			inherentance_map varchar(1000)
		);
		
		WITH RECURSIVE 
		cte1 as (
				SELECT b.oid, b.rolname, m.roleid as parentid
				FROM pg_catalog.pg_auth_members m
				RIGHT OUTER JOIN pg_catalog.pg_roles b ON (m.member = b.oid)
				WHERE b.rolname !~ '^pg_'
		),
		cte2 as (
				SELECT oid, rolname, parentid, CAST(rolname AS varchar(100)) AS inheritance_map
				FROM cte1 
				WHERE parentid IS NULL

				UNION all
				
				SELECT c1.oid, c1.rolname, c1.parentid,
				CAST(c2.inheritance_map || '->' || c1.rolname AS varchar(100)) AS inheritance_map
				FROM cte1 c1 INNER JOIN cte2 c2
				ON (c1.parentid = c2.oid)
		)


	insert into  temp_db_operations_tbl(obj_id, schema_name, parent_obj_id, inherentance_map)
	SELECT oid::int, rolname::varchar(100), parentid::int, inheritance_map::varchar(100)  FROM cte2;
		
	end if;
end; $$