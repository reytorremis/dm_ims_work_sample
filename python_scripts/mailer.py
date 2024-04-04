#!/usr/bin/env python
""""
Project File: mailer.py
Author: Rey Lawrence Torrecampo
Details: Access Mailgun API to send email to user
Created: 03/29/2022
"""

#  ------------------ Importing Libraries ------------------------------
import requests
import json
import sys

#  ------------------ Functions ------------------------------
def get_subject_line(_x:int):
    return {
        1:"Welcome to DB IMS",
        2:"Password Recovery Link",
        3:"Account Username Changed",
        4:"Account Password Changed",
        5:"Account Username and Password Changed"
    }.get(_x, "Unspecified Email")

#  ------------------ Defining Variables ------------------------------
to_email = sys.argv[1]
get_username = sys.argv[2]
get_password = sys.argv[3]
sequence_no = sys.argv[4]

https = 'http://localhost/dbims/'
php_file = 'idx_password_change.php?'
data = 'email=' + str(to_email)
password_reset_url = https + php_file + data
subject = str(get_subject_line(sequence_no))

#  ------------------ Mail Function ------------------------------

if int(sequence_no) == 1:
    r = requests.post(
            # Here goes your Base API URL
            "https://api.mailgun.net/v3/mydomainis238.me/messages",
            # Authentication part - A Tuple
            auth=("api", "218e4a631534cdb64b28b9d0d19911f3-62916a6c-79cb9561"),

            # mail data will be used to send emails
            data={"from": "DB IMS AUTOMATED EMAIL<no_reply@mydomainis238.me>",
                     "to":[to_email], # passing a list or a signle email address with string data type.
                     "subject": subject,
                     "template": "alert_email_dbims",
                "h:X-Mailgun-Variables": json.dumps({"username": get_username, "password" : get_password })
                  })
else:
    r = requests.post(
        # Here goes your Base API URL
        "https://api.mailgun.net/v3/mydomainis238.me/messages",
        # Authentication part - A Tuple
        auth=("api", "218e4a631534cdb64b28b9d0d19911f3-62916a6c-79cb9561"),

        # mail data will be used to send emails
        data={"from": "DB IMS AUTOMATED EMAIL<no_reply@mydomainis238.me>",
              "to": [to_email],  # passing a list or a signle email address with string data type.
              "subject": subject,
              "template": "db_ims_password_recovery",
              "h:X-Mailgun-Variables": json.dumps({"password_reset_url": password_reset_url})
              })

#  ------------------ Show Status Code  ------------------------------

print(r.status_code)