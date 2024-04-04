""""
Project File: run_script.py
Author: Rey Lawrence Torrecampo
Details: Contains all Functions for manual run and automated run
Created: 05/01/2022
"""

#  ------------------ Importing Libraries ------------------------------
import time

#  ------------------ Importing Other Python Files ------------------------------
from log_info import system_log
import local_db_connection as ldc
import sql_server_extract_data as ssed
import pg_extract_data as ped
import mysql_extract_data as med
import oracle_extract_data as oed

#  ------------------ Defining Logging System ------------------------------
def error_log (_message:str):
    return system_log('E').log_results(_message)

def info_log (_message:str):
    return system_log('I').log_results(_message)

#  ------------------ Defining Function for Script Execution ------------------------------
## Run Job using SP ID
def sp_run(_jid):
    py_step_id = ldc.get_py_step_id(_jid)
    perm_con_id = ldc.get_perm_con_id_for_run(_jid)
    platfrom = ldc.get_platform_for_run(_jid)
    if platfrom == 'MS':
        if py_step_id == 0:
            ssed.fx_sql_get_uptime(perm_con_id)
        else:
            ssed.fx_sql_extract_db_data(perm_con_id, py_step_id)
    elif platfrom == 'PG':
        if py_step_id == 0:
            ped.fx_pg_get_uptime(perm_con_id)
        else:
            ped.fx_pg_extract_data(perm_con_id,py_step_id)
    elif platfrom == 'MY':
        if py_step_id == 0:
            med.fx_my_get_uptime(perm_con_id)
        else:
            med.fx_my_extract_data(perm_con_id,py_step_id)
    elif platfrom == 'OR':
        oed.fx_or_extract_data(perm_con_id,py_step_id)
    ldc.update_job_run(_jid)

## Automated Run Job Sequence
def sp_run_automated():
    _cnt = ldc.get_count_automated ()
    if _cnt > 0:
        for i in range(_cnt):
            try:
                _jid = ldc.get_job_id_automated()
                time.sleep(5)
                info_log('Sequence: ' + str(i) + ' / ' + str(_cnt) + '; execute job id = ' + str(_jid))
                sp_run(_jid)
            except Exception as e:
                ldc.update_job_run(_jid)
                info_log('Sequence: ' + str(i) + ' / ' + str(_cnt) + '; Failed to execute = ' + str(
                    _jid) + ' removing id; Error: ' + str(e))
    else:
        info_log('SKIPPING AUTOMATED RUN')

## Refresh Table
def refresh_pending_jobs():
    info_log('Refreshing Job List')
    ldc.refresh_pending_jobs()