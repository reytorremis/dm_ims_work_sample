""""
Project File: py_open_sql_scripts.py
Author: Rey Lawrence Torrecampo
Details: Python Script of Installing Stored Procedures
Created: 04/07/2022
"""

#  ------------------ Importing Libraries ------------------------------
import os, os.path

#  ------------------ Defining Reading SQL Script Function ------------------------------
def install_all_sql_scripts(_platform:str):
    _ctr = 0
    if _platform == 'MS': filepath_ext = 'sql_scripts/sql-server'
    elif _platform == 'PG': filepath_ext = 'sql_scripts/postgres'
    elif _platform == 'MY': filepath_ext = 'sql_scripts/my-sql'
    filepath = os.path.join(os.getcwd(), filepath_ext)
    try:
        files = [f for f in os.listdir(filepath) if os.path.isfile(os.path.join(filepath, f))]
        return {
            'status': 'OK',
            'filepath': filepath,
            'files': files,
            'count': len(files)
        }
    except FileNotFoundError as e:
        return {
            'status': 'ERROR',
            'message': e
        }