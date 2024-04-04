""""
Project File: mysql_extract_data.py
Author: Rey Lawrence Torrecampo
Details: MySQL Database Information Extraction Functions (Permanent Connection)
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
def get_param(_x):
    return {
        1: {'param':'UPTIME','category': 'availability-uptime-statistics'},
        2: {'param':'INNODB','category': 'capacity-capacity-innodb'},
        3: {'param':'DATABASE','category': 'capacity-capacity-database'},
        4: {'param':'TABLE','category': 'capacity-capacity-table'},
        5: {'param':'TABLE-INDEX','category': 'capacity-capacity-index'},
        6: {'param':'PROCESSLIST','category': 'availability-connection-active-connections'},
        7: {'param':'CONNECTION','category': 'availability-connection-max-connection'},
        8: {'param':'LARGEST','category': 'capacity-capacity-largest'}
    }.get(_x, None)

#  ------------------ ETL for MySQL for Other Information ------------------------------
def fx_my_extract_data(_pc_id:str, step_id:int):
    my_condet = ldc.get_permanent_connection_details(_pc_id)
    _time_init = time()
    sql1 = "call mysql.sp_extract_db_information (?)"
    sql2 = "select * from mysql.db_ims_extract_tbl;"
    _msg = 'MY: '
    _cntr = 1
    conn_str = (
        "DRIVER={driver};"
        "Database={database};"
        "UID={username};"
        "PWD={password};"
        "SERVER={server};"
        "PORT={port};".format(driver=my_condet['driver'],
                              database='mysql',
                              username=my_condet['dbusername'],
                              password=my_condet['dbpassword'],
                              server=my_condet['ipaddress'],
                              port=my_condet['port']))

    my_con = pydc_connect(conn_str)
    if (my_con.status == 'OK'):
        try:
            param = get_param(step_id)
            my_con.execute(sql1, param['param'])
            x = my_con.query(sql2)
            tab_name = ldc.get_table_name(my_condet['platform'], sql1, param['param'])
            _msg = _msg + '[' + str(_cntr) + ']' + 'Getting Table Information for sp_pg_db_operations=' + str(param['category']) + ';'
            if param['param'] in ('UPTIME'):
                pass
            else:
                _cntr += 1
                ldc.refresh_table_state(_pc_id, tab_name)
                _msg = _msg + '[' + str(_cntr) + ']' + 'Refreshing table; '
                for row in x:
                    ldc.my_insert_to_table(_pc_id, my_condet['platform'], sql1, param['param'],  row)
                ldc.log_db_procedure_execution_success(_pc_id, _time_init, sql1, param['param'])
                _cntr += 1
                _msg = _msg + '[' + str(_cntr) + ']' + 'Retrieving Data and Loading it to Local DB; '
                info_log(_msg)
        except Exception as e:
            _cntr += 1
            ldc.log_db_procedure_execution_error(_pc_id, _time_init, sql1, param['param'], str(e))
            error_log(_msg + '[' + str(_cntr) + ']' + str(e))
        finally:
            my_con.close()
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, my_con.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')

#  ------------------ ETL for MySQL for Uptime ------------------------------
def fx_my_get_uptime (_pc_id:str):
    my_condet = ldc.get_permanent_connection_details(_pc_id)
    _my_sql = "Call mysql.sp_extract_db_information ('UPTIME')"
    _my_sql_extract = "select * from mysql.db_ims_extract_tbl;"
    _time_init = time()
    conn_str = (
        "DRIVER={driver};"
        "Database={database};"
        "UID={username};"
        "PWD={password};"
        "SERVER={server};"
        "PORT={port};".format(driver=my_condet['driver'],
                              database='mysql',
                              username=my_condet['dbusername'],
                              password=my_condet['dbpassword'],
                              server=my_condet['ipaddress'],
                              port=my_condet['port'],
                              timeout=4))

    my_condet = pydc_connect(conn_str)
    if (my_condet.status == 'OK'):
        try:
            ldc.log_db_status_online(_pc_id)
            my_condet.execute(_my_sql)
            my_uptime = my_condet.query(_my_sql_extract)
            for row in my_uptime:
                result = ldc.log_my_uptime_details(_pc_id, row[1], row[0])
                info_log(result['Message']) if result['status'] == 'OK' else error_log(result['Message'])
            ldc.log_db_query_execution_success(_pc_id, _time_init, _my_sql)
        except KeyError as e:
            ldc.log_db_query_execution_error(_pc_id, _time_init, _my_sql, 'KeyError: ' + str(e))
            error_log('KeyError: ' + str(e))
        except Exception as e:
            ldc.log_db_query_execution_error(_pc_id, _time_init, _my_sql, str(e))
            error_log(str(e))
        finally:
            my_condet.close()
    else:
        ldc.log_db_connection_error(_pc_id, _time_init, my_condet.show_error())
        ldc.log_db_status_offline(_pc_id)
        error_log('UNABLE TO CONNECT TO DATABASE')





