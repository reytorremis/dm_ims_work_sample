""""
Project File: automated_run.py
Author: Rey Lawrence Torrecampo
Details: Python Script for automated run. Run in the background
Created: 05/01/2022
"""

#  ------------------ Importing Libraries ------------------------------

import run_script as rs
import time

#  ------------------ Defining Automated Workflow ------------------------------
def auto_workflow():
    rs.refresh_pending_jobs()
    time.sleep(2)
    rs.sp_run_automated()
    time.sleep(2)

#  ------------------ Main Automated Script ------------------------------
while 1:
    auto_workflow()
    time.sleep(60)


