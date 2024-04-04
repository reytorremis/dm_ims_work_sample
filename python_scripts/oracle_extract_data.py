""""
Project File: oracle_extract_data.py
Author: Rey Lawrence Torrecampo
Details: Oracle Database Information Extraction Functions (Permanent Connection)
Created: 05/07/2022
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
def get_tsql(_x):
    return {
        1: {'TSQL': 'SELECT TO_CHAR(STARTUP_TIME, \'YYYY-MM-DD HH24:MI:SS\') , TO_CHAR(SYSDATE, \'YYYY-MM-DD HH24:MI:SS\') FROM SYS.V_$INSTANCE', 'category':'availability-availability-uptimestastics'},
        2: {'TSQL': 'SELECT INSTANCE_NAME, HOST_NAME, STATUS, DATABASE_STATUS, ACTIVE_STATE, LOGINS FROM SYS.V_$INSTANCE', 'category':'availability-availability-dbstatus'},
        3: {'TSQL': 'SELECT trim(current_utilization) AS current_utilization, trim(limit_value) AS max_connections from v$resource_limit where resource_name=\'sessions\';', 'category':'capacity-capacity-connection'}
    }.get(_x, None)

#  ------------------ ETL for Oracle ------------------------------
def fx_or_extract_data(_pc_id:str, step_id:int):
    or_condet = ldc.get_permanent_connection_details(_pc_id)
    _time_init = time()
    sql = get_tsql(step_id)
    _msg = 'OR: '
    _cntr = 1
    conn_str = (
        "DRIVER={driver};"
        "DATABASE={ora_db};"
        "SERVER={server};"
        "PORT={port};"
        "UID={username};"
        "PWD={password} as sysdba"
            .format(driver= or_condet['driver'],
                    ora_db= or_condet['oracle_db'],
                    username= or_condet['dbusername'],
                    password= or_condet['dbpassword'],
                    server= or_condet['ipaddress'],
                    port= or_condet['port'],
                    timeout=1))
    or_con = pydc_connect(conn_str)
    if (or_con.status == 'OK'):
        ldc.log_db_status_online(_pc_id)
        try:
            result = or_con.query(sql['TSQL'])
            _msg = _msg + '[' + str(_cntr) + ']' + 'Getting Table Information for ' + str(sql['category']) + ';'
            if step_id == 1:
                for row in result:
                    ldc.log_pg_uptime_details(_pc_id, row[1], row[0])
            elif step_id == 2:
                for row in result:
                    ldc.or_record_instance_information(_pc_id, row[0], row[1], row[2], row[3], row[4], row[5])
            elif step_id == 3:
                for row in result:
                    ldc.or_record_connections(_pc_id, row[0], row[1])
            _cntr += 1
            _msg = _msg + '[' + str(_cntr) + ']' + 'Retrieving Data and Loading it to Local DB; '
            ldc.log_db_query_execution_success(_pc_id, _time_init, sql['TSQL'])
            info_log(_msg)
        except Exception as e:
            _cntr += 1
            ldc.log_db_query_execution_error(_pc_id, _time_init, sql['TSQL'], str(e))
            error_log(_msg + '[' + str(_cntr) + ']' + str(e))
        finally:
            or_con.close()
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, or_condet.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')





