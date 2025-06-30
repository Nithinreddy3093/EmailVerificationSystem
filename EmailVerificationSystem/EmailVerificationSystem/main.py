#!/usr/bin/env python3
"""
Flask wrapper to run PHP application through subprocess
"""

import subprocess
import os
from flask import Flask, request, Response
import tempfile

app = Flask(__name__)

def run_php_script(script_name, method='GET', form_data=None):
    """Execute PHP script and return output"""
    env = os.environ.copy()
    env['REQUEST_METHOD'] = method
    env['HTTP_HOST'] = '0.0.0.0:5000'
    
    if method == 'POST' and form_data:
        # Convert form data to query string for PHP
        post_data = '&'.join([f"{k}={v}" for k, v in form_data.items()])
        env['CONTENT_LENGTH'] = str(len(post_data))
        env['CONTENT_TYPE'] = 'application/x-www-form-urlencoded'
        
        # Use subprocess with stdin for POST data
        process = subprocess.Popen(
            ['php', script_name],
            stdin=subprocess.PIPE,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            env=env,
            text=True
        )
        stdout, stderr = process.communicate(input=post_data)
    else:
        # GET request
        process = subprocess.run(
            ['php', script_name],
            capture_output=True,
            text=True,
            env=env
        )
        stdout = process.stdout
        stderr = process.stderr
    
    return stdout

@app.route('/', methods=['GET', 'POST'])
def index():
    """Serve index.php"""
    form_data = request.form.to_dict() if request.method == 'POST' else None
    return run_php_script('index.php', request.method, form_data)

@app.route('/unsubscribe.php', methods=['GET', 'POST'])
def unsubscribe():
    """Serve unsubscribe.php"""
    form_data = request.form.to_dict() if request.method == 'POST' else None
    return run_php_script('unsubscribe.php', request.method, form_data)

@app.route('/email_viewer.php', methods=['GET', 'POST'])
def email_viewer():
    """Serve email_viewer.php for development"""
    form_data = request.form.to_dict() if request.method == 'POST' else None
    return run_php_script('email_viewer.php', request.method, form_data)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)