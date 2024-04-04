""""
Project File: pydbc_initial_con.py
Author: Rey Lawrence Torrecampo
Details: Python Script for Establishing and Checking initial Connection with database
Created: 04/07/2022
"""

#  ------------------ Importing Libraries ------------------------------
import os, os.path
import time

#  ------------------ Other Python Files ------------------------------
import local_db_connection as ldc
import pyodbc
from python_connector_class import python_connector as pydc_connect
import py_open_sql_scripts as sp_scripts
from log_info import system_log


#  ------------------ Defining Logging Functions ------------------------------
def error_log (_message:str):
    return system_log('E').log_results(_message)

def info_log (_message:str):
    return system_log('I').log_results(_message)

#  ------------------ Defining dbconnection Fxns ------------------------------

# Get Equivalent Platform Name for Driver
def get_platform_name(_x):
    return {
        'MS': 'SQL Server',
        'MY':  'MySQL',
        'PG':  'PostgreSQL',
        'OR':  'Oracle',
    }.get(_x, 'Undefined')

# Get list of available drivers
def get_list_of_available_drivers(_y):
    driver_names = []
    for x in pyodbc.drivers():
        if x.endswith(get_platform_name(_y)) or x.startswith(get_platform_name(_y)):
            driver_names.append(x)
    return driver_names

# Check if connection is established
def initiliaze_temporary_connection(_temp_connection_id:int):
    info_log('Intializing Connection to temp ID ' + str(_temp_connection_id))
    _condetails = ldc.get_temp_connection_details(_temp_connection_id)
    _connection_string = 'Platform: ' + get_platform_name(_condetails['platform']) + ' Connection String: ' + \
                         _condetails['ipaddress'] + '=' + str( _condetails['port']) + ';' + \
                         _condetails['dbusername'] + '/' + _condetails['dbpassword']
    info_log('Retrieving record. ' + _connection_string)
    available_drivers = get_list_of_available_drivers(_condetails['platform'])
    hostname = _condetails['ipaddress']
    for i in range(len(available_drivers)):
        info_log('Checking Driver ' + str(available_drivers[i]))
        if _condetails['platform'] == 'OR':
            conn_str = (
                    "DRIVER={driver};"
                    "DATABASE={ora_db};"
                    "SERVER={server};"
                    "PORT={port};"
                    "UID={username};"
                    "PWD={password} as sysdba"
                        .format(driver=available_drivers[i],
                                          ora_db=_condetails['oracledb'],
                                          username=_condetails['dbusername'],
                                          password=_condetails['dbpassword'],
                                          server=_condetails['ipaddress'],
                                          port=_condetails['port'],
                                          timeout=1))
        elif _condetails['platform'] == 'MS':
            conn_str = (
                "DRIVER={driver};"
                "UID={username};"
                "Database={database};"
                "PWD={password};"
                "SERVER={server};"
                "PORT={port};".format(driver='{' + available_drivers[i] + '}',
                                                 database='master',
                                                 username=_condetails['dbusername'],
                                                 password=_condetails['dbpassword'],
                                                 server=_condetails['ipaddress'],
                                                 port=_condetails['port'],
                                                 timeout=1))
        else:
            conn_str = (
                    "DRIVER={driver};"
                    "UID={username};"
                    "PWD={password};"
                    "SERVER={server};"
                    "PORT={port};"
                    "Trusted_Connection=yes;".format(driver='{'+available_drivers[i]+'}',
                                          username=_condetails['dbusername'],
                                          password=_condetails['dbpassword'],
                                          server=_condetails['ipaddress'],
                                          port=_condetails['port'],
                                          timeout=1))
        # print(conn_str)
        pyconnection = pydc_connect(conn_str)
        # print(pyconnection.status)
        # print(available_drivers[i])
        if pyconnection.status == 'OK':
            try:
                x = pyconnection.check_instance(_condetails['platform'], available_drivers[i])
                if x['status'] == 'OK':
                    info_log('Recording Driver ' + str(available_drivers[i]) + ' successfully connected')
                    _status = 'OK'
                    _driver = x['output']
                    _version = str(x['version'][0]).replace('\n', ' ')
                    break
                else:
                    raise ConnectionError
            except:
                error_log('Failed to connect with ' + available_drivers[i])
                if i < len(available_drivers) - 1:
                    continue
                else:
                    _status = 'ERROR'
                    _driver = None
                    _version = None
                    break
        else:
            if i < len(available_drivers) - 1:
                continue
            else:
                _status = 'ERROR'
                _driver = None
                _version = None
                break

    return {'status': _status, 'driver': _driver, 'version':_version }

# Install Stored Procedure
def install_stored_procedure (_permanent_connection_id:str):
    db_con = ldc.get_permanent_connection_details(_permanent_connection_id)
    cntr = 0
    notins = []
    if db_con['platform'] == 'MS':
            conn_str = ("DRIVER={driver};"
                    "DATABASE={database};"
                    "UID={username};"
                    "PWD={password};"
                    "SERVER={server};"
                    "PORT={port};".format(driver= db_con['driver'],
                                          database='master',
                                          username= db_con['dbusername'],
                                          password= db_con['dbpassword'],
                                          server= db_con['ipaddress'],
                                          port= db_con['port'],
                                          timeout=5)
            )
    elif db_con['platform'] == 'PG':
            conn_str = ("DRIVER={driver};"
                        "DATABASE={database};"
                        "UID={username};"
                        "PWD={password};"
                        "SERVER={server};"
                        "PORT={port};".format(driver=db_con['driver'],
                                              database='postgres',
                                              username=db_con['dbusername'],
                                              password=db_con['dbpassword'],
                                              server=db_con['ipaddress'],
                                              port=db_con['port'],
                                              timeout=5)
                        )
    elif db_con['platform'] == 'MY':
        conn_str = ("DRIVER={driver};"
                    "DATABASE={database};"
                    "UID={username};"
                    "PWD={password};"
                    "SERVER={server};"
                    "PORT={port};".format(driver=db_con['driver'],
                                          database='mysql',
                                          username=db_con['dbusername'],
                                          password=db_con['dbpassword'],
                                          server=db_con['ipaddress'],
                                          port=db_con['port'],
                                          timeout=5)
                    )
    open_sp_scripts = sp_scripts.install_all_sql_scripts(db_con['platform'])
    if open_sp_scripts['status'] == 'ERROR':
        error_log(open_sp_scripts['message'])
        return {"status": "ERROR", "Message": open_sp_scripts['message']}
    else:
        pyconnection = pydc_connect(conn_str)
        for x in open_sp_scripts['files']:
            sp_name = str(x)
            try:
                sql_file = os.path.join(open_sp_scripts['filepath'], x)
                with open(sql_file, 'r', encoding='utf-8') as y:
                    fileAsString = y.read()
                sql_query = pyconnection.install(fileAsString)
                if sql_query['status'] == 'OK':
                    cntr += 1
                    info_log('SP ' + x.replace('-', '_') + ' installed')
                else:
                    raise ConnectionRefusedError
            except ConnectionRefusedError as e:
                error_log(sp_name.replace('-', '_') + 'not installed due to ' + str(sql_query['output']))
                notins.append(sp_name.replace('-', '_'))
            except KeyError as e:
                error_log(e)
                notins.append(sp_name.replace('-', '_'))
            except Exception as e:
                error_log(e)
                notins.append(sp_name.replace('-', '_'))
                continue

        message = str(cntr) + ' stored procedure installed. List of not installed: ' + ' '.join(map(str, notins))
        # pyconnection.close()
        return {"status": "OK", "Message": message}

# Function to check SQL SERVER SP is installed
def fx_sql_chk_sp (_pc_id:str):
    sql_condet = ldc.get_permanent_connection_details(_pc_id)
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
    try:
        sql_con = pydc_connect(conn_str)
        get_sp_count = ldc.check_number_of_unistalled_procedures(_pc_id)
        for i in range(get_sp_count):
            get_sp_name = ldc.get_job_name_of_unistalled_procedures(_pc_id)
            sql = "select 1 as result from sys.objects where type = 'P' and object_id = OBJECT_ID('dbo.{sp_name}')".format(sp_name=get_sp_name)
            sql_sp_check = sql_con.query(sql)
            for row in sql_sp_check:
                if row[0] == 1:
                    ldc.update_sp_status(_pc_id, get_sp_name, 'I')
                else:
                    ldc.update_sp_status(_pc_id, get_sp_name, 'N')
    except Exception as e:
        error_log(str(e))
        ldc.sp_log_system(_pc_id, 'E', str(e))
        ldc.log_db_status_online(_pc_id)

# Function to check POSTGRES SP is installed
def fx_pg_chk_sp (_pc_id:str):
    pg_condet = ldc.get_permanent_connection_details(_pc_id)
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


    try:
        pg_con = pydc_connect(conn_str)
        sql = "SELECT 1 FROM pg_catalog.pg_proc JOIN pg_namespace ON pg_catalog.pg_proc.pronamespace = pg_namespace.oid WHERE proname = 'sp_pg_db_operations' AND pg_namespace.nspname = 'public'"
        pg_sp_check = pg_con.query(sql)
        for row in pg_sp_check:
            if row[0] == 1:
                ldc.update_sp_status(_pc_id, 'sp_pg_db_operations', 'I')
            else:
                ldc.update_sp_status(_pc_id, 'sp_pg_db_operations', 'N')
    except Exception as e:
        error_log(str(e))
        ldc.sp_log_system(_pc_id, 'E', str(e))
        ldc.log_db_status_online(_pc_id)

# Function to check MYSQL SP is installed

def fx_my_chk_sp (_pc_id:str):
    my_condet = ldc.get_permanent_connection_details(_pc_id)
    conn_str = (
        "DRIVER={driver};"
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


    try:
        my_con = pydc_connect(conn_str)
        sql = "select 1 from information_schema.routines where routine_type='PROCEDURE' and ROUTINE_SCHEMA = 'mysql' and SPECIFIC_NAME = 'sp_extract_db_information'"
        my_sp_check = my_con.query(sql)
        for row in my_sp_check:
            if row[0] == 1:
                ldc.update_sp_status(_pc_id, 'sp_extract_db_information', 'I')
            else:
                ldc.update_sp_status(_pc_id, 'sp_extract_db_information', 'N')
    except Exception as e:
        error_log(str(e))
        ldc.sp_log_system(_pc_id, 'E', str(e))
        ldc.log_db_status_online(_pc_id)

# Process to connect to a database platform
def attempt_to_connect_procedure (_temp_connection_id:int):
    initialize_con = initiliaze_temporary_connection(_temp_connection_id)
    time.sleep(5)
    if initialize_con['status'] == 'OK':
        connection_type = ldc.change_connection_status(_temp_connection_id, 1)
        new_con_id = ldc.transfer_connection_details(_temp_connection_id, initialize_con['driver'], initialize_con['version'])
    else:
        connection_type = ldc.change_connection_status(_temp_connection_id, 0)
        new_con_id = None
    info_log('Modifying Local DB Record ID ' + str(_temp_connection_id) + ' to ' + connection_type)

    if new_con_id is not None:
        info_log('Creating Permanent Connection for ' +  str(_temp_connection_id))
        time.sleep(5)
        if new_con_id[:2] == 'OR':
            ldc.remove_temporary_connection_details(_temp_connection_id)
            info_log('Removing Temporary Connection for ' + str(_temp_connection_id))
            ldc.create_default_configuration(new_con_id)
            return new_con_id
        else:
            sp_install = install_stored_procedure(new_con_id)
            if sp_install['status'] == 'OK':
                info_log(sp_install['Message'])
                info_log('Removing Temporary Connection for ' + str(_temp_connection_id))
                ldc.remove_temporary_connection_details(_temp_connection_id)
                ldc.create_default_configuration(new_con_id)
                if new_con_id[:2] == 'MS':
                    fx_sql_chk_sp(new_con_id)
                elif new_con_id[:2] == 'PG':
                    fx_pg_chk_sp(new_con_id)
                elif new_con_id[:2] == 'MY':
                    fx_my_chk_sp(new_con_id)
                return new_con_id
            else:
                info_log('Removing Temporary Connection for ' + str(_temp_connection_id))
                ldc.remove_temporary_connection_details(_temp_connection_id)
                ldc.create_default_configuration(new_con_id)
                if new_con_id[:2] == 'MS':
                    fx_sql_chk_sp(new_con_id)
                elif new_con_id[:2] == 'PG':
                    fx_pg_chk_sp(new_con_id)
                elif new_con_id[:2] == 'MY':
                    fx_my_chk_sp(new_con_id)
                info_log(sp_install['Message'])
                return None
    else:
        error_log('Failed to transfer records as permanent connection')
        ldc.remove_temporary_connection_details(_temp_connection_id)
        info_log('Removing Temporary Connection for ' + str(_temp_connection_id))
    return None