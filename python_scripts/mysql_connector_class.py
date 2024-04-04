""""
Project File: mysql_connector_class.py
Author: Rey Lawrence Torrecampo
Details: MySQL Connector Python Class
Created: 03/19/2022
"""

#  ------------------ Importing Libraries ------------------------------
import mysql.connector

#  ------------------ Defining Class ------------------------------
class mysql_connection_db:

    def __init__(self, name):
        self._conn = mysql.connector.connect(**name)
        self._cursor = self._conn.cursor(buffered=True)
        self._error = mysql.connector.Error

    def __enter__(self):
        return self

    def __exit__(self, exception_type, exception_val, trace):
        self.close()

    @property
    def connection(self):
        return self._conn

    @property
    def cursor(self):
        return self._cursor

    def commit(self):
        self.connection.commit()

    def close(self):
        self.connection.close()

    def execute(self, sql, params=None):
        self.cursor.execute(sql, params or ())

    def delete(self, sql, params=None):
        try:
            self.cursor.execute(sql, params or ())
            self.commit()
            return {'status': 'OK', 'output': ' deletion complete'}
        except(self._error) as e:
            return {'status': 'ERROR','output': e}

    def insert(self, sql, params=None):
        try:
            self.cursor.execute(sql, params or ())
            self.commit()
            return {'status': 'OK', 'output': ' insert complete'}
        except(self._error) as e:
            return {'status': 'ERROR','output': e}

    def fetchall(self):
        return self.cursor.fetchall()

    def fetchone(self):
        return self.cursor.fetchone()

    def extract_tbl_info(self, tblname):
        try:
            self.cursor.execute("select concat(tname, ' ' ,column_list) as table_info, no_of_rows from db_ims.vw_table_details where tname = '{tname}'".format(tname=tblname))
            self.commit()
            return {'status': 'OK','output': self.fetchone()}
        except(self._error) as e:
            return {'status': 'ERROR', 'output': e}

    def query(self, sql, params=None):
        try:
            self.cursor.execute(sql, params or ())
            return {'status': 'OK', 'row_count': self.cursor.rowcount,'output': self.fetchall()}
        except(self._error) as e:
            return {'status': 'ERROR', 'row_count': 0, 'output': e}


    def callproc(self, proc_name, params=None):
        try:
            return {'status': 'OK', 'output': self.cursor.callproc(proc_name, params or ())}
        except(self._error) as e:
            return {'status': 'ERROR', 'output': e}


