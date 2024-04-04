""""
Project File: python_connector_class.py
Author: Rey Lawrence Torrecampo
Details: Pyodc Connector Class
Created: 04/07/2022
"""

#  ------------------ Importing Libraries ------------------------------
import pyodbc
import sys
from log_info import system_log

#  ------------------ Defining Classs ------------------------------
class python_connector:

    def __init__(self, name):
        try:
            self._conn = pyodbc.connect(name, timeout=30)
            self._cursor = self._conn.cursor()
            self._status = 'OK'
            self._error = ''
        except pyodbc.Error as ex:
            msg = str(ex.args[1]).replace('\n','')
            self._status = 'ER'
            self._error = msg
            self.__exit__(msg)
        except Exception:
            self._status = 'ER'
            self.__exit__(sys.exc_info())

    def __enter__(self):
        return self

    def __exit__(self, message):
        system_log('E').log_results(message)

    @property
    def connection(self):
        return self._conn

    @property
    def status(self):
        return self._status

    @property
    def cursor(self):
        return self._cursor

    def commit(self):
        self.connection.commit()

    def show_error(self):
        return self._error

    def execute(self, sql, params=None):
        self.cursor.execute(sql, params or ())

    def my_execute(self, sql, params=None):
        self.cursor.execute(sql, params or ())
        self.cursor.commit()

    def install(self, sql, params=None):
        try:
            self.cursor
            self.cursor.execute(sql, params or ())
            self.cursor.commit()
            return {'status': 'OK'}
        except Exception as e:
            return {'status': 'ERROR', 'output': e}


    def check_instance(self, _platform, _driver):
        try:
            if _platform == 'PG':
                self.cursor.execute("SELECT version();")
                return {'status': 'OK', 'output': _driver, 'version': self.fetchone()}
            elif _platform == 'MS':
                self.cursor.execute("SELECT @@version;")
                return {'status': 'OK', 'output': _driver, 'version': self.fetchone()}
            elif _platform == 'OR':
                self.cursor.execute('SELECT VERSION_TEXT FROM SYS."SM_$VERSION";')
                return {'status': 'OK', 'output': _driver, 'version': self.fetchone()}
            elif _platform == 'MY':
                self.cursor.execute("select version();")
                return {'status': 'OK', 'output': _driver, 'version': self.fetchone()}
        except Exception as e:
            return {'status': 'ERROR', 'output': e}
        finally:
            self.connection.close()

    def fetchall(self):
        return self.cursor.fetchall()

    def fetchone(self):
        return self.cursor.fetchone()

    def query(self, sql, params=None):
        self.cursor.execute(sql, params or ())
        return self.fetchall()

    def rollback (self):
        return self.cursor.rollback()

    def exec_proc_no_return(self, proc_name, params=None):
        try:
            self.cursor.execute("exec " + proc_name , params or ())
            self.cursor.commit()
            return {'status': 'OK'}
        except Exception as e:
            return {'status': 'ERROR', 'output': e}

    def exec_proc_w_return(self, proc_name, params=None):
        try:
            self.cursor.execute("exec " + proc_name, params or ())
            return {'status': 'OK', 'output': self.fetchall()}
        except Exception as e:
            return {'status': 'ERROR', 'output': e}

    def close(self, commit=True):
        if commit:
            self.commit()
        self.connection.close()
