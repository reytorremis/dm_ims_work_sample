#!/usr/bin/env python

""""
Project File: php connector
Connection Details for Python Script. Use to connect and check python connection.

"""

# ------------------------ Importing Libraries ------------------------
import sys
import pydbc_initial_con as pycon

# ------------------------ Importing Variables -------------------------
temporary_connection_id = sys.argv[1]
x = pycon.attempt_to_connect_procedure(temporary_connection_id)

if x is not None:
    print(x)
