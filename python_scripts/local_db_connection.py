""""
Project File: local_db_connection.py
Author: Rey Lawrence Torrecampo
Details: Contains all Functions Local Database Connections such as Calling Stored Procedures, Inserting Values, etc.
Created: 04/07/2022
"""

#  ------------------ Importing Libraries ------------------------------
from mysql_connector_class import mysql_connection_db as local_con
from datetime import datetime
from log_info import system_log
from time import time

#  ------------------ Importing Logging System ------------------------------
def error_log (_message:str):
    return system_log('E').log_results(_message)

def info_log (_message:str):
    return system_log('I').log_results(_message)

def date_today():
    return datetime.strptime(datetime.now().strftime('%Y-%m-%d  %H:%M:%S'), '%Y-%m-%d  %H:%M:%S')

#  ------------------ DB CONNECTION DETAILS ------------------------------
db_config = {
            'host': "127.0.0.1",                 # database host
            'port': 3306,                       # port
            'user': "db_ims_admin",              # username
            'passwd': "p@ssw0rd",                # password
            'db': "db_ims",                      # database
            'charset': 'utf8'                    # charset encoding
            }

# Define Local DB Connection
local_dbcon = local_con(db_config)

# # Define Curosr
# dbcursor = local_dbcon.cursor

#  ------------------ FUNX for Local DB ------------------------------

#  ------------------ Initial Connection ------------------------------
def change_connection_status(_temp_connection_id: int, _connection_state):
    sp_param = [_temp_connection_id, _connection_state, '']
    sp_call = local_dbcon.callproc('change_connection_status', sp_param)
    local_dbcon.commit()
    return sp_call['output'][2] if sp_call['status'] == 'OK' else sp_call['output']

def transfer_connection_details(_temp_connection_id: int, _driver :str, _version:str):
    sp_param = [_temp_connection_id,  _driver, _version, '']
    sp_call = local_dbcon.callproc('transfer_connection_details', sp_param)
    local_dbcon.commit()
    return sp_call['output'][3] if sp_call['status'] == 'OK' else sp_call['output']

def remove_temporary_connection_details(_temp_connection_id :int):
    sp_param = [_temp_connection_id]
    sp_call = local_dbcon.callproc('remove_temporary_connection_details', sp_param)
    local_dbcon.commit()
    return "Temp ID {} remove from local database".format(_temp_connection_id) if sp_call['status'] == 'OK' \
        else sp_call['output']

def get_temp_connection_details(_temp_connection_id):
    sql_procedure_query = "Select username, server, port, admin_username, admin_password, " \
                          "paltform_val as platform, oracle_db from vw_db_connection_details_temporary where con_id_no_temp = %s;"
    data_tuple = (int(_temp_connection_id),)
    query = local_dbcon.query(sql_procedure_query, data_tuple)
    if query['status'] == 'OK' and query['row_count'] == 1:
        return {
            "status": "OK",
            "userinfo": query['output'][0][0],
            "ipaddress": query['output'][0][1],
            "port": query['output'][0][2],
            "dbusername": query['output'][0][3],
            "dbpassword": query['output'][0][4],
            "platform": query['output'][0][5],
            "oracledb":  query['output'][0][6]
        }
    else:
        return {"status": "ERROR", "message" : "No records were extracted"}

def get_permanent_connection_details(_db_connection_string_id:str):
    sql_query = "select username, case when server = 'localhost' then '127.0.0.1' else server end as server, port, admin_username, " \
                         "admin_password, paltform_val, driver, oracle_db from db_ims.vw_db_connection_details_complete vdcdc where db_con_string_id = %s;"
    data_tuple = (_db_connection_string_id,)
    query = local_dbcon.query(sql_query, data_tuple)
    if query['status'] == 'OK' and query['row_count'] == 1:
        return { "userinfo": query['output'][0][0],
                "ipaddress": query['output'][0][1],
                "port": query['output'][0][2],
                "dbusername": query['output'][0][3],
                "dbpassword": query['output'][0][4],
                "platform": query['output'][0][5],
                "driver": query['output'][0][6],
                "oracle_db": query['output'][0][7]
            }
    else:
        return 'No records were extracted'

#  ------------------ System Logging Functions ------------------------------

def sp_log_system(_db_connection_string_id:str, _message_type:str, _message:str):
    sp_param = [_db_connection_string_id, _message_type, _message]
    local_dbcon.callproc('sp_log_to_systems_log_tbl', sp_param)
    local_dbcon.commit()

def sp_store_to_sp_history(_db_connection_string_id:str, _sp_name:str, _sp_param:str, _sp_estat:str):
    sp_param = [_db_connection_string_id, _sp_name, _sp_param, _sp_estat]
    local_dbcon.callproc('sp_store_to_sp_history', sp_param)
    local_dbcon.commit()

#  ------------------ Database Uptime Functions ------------------------------

def log_db_status_online(_permanent_con_id:str):
    sp_param = [_permanent_con_id, 'ONLINE']
    local_dbcon.callproc('sp_insert_status', sp_param)
    local_dbcon.commit()

def log_db_status_offline(_permanent_con_id:str):
    sp_param = [_permanent_con_id, 'OFFLINE']
    local_dbcon.callproc('sp_insert_status', sp_param)
    local_dbcon.commit()

#  ------------------ Query/SP Execution Functions ------------------------------

def log_db_query_execution_success(_permanent_con_id:str, _exec_start, _exec_query:str):
    _exec_time = round(time() - _exec_start,3)
    sp_param = [_permanent_con_id, _exec_time, _exec_query, '', '', 'Q', 'S']
    local_dbcon.callproc('sp_log_py_db_details', sp_param)
    local_dbcon.commit()

def log_db_procedure_execution_success(_permanent_con_id:str, _exec_start, _exec_proc:str, _proc_param:str):
    _exec_time = round(time() - _exec_start,3)
    sp_param = [_permanent_con_id, _exec_time, _exec_proc, _proc_param, '', 'P', 'S']
    local_dbcon.callproc('sp_log_py_db_details', sp_param)
    local_dbcon.commit()

def log_db_query_execution_error(_permanent_con_id:str, _exec_start, _exec_query:str, _error_msg:str):
    _exec_time = round(time() - _exec_start,3)
    sp_param = [_permanent_con_id, _exec_time, _exec_query, '', _error_msg, 'Q', 'E']
    local_dbcon.callproc('sp_log_py_db_details', sp_param)
    local_dbcon.commit()

def log_db_connection_error(_permanent_con_id:str, _exec_start, _error_msg:str):
    _exec_time = round(time() - _exec_start,3)
    sp_param = [_permanent_con_id, _exec_time, 'pyodbc', '', _error_msg, 'Y', 'E']
    local_dbcon.callproc('sp_log_py_db_details', sp_param)
    local_dbcon.commit()

def log_db_procedure_execution_error(_permanent_con_id:str, _exec_start, _exec_proc:str, _proc_param:str, _error_msg:str):
    _exec_time = round(time() - _exec_start,3)
    sp_param = [_permanent_con_id, _exec_time, _exec_proc, _proc_param, _error_msg, 'P', 'S']
    local_dbcon.callproc('sp_log_py_db_details', sp_param)
    local_dbcon.commit()

#  ------------------ Uptime Functions ------------------------------

def log_db_uptime_check(_permanent_con_id:str, _exec_start):
    _exec_time = round(time() - _exec_start,3)
    sp_param = [_permanent_con_id, _exec_time, 'pyodbc', '', '', 'Y', 'S']
    local_dbcon.callproc('sp_log_py_db_details', sp_param)
    local_dbcon.commit()


def log_sql_uptime_details(_permanent_con_id:str, _db_uptime_check:str, _db_last_check:str):
    sp_param = [_permanent_con_id, _db_uptime_check, _db_last_check, '']
    sp_call = local_dbcon.callproc('validate_uptime_records', sp_param)
    local_dbcon.commit()
    if sp_call['output'][3] == "OK": output = { "status": "OK", "Message": "Uptime Successfully Logged"}
    elif sp_call['output'][3] == "F1": output = {"status": "ERROR","Message": "Last Time Check Should at least more than a minute after DB went online"}
    elif sp_call['output'][3] == "F2": output = {"status": "ERROR","Message": "Database Check should not be retroactive"}
    else: output = {"status": "ERROR","Message": "Unknown Error"}
    return output

def log_pg_uptime_details(_permanent_con_id:str, _db_last_check:str, _db_uptime_check:str ):
    sp_param = [_permanent_con_id,  _db_last_check, _db_uptime_check, '']
    sp_call = local_dbcon.callproc('validate_uptime_records', sp_param)
    local_dbcon.commit()
    if sp_call['output'][3] == "OK": output = { "status": "OK", "Message": "Uptime Successfully Logged"}
    elif sp_call['output'][3] == "F1": output = {"status": "ERROR","Message": "Last Time Check Should at least more than a minute after DB went online"}
    elif sp_call['output'][3] == "F2": output = {"status": "ERROR","Message": "Database Check should not be retroactive"}
    else: output = {"status": "ERROR","Message": "Unknown Error"}
    return output

def log_my_uptime_details(_permanent_con_id:str, _db_last_check:str, _db_uptime_check:str ):
    sp_param = [_permanent_con_id,  _db_last_check, _db_uptime_check, '']
    sp_call = local_dbcon.callproc('validate_uptime_records', sp_param)
    local_dbcon.commit()
    if sp_call['output'][3] == "OK": output = { "status": "OK", "Message": "Uptime Successfully Logged"}
    elif sp_call['output'][3] == "F1": output = {"status": "ERROR","Message": "Last Time Check Should at least more than a minute after DB went online"}
    elif sp_call['output'][3] == "F2": output = {"status": "ERROR","Message": "Database Check should not be retroactive"}
    else: output = {"status": "ERROR","Message": "Unknown Error"}
    return output

#  ------------------ REFRESH TABLES ------------------------------
## Remove data from target table by deleteing records
def refresh_table_state(_permanent_con_id:str, tname:str):
    sql_query = "delete from {tname} where db_con_string_id = '{db_con_id}'".format(tname=tname, db_con_id=_permanent_con_id)
    query = local_dbcon.delete(sql_query)
    return tname + query['output'] if query['status'] == 'OK' else query['output']

## Remove data from target table by deleteing records without touching other loaded records
def refresh_same_table(_permanent_con_id:str, tname:str, _param2:str):
    sql_query = "delete from {tname} where db_con_string_id = '{db_con_id}' and obj_type = '{param2}'".format(tname=tname, db_con_id=_permanent_con_id, param2= _param2)
    query = local_dbcon.delete(sql_query)
    return tname + query['output'] if query['status'] == 'OK' else query['output']

#  ------------------ SQL SERVER FUNCTIONS ------------------------------
## ETL for SQL Server
def log_sql_database_uptime_details(_permanent_con_id:str, _db_name:str, _db_status:str,  _db_service_time:str):
    sp_param = [_permanent_con_id, _db_name, _db_status, _db_service_time, '']
    sp_call = local_dbcon.callproc('sp_add_database_statuses', sp_param)
    return {"status": "OK", "Message": sp_call['output'][4]}

def transfer_sql_database_logs_to_uptime(_permanent_con_id:str):
    sp_param = [_permanent_con_id, '']
    sp_call = local_dbcon.callproc('sp_update_database_details', sp_param)
    info_log(sp_call)
    return {"status": "OK", "Message": sp_call['output'][1]}

def sql_log_sp_history(_permanent_con_id:str, _sp_name:str, _sp_param:str, _sp_exe_stat:str):
    sp_param = [_permanent_con_id, _sp_name, _sp_param, _sp_exe_stat, 'MS']
    local_dbcon.callproc('sp_store_to_sp_history', sp_param)

def check_sql_jobs_status(_permanent_con_id:str, _sp_name:str, _sp_stat:str, _cdate:str,  _mdate:str, tname:str):
    tbl = local_dbcon.extract_tbl_info(tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{sp_name}', '{sp_stat}', '{cdate}' , '{mdate}', '{tstamp}')".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            sp_name=_sp_name,
            sp_stat=_sp_stat,
            cdate=datetime.strptime(_cdate, '%Y-%m-%d  %H:%M:%S'),
            mdate=datetime.strptime(_mdate, '%Y-%m-%d  %H:%M:%S'),
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ tname + 'Error Mesage:' + insert['output'])


def store_max_connection(_permanent_con_id:str, _max_con:int, tname:str):
    tbl = local_dbcon.extract_tbl_info(tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{max_con}', '{tstamp}')".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            max_con=_max_con,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ tname + 'Error Mesage:' + insert['output'])

def store_breakdown_connection(_permanent_con_id:str, _dbname:str, _no_con:int, _login:str, tname:str):
    tbl = local_dbcon.extract_tbl_info(tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{dbname}', {no_con} , '{login}', '{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            dbname=_dbname,
            no_con=_no_con,
            login=_login,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ tname + 'Error Mesage:' + insert['output'])

def store_active_sessions(_permanent_con_id:str, _sid:int, _logname:str, _logtime, _lasttime, _host:str, _program:str, _nt:str, _process:str, _db:str,_status:str, _net:str,  _prtcl:str,  _client:str, _port:str, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', {sid},'{logname}','{logtime}','{lasttime}','{host}','{program}',NULLIF('{nt}', 'None'),'{process}','{db}','{status}','{net}','{prtcl}','{client}',NULLIF('{port}', 'None'),'{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            sid=_sid,
            logname=_logname,
            logtime=_logtime,
            lasttime=_lasttime,
            host=_host,
            program=_program,
            nt=_nt,
            process=_process,
            db=_db,
            status=_status,
            net=_net,
            prtcl=_prtcl,
            client=_client,
            port=_port,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_back_up_details(_permanent_con_id:str, _server:str, _dbname:str, _dbstatus:str, _fdate:str, _fsize:float, _ldate:str, _lsize:float, _age:int, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{server}','{dbname}','{dbstatus}','{fdate}',{fsize},NULLIF('{ldate}', 'None'),NULLIF('{lsize}', 'None'),{age},'{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            server=_server,
            dbname=_dbname,
            dbstatus=_dbstatus,
            fdate=_fdate,
            fsize=_fsize,
            ldate=_ldate,
            lsize=_lsize,
            age=_age,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_back_up_growth_rate(_permanent_con_id:str, _dbname:str, _gyear:int, _gmonth:int, _bsize:float, _bdelta:float,  _cbsize:float, _cbdelta:float, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{dbname}',{gyear},{gmonth},{bsize},{bdelta},{cbsize},{cbdelta},'{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            dbname=_dbname,
            gyear=_gyear,
            gmonth=_gmonth,
            bsize=_bsize,
            bdelta=_bdelta,
            cbsize=_cbsize,
            cbdelta=_cbdelta,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_database_growth_rate(_permanent_con_id:str, _dbname:str, _gname:str, _fname:str, _ttaken:float, _sdate, _edate,  _chsize:float, _apname:str, _host:str, _log:str, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{dbname}','{gname}','{fname}','{ttaken}','{sdate}','{edate}', {chsize}, '{apname}','{host}', '{log}','{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            dbname=_dbname,
            gname=_gname,
            fname=_fname,
            ttaken=_ttaken,
            sdate=_sdate,
            edate=_edate,
            chsize=_chsize,
            apname=_apname,
            host=_host,
            log=_log,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_sql_agent_status(_permanent_con_id:str, _server:str, _version:str, _status:str, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{server}','{version}','{status}','{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            server=_server,
            version=_version,
            status=_status,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_job_status(_permanent_con_id:str, _jname:str, _rstat:str, _jstat:str, _cdate:str, _mdate:str, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{jname}','{rstat}','{jstat}','{cdate}','{mdate}','{tstamp}' )".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            jname=_jname,
            rstat=_rstat,
            jstat=_jstat,
            cdate=datetime.strptime(_cdate, '%Y-%m-%d  %H:%M:%S'),
            mdate=datetime.strptime(_mdate, '%Y-%m-%d  %H:%M:%S'),
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_job_history(_permanent_con_id:str, _jname:str, _rtime, _jout:str, _rmes:str, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{jname}','{rtime}','{jout}','{rmes}','{tstamp}')".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            jname=_jname,
            rtime=datetime.strptime(_rtime, '%Y-%m-%d  %H:%M:%S'),
            jout=_jout,
            rmes=_rmes,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_sql_db_logs_cap(_permanent_con_id:str, _dbid:int, _dbname:str, _dbstatus:str, _rmodel:str, _total:float, _data:float, _utdata:float, _log:float, _utlog:float, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', {dbid}, '{dbname}', '{dbstatus}','{rmodel}',{total},{data},NULLIF('{utdata}', 'None'),{log},NULLIF('{utlog}','None'),'{tstamp}')".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            dbid=_dbid,
            dbname=_dbname,
            dbstatus=_dbstatus,
            rmodel=_rmodel,
            total=_total,
            data=_data,
            utdata=_utdata,
            log=_log,
            utlog=_utlog,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

def store_sql_memory_cap(_permanent_con_id:str, _vol:str, _ftype:str, _total:float, _free:float, _sup:str, _com:str, _tname:str):
    tbl = local_dbcon.extract_tbl_info(_tname)
    if tbl['status'] == 'OK':
        d = datetime.now().strftime('%Y-%m-%d  %H:%M:%S')
        sql_query = "insert into {tname} values ('{db_con_id}', '{vol}', '{ftype}',{total},{free},'{sup}', '{com}', '{tstamp}')".format(
            tname=tbl['output'][0],
            db_con_id=_permanent_con_id,
            vol=str(_vol) + "\\",
            ftype=_ftype,
            total=_total,
            free=_free,
            sup=_sup,
            com=_com,
            tstamp=datetime.strptime(d, '%Y-%m-%d  %H:%M:%S')
        )
        insert = local_dbcon.insert(sql_query)
        if insert['status'] == 'ERROR': error_log('Error in storing to '+ _tname + 'Error Mesage:' + insert['output'])

#  ------------------ OTHER FUNCTIONS FOR DYNAMIC INSERT ------------------------------
def fx_chck (_x):
    if _x == 'None':
        return "NULLIF('{x}','None')".format(x=_x)
    elif str(_x).isdigit():
        return str(_x)
    else:
        _nformat = str(_x).replace('\'','')
        _y = _nformat.replace ('  ', '')
        return "'{x}'".format(x=_y)

## Get table name for table reference table
def get_table_name ( _platform:str, _pname:str, _param:str):
    sql = "select db_ims.sp_table_reference ('{platform}', '{pname}', '{param}', 'T')".format(
        platform=_platform,
        pname=_pname,
        param=_param
    )
    x = local_dbcon.query(sql)
    return x['output'][0][0]

#  ------------------ POSTGRES FUNCTIONS ------------------------------
## ETL for Postgres
def pg_insert_to_table (_permanent_connection_id:str, _platform:str, _pname:str, _param:str, *args):
    tab_name = get_table_name (_platform, _pname, _param)
    try:
        tbl = local_dbcon.extract_tbl_info(tab_name)
        sql_stmt = "insert into " + tbl['output'][0]
        ifnull = lambda i: i or 'None'
        xy = [ifnull(i) for i in args[0]]
        new_params = list(filter(None, xy))
        new_params2 = ','.join([fx_chck(elem) for elem in new_params])
        if _param in ('index-cache','table-cache'):
            stmt_ext = "Values ('{pid}', '{obj_type}', {params}, '{tstamp}')".format(
                pid=_permanent_connection_id,
                obj_type= 'T' if  _param == 'table-cache' else'I',
                params=new_params2,
                tstamp=date_today()
            )
        else:
            stmt_ext = "Values ('{pid}', {params}, '{tstamp}')". format(
                pid= _permanent_connection_id,
                params=new_params2,
                tstamp=date_today()
            )
        sql_stmt += "\n " +stmt_ext
        insert = local_dbcon.insert(sql_stmt)
        if insert['status'] == 'ERROR': error_log(insert['output'])
    except Exception as e:
        error_log(str(e))

#  ------------------ DB IMS JOB PROCEDURES ------------------------------
## Check number of procedures not installed during attempt to connect
def check_number_of_unistalled_procedures (_permanent_connection_id:str):
    sql = "select db_ims.get_number_of_unchecked_status('{con_string}')".format(con_string=_permanent_connection_id)
    x = local_dbcon.query(sql)
    return int(x['output'][0][0])

## Get Job Name from uninstalled procedures
def get_job_name_of_unistalled_procedures (_permanent_connection_id:str):
    sql = "select db_ims.get_unchecked_installation_status('{con_string}')".format(con_string=_permanent_connection_id)
    x = local_dbcon.query(sql)
    return x['output'][0][0]

## Update SP Status after run
def update_sp_status(_permanent_con_id:str, _tsql:str, _status:str):
    sp_param = [_permanent_con_id,  _tsql, _status]
    local_dbcon.callproc('alter_job_installation_status', sp_param)
    local_dbcon.commit()

## Execute default configuration of jobs upon installation
def create_default_configuration(_permanent_con_id:str):
    sp_param = [_permanent_con_id]
    local_dbcon.callproc('sp_create_default_job_configuration', sp_param)
    local_dbcon.commit()

## Get Python Step ID
def get_py_step_id (_job_id:str):
    sql = "select py_step_id from db_ims.vw_mysql_to_py_steps where job_id = '{jobid}'".format(jobid=_job_id)
    x = local_dbcon.query(sql)
    return int(x['output'][0][0])

## Get db_con_string_id from job_id
def get_perm_con_id_for_run (_job_id:str):
    sql = "select distinct db_con_string_id from db_ims.vw_mysql_to_py_steps where job_id = '{jobid}'".format(jobid=_job_id)
    x = local_dbcon.query(sql)
    return str(x['output'][0][0])

## Get databasae_platform from job_id
def get_platform_for_run (_job_id:str):
    sql = "select platform from db_ims.vw_mysql_to_py_steps where job_id = '{jobid}'".format(jobid=_job_id)
    x = local_dbcon.query(sql)
    return str(x['output'][0][0])

## Update run
def update_job_run(_job_id:str):
    sp_param = [_job_id]
    local_dbcon.callproc('update_last_run', sp_param)
    local_dbcon.commit()

## Get Priority Queue from automated run
def get_job_id_automated ():
    sql = "select db_ims.fx_get_priority_queue ()"
    x = local_dbcon.query(sql)
    return x['output'][0][0]

## Get Number of items in queue
def get_count_automated ():
    sql = "select count(distinct job_id) from db_ims.db_ims_automated_run_queue"
    x = local_dbcon.query(sql)
    return x['output'][0][0]

## Refresh Table for Automated run
def refresh_pending_jobs():
    sp_param = []
    local_dbcon.callproc('sp_populate_automated_tbl', sp_param)
    local_dbcon.commit()

## Uninstall all stored procedure in MS SQL SERVER
def sql_uninstall_all_stored_procedure():
    sp_param = []
    local_dbcon.callproc('sp_populate_automated_tbl', sp_param)
    local_dbcon.commit()

## Uninstall all stored procedure in Postgres
def pg_uninstall_all_stored_procedure():
    sp_param = []
    local_dbcon.callproc('sp_populate_automated_tbl', sp_param)
    local_dbcon.commit()

#  ------------------ MYSQL FUNCTIONS ------------------------------
## ETL for MySQL Database
def my_insert_to_table (_permanent_connection_id:str, _platform:str, _pname:str, _param:str, *args):
    tab_name = get_table_name (_platform, _pname, _param)
    try:
        tbl = local_dbcon.extract_tbl_info(tab_name)
        sql_stmt = "insert into " + tbl['output'][0]
        ifnull = lambda i: i or 'None'
        xy = [ifnull(i) for i in args[0]]
        new_params = list(filter(None, xy))
        new_params2 = ','.join([fx_chck(elem) for elem in new_params])
        stmt_ext = "Values ('{pid}', {params}, '{tstamp}')".format(
            pid=_permanent_connection_id,
            params=new_params2,
            tstamp=date_today())
        sql_stmt += "\n " +stmt_ext
        insert = local_dbcon.insert(sql_stmt)
        if insert['status'] == 'ERROR': error_log(insert['output'])
    except Exception as e:
        error_log(str(e))

#  ------------------ ORACLEFUNCTIONS ------------------------------
## ETL for MySQL Database for instance information
def or_record_instance_information(_permanent_connection_id:str, _inname:str, _host:str, _instat:str, _dbstat:str, _st:str, _log:str):
    sp_param = [_permanent_connection_id, _inname, _host, _instat, _dbstat, _st, _log]
    local_dbcon.callproc('or_instance_information', sp_param)
    local_dbcon.commit()

## ETL for MySQL Database for connections
def or_record_connections(_permanent_connection_id:str, _util:int, _max:int ):
    sp_param = [_permanent_connection_id, _util,  _max]
    local_dbcon.callproc('or_record_connections', sp_param)
    local_dbcon.commit()