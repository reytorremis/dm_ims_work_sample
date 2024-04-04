""""
Project File: log_info.py
Author: Rey Lawrence Torrecampo
Details: Logging System. Logs are transcribe in text file instead of printing.
Created: 03/30/2022
"""

#  ------------------ Importing Libraries ------------------------------
import logging
from datetime import date
import os

#  ------------------ Defining Logging Class ------------------------------
class system_log:

    def __init__(self, log_type):
        self.log_type = log_type
        self.log_directory = os.path.join(os.getcwd(), 'db_ims-connection-logs-' + date.today().strftime("%b-%d-%Y")+'.log')

    def chk_file_exists(self):
        file_exists = os.path.exists(self.log_directory)
        return 'a' if file_exists is True else 'w'

    def log_results(self, message):
        logging.basicConfig(filename=self.log_directory, filemode=self.chk_file_exists(), format='[%(levelname)s %(asctime)s] : %(message)s', level=logging.DEBUG, datefmt='%Y-%m-%d %I:%M:%S %p')
        if self.log_type  == 'D':
            logging.debug(message)
        elif self.log_type  == 'I':
            logging.info(message)
        elif self.log_type == 'W':
            logging.warning(message)
        elif self.log_type  == 'E':
            logging.error(message)