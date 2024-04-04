""""
Project File: sql_server_extract_data.py
Author: Rey Lawrence Torrecampo
Details: SQL Server Database Information Extraction Functions (Permanent Connection)
Created: 04/02/2022
"""

#  ------------------ Importing Libraries ------------------------------
from time import time

#  ------------------ Importing Other Python Files ------------------------------
import local_db_connection as ldc
from python_connector_class import python_connector as pydc_connect
from log_info import system_log


#  ------------------ Importing Logging System ------------------------------
def error_log (_message:str):
    return system_log('E').log_results(_message)

def info_log (_message:str):
    return system_log('I').log_results(_message)

#  ------------------ Defining Other Functions ------------------------------
def get_sp_param(_x):
    return {
        1: {'category':'sp_check', 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_check_installed_sp] ?', 'sp_param': 'check', 'query': 'select * from ##sp_status_tbl'},
        2: {'category':'connection-max_connection', 'refresh': 'N','sp_name': '[dbo].[sp_dbims_display_connection_details] ?', 'sp_param': 'max', 'query': None},
        3: {'category':'connection-connection_breakdown','refresh': 'Y','sp_name': '[dbo].[sp_dbims_display_connection_details] ?', 'sp_param': 'breakdown', 'query': None},
        4: {'category':'connection-active_sessions', 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_display_connection_details] ?', 'sp_param':'session', 'query': None  },
        5: {'category':'backup', 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_extract_backup_info]' , 'sp_param': '', 'query': None},
        6: {'category':'growth_rate-backup', 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_extract_growth_rate] ?', 'sp_param':'backup', 'query': 'select * from ##check_back_up_size'},
        7: {'category':'growth_rate-database','refresh': 'Y','sp_name': '[dbo].[sp_dbims_extract_growth_rate] ?', 'sp_param': 'data', 'query': 'select * from ##check_data_size'},
        8: {'category':'agent-agent_status', 'refresh': 'N','sp_name': '[dbo].[sp_dbims_check_sql_agent_and_jobs] ?', 'sp_param':'agent', 'query': 'select * from ##temp_agent_chk'},
        9: {'category':'agent-job_status' , 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_check_sql_agent_and_jobs] ?', 'sp_param':'status', 'query': None},
        10: {'category':'agent-job_history', 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_check_sql_agent_and_jobs] ?', 'sp_param':'history', 'query': None},
        11: {'category':'capacity-db_log', 'refresh': 'Y','sp_name': '[dbo].[sp_dbims_extract_log_and_data_info]', 'sp_param':'', 'query': 'select * from ##extract_log_and_data'},
        12: {'category':'capacity-server', 'refresh': 'N','sp_name': '[dbo].[sp_dbims_extract_memory_capacity]', 'sp_param':'', 'query': None}
    }.get(_x, None)


#  ------------------ ETL FOR SQL SERVER OTHER INFO ------------------------------
def fx_sql_extract_db_data (_pc_id:str, _step_id:int):
    sql_condet = ldc.get_permanent_connection_details(_pc_id)
    _time_init = time()
    _cntr = 1
    x = get_sp_param(_step_id)
    _msg = 'MS: '
    conn_str = (
        "DRIVER={driver};"
        "UID={username};"
        "PWD={password};"
        "SERVER={server};"
        "PORT={port};".format(driver=sql_condet['driver'],
                              database='master',
                              username=sql_condet['dbusername'],
                              password=sql_condet['dbpassword'],
                              server=sql_condet['ipaddress'],
                              port=sql_condet['port'],
                              timeout=4))
    category = str(x['category'])
    refresh = x['refresh']
    sp_name = str(x['sp_name'])
    sp_param = str(x['sp_param'])
    query = str(x['query'])
    _tname = ldc.get_table_name(sql_condet['platform'], sp_name, sp_param)
    sql_con = pydc_connect(conn_str)
    if (sql_con.status == 'OK'):
        ldc.log_db_status_online(_pc_id)
        try:
            if query != 'None':
                con = sql_con.exec_proc_no_return(sp_name, sp_param)
                if con['status'] == 'OK':
                    sql_sp_info = sql_con.query(query)
                    _msg = _msg + '[' + str(_cntr) + ']' + 'Extracting data from SQL Server for ' + category + ';'
                    if refresh == 'Y':
                        _cntr += 1
                        ldc.refresh_table_state(_pc_id, _tname)
                        _msg = _msg + '[' + str(_cntr) + ']' + ' Refreshing Table; '
                    for row in list(filter(None, sql_sp_info)):
                        if category == 'sp_check':
                            ldc.check_sql_jobs_status(_pc_id, row[0], row[1], row[2], row[3], _tname)
                        elif category == 'growth_rate-backup':
                            ldc.store_back_up_growth_rate(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5],
                                                          row[6], _tname)
                        elif category == 'growth_rate-database':
                            ldc.store_database_growth_rate(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5],
                                                           row[6], row[7], row[8], row[9],
                                                           _tname)
                        elif category == 'agent-agent_status':
                            ldc.store_sql_agent_status(_pc_id, row[0], row[1], row[2], _tname)
                        elif category == 'capacity-db_log':
                            ldc.store_sql_db_logs_cap(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5], row[6],
                                                      row[7], row[8], _tname)
                        _cntr += 1
                        _msg = _msg + '[' + str(_cntr) + ']' + ' Insert records to ' + _tname + '; '
                    ldc.sp_log_system(_pc_id, 'I', _msg)
                    info_log(_msg)
                else:
                    raise ConnectionError
            else:
                con = sql_con.exec_proc_w_return(sp_name, sp_param)
                _msg = _msg + '[' + str(_cntr) + ']' + 'Extracting data from SQL Server using ' + category + ';'
                if refresh == 'Y':
                    _cntr += 1
                    ldc.refresh_table_state(_pc_id, _tname)
                    _msg = _msg + '[' + str(_cntr) + ']' + ' Refreshing Table; '
                if con['status'] == 'OK':
                    for row in list(filter(None, con['output'])):
                        if category == 'connection-max_connection':
                            ldc.store_max_connection(_pc_id, row[0], _tname)
                        elif category == 'connection-connection_breakdown':
                            ldc.store_breakdown_connection(_pc_id, row[0], row[1], row[2].strip(), _tname)
                        elif category == 'connection-active_sessions':
                            ldc.store_active_sessions(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5], row[6],
                                                      row[7], row[8], row[9], row[10], row[11], row[12], row[13],
                                                      _tname)
                        elif category == 'backup':
                            ldc.store_back_up_details(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5], row[6],
                                                      row[7], _tname)
                        elif category == 'agent-job_status':
                            ldc.store_job_status(_pc_id, row[0], row[1], row[2], row[3], row[4], _tname)
                        elif category == 'agent-job_history':
                            string = str(row[4]).replace('\'', '')
                            ldc.store_job_history(_pc_id, row[0], row[1], row[3], string, _tname)
                        elif category == 'capacity-server':
                            ldc.store_sql_memory_cap(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5], _tname)
                    _cntr += 1
                    _msg = _msg + '[' + str(_cntr) + ']' + ' Insert records to ' + _tname + '; '
                    ldc.sp_log_system(_pc_id, 'I', _msg)
                    info_log(_msg)
                else:
                    raise ConnectionError
            ldc.log_db_procedure_execution_success(_pc_id, _time_init, sp_name, sp_param)
        except Exception as e:
            _cntr += 1
            error_log(_msg + '[' + str(_cntr) + ']' + str(e))
            ldc.log_db_procedure_execution_error(_pc_id, _time_init, sp_name, sp_param, str(e))
        finally:
            sql_con.close()
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, sql_con.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')

#  ------------------ ETL FOR SQL SERVER UPTIME  ------------------------------
def fx_sql_get_uptime (_pc_id:str):
    sql_condet = ldc.get_permanent_connection_details(_pc_id)
    _time_init = time()
    _msg = ''
    _cntr = 1
    conn_str = (
            "DRIVER={driver};"
            "UID={username};"
            "PWD={password};"
            "SERVER={server};"
            "PORT={port};".format(driver=sql_condet['driver'],
                                  database='master',
                                  username=sql_condet['dbusername'],
                                  password=sql_condet['dbpassword'],
                                  server=sql_condet['ipaddress'],
                                  port=sql_condet['port'],
                                  timeout=4))
    sql_con = pydc_connect(conn_str)
    if (sql_con.status == 'OK'):
        try:
            ldc.transfer_sql_database_logs_to_uptime(_pc_id)
            sql_uptime = sql_con.query("Select convert(varchar, getdate(),20) [Last Server Check], convert(varchar, sqlserver_start_time,20) [Last Server Uptime] FROM master.sys.dm_os_sys_info;")
            for row in sql_uptime:
                ldc.log_sql_uptime_details(_pc_id, row[0], row[1])
            ldc.log_db_status_online(_pc_id)
            sql_db_uptime = sql_con.query("select name [Database Name], convert (varchar(20), DATABASEPROPERTYEX(name,'Status')) [Database Status],  convert(varchar, create_date ,20) [Database Service Date] from master.sys.databases;")
            for row in sql_db_uptime:
                ldc.log_sql_database_uptime_details(_pc_id, row[0], row[1], row[2])
                _cntr += 1
            ldc.log_db_uptime_check(_pc_id, _time_init)
            sql_con.close()
        except Exception as e:
            error_log('ERROR: ' + str(e))
            ldc.log_db_procedure_execution_error(_pc_id, _time_init, 'pyodbc', '', str(e))
            ldc.log_db_connection_error(_pc_id, _time_init, str(e))
            ldc.log_db_status_offline(_pc_id)
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, sql_con.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')



