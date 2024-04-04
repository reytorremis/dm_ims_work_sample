""""
Project File: pg_extract_data.py
Author: Rey Lawrence Torrecampo
Details: Postgres Database Information Extraction Functions (Permanent Connection)
Created: 04/04/2022
"""

#  ------------------ Importing Libraries ------------------------------
from time import time
from datetime import datetime

#  ------------------ Importing Other Python Files ------------------------------
import local_db_connection as ldc
from python_connector_class import python_connector as pydc_connect
from log_info import system_log


#  ------------------ Defining Logging System ------------------------------
def error_log (_message:str):
    return system_log('E').log_results(_message)

def info_log (_message:str):
    return system_log('I').log_results(_message)

#  ------------------ Defining Other Functions ------------------------------
def date_today():
    return datetime.strptime(datetime.now().strftime('%Y-%m-%d  %H:%M:%S'), '%Y-%m-%d  %H:%M:%S')

def get_param(_x):
    return {
        1: 'connect',
        2: 'session',
        3: 'query',
        4: 'cache',
        5: 'table-memory',
        6: 'vacuum',
        7: 'index-cache',
        8: 'table-cache',
        9: 'database-memory',
        10: 'login'
    }.get(_x, None)

def get_category(_x):
    return {
        1: 'availability-connection-max_connection',
        2: 'availability-connection-active_connections',
        3: 'capacity-query_logs',
        4: 'capacity-cache-hit-ratio-overview',
        5: 'capacity-capacity-table',
        6: 'availability-vacuum',
        7: 'capacity-cache-hit-ratio-index',
        8: 'capacity-cache-hit-ratio-table',
        9: 'capacity-memory-database',
        10: 'availability-roles'
    }.get(_x, None)

#  ------------------ ETL for Postgres Other Information ------------------------------
def fx_pg_extract_data(_pc_id:str, step_id:int):
    pg_condet = ldc.get_permanent_connection_details(_pc_id)
    _time_init = time()
    sql = "call postgres.public.sp_pg_db_operations (?)"
    sql2 = "select * from temp_db_operations_tbl;"
    _msg = 'PG: '
    _cntr = 1
    conn_str = (
        "DRIVER={driver};"
        "UID={username};"
        "PWD={password};"
        "SERVER={server};"
        "PORT={port};".format(driver=pg_condet['driver'],
                              database='postgres',
                              username=pg_condet['dbusername'],
                              password=pg_condet['dbpassword'],
                              server=pg_condet['ipaddress'],
                              port=pg_condet['port'],
                              timeout=4))
    pg_con = pydc_connect(conn_str)
    if (pg_con.status == 'OK'):
        try:
            param = get_param(step_id)
            pg_con.execute(sql, param)
            x = pg_con.query(sql2)
            tab_name = ldc.get_table_name(pg_condet['platform'], sql, param)
            print(pg_condet['platform'], sql, param)
            _msg = _msg + '[' + str(_cntr) + ']' + 'Getting Table Information for sp_pg_db_operations=' + str(get_category(step_id)) + ';'
            if param in ('connect', 'cache', 'database-memory'):
                pass
            elif param in ('index-cache','table-cache'):
                _cntr += 1
                ldc.refresh_same_table(_pc_id, tab_name, 'T' if param == 'table-cache' else 'I')
                _msg = _msg + '[' + str(_cntr) + ']' + 'Refreshing table; '
            else:
                _cntr += 1
                ldc.refresh_table_state(_pc_id, tab_name)
                _msg = _msg + '[' + str(_cntr) + ']' + 'Refreshing table; '
            for row in x:
                ldc.pg_insert_to_table(_pc_id, pg_condet['platform'], sql, param,  row)
            ldc.log_db_procedure_execution_success(_pc_id, _time_init, sql, param)
            _cntr += 1
            _msg = _msg + '[' + str(_cntr) + ']' + 'Retrieving Data and Loading it to Local DB; '
            info_log(_msg)
        except Exception as e:
            _cntr += 1
            ldc.log_db_procedure_execution_error(_pc_id, _time_init, sql, param, str(e))
            error_log(_msg + '[' + str(_cntr) + ']' + str(e))
        finally:
            pg_con.close()
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, pg_con.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')

#  ------------------ ETL for Postgres Uptime ------------------------------
def fx_pg_get_uptime (_pc_id:str):
    pg_condet = ldc.get_permanent_connection_details(_pc_id)
    _pg_sql = "SELECT to_char(pg_postmaster_start_time(), 'YYYY-MM-DD hh24:mi:ss');"
    _time_init = time()
    conn_str = (
            "DRIVER={driver};"
            "UID={username};"
            "PWD={password};"
            "SERVER={server};"
            "PORT={port};".format(driver=pg_condet['driver'],
                                  username=pg_condet['dbusername'],
                                  password=pg_condet['dbpassword'],
                                  server=pg_condet['ipaddress'],
                                  port=pg_condet['port'],
                                  timeout=5))

    pg_con = pydc_connect(conn_str)
    if (pg_con.status == 'OK'):
        try:
            ldc.log_db_status_online(_pc_id)
            pg_uptime = pg_con.query(_pg_sql)
            for row in pg_uptime:
                result = ldc.log_pg_uptime_details(_pc_id, date_today(), row[0])
                info_log(result['Message']) if result['status'] == 'OK' else error_log(result['Message'])
            ldc.log_db_query_execution_success(_pc_id, _time_init, _pg_sql)
        except KeyError as e:
            ldc.log_db_query_execution_error(_pc_id, _time_init, _pg_sql, 'KeyError: ' + str(e))
            error_log('KeyError: ' + str(e))
        except Exception as e:
            ldc.log_db_query_execution_error(_pc_id, _time_init, _pg_sql, str(e))
            error_log(str(e))
        finally:
            pg_con.close()
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, pg_con.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')





