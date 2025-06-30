#!/bin/bash

# Postfix configuration for local email delivery
echo "Configuring local mail server..."

# Create minimal postfix configuration
mkdir -p /tmp/postfix
cat > /tmp/postfix/main.cf << 'EOF'
# Basic postfix configuration for local delivery
myhostname = localhost
mydomain = localdomain
myorigin = $mydomain
inet_interfaces = localhost
mydestination = $myhostname, localhost, localhost.localdomain, $mydomain
relayhost = 
mynetworks = 127.0.0.0/8
mailbox_size_limit = 0
recipient_delimiter = +
inet_protocols = ipv4

# Mail delivery settings
home_mailbox = Maildir/
mailbox_command = 

# Logging
mail_log_level = 1
EOF

# Create simple master.cf
cat > /tmp/postfix/master.cf << 'EOF'
smtp      inet  n       -       n       -       -       smtpd
pickup    fifo  n       -       n       60      1       pickup
cleanup   unix  n       -       n       -       0       cleanup
qmgr      fifo  n       -       n       300     1       qmgr
tlsmgr    unix  -       -       n       1000?   1       tlsmgr
rewrite   unix  -       -       n       -       -       trivial-rewrite
bounce    unix  -       -       n       -       0       bounce
defer     unix  -       -       n       -       0       bounce
trace     unix  -       -       n       -       0       bounce
verify    unix  -       -       n       -       1       verify
flush     unix  n       -       n       1000?   0       flush
proxymap  unix  -       -       n       -       -       proxymap
proxywrite unix -       -       n       -       1       proxymap
smtp      unix  -       -       n       -       -       smtp
relay     unix  -       -       n       -       -       smtp
showq     unix  n       -       n       -       -       showq
error     unix  -       -       n       -       -       error
retry     unix  -       -       n       -       -       error
discard   unix  -       -       n       -       -       discard
local     unix  -       n       n       -       -       local
virtual   unix  -       n       n       -       -       virtual
lmtp      unix  -       -       n       -       -       lmtp
anvil     unix  -       -       n       -       1       anvil
scache    unix  -       -       n       -       1       scache
EOF

echo "Postfix configuration created at /tmp/postfix/"
echo "Configuration complete."