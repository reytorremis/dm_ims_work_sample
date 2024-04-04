#!/usr/bin/env python

""""
Project File: run_manual.py
Author: Rey Lawrence Torrecampo
Details: Python Script for manual run. Run using interface
Created: 05/01/2022
"""
#  ------------------ Importing Libraries ------------------------------
import sys
#  ------------------ Importing Other Python Files ------------------------------
import run_script as rs

#  ------------------ Run Sequence ------------------------------
try:
    job_id = sys.argv[1]
    rs.sp_run(job_id)
    print(200)
except:
    print(500)

