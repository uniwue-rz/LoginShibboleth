# Piwik Shibboleth Plugin
This Plug-in can be used to combine Shibboleth (Ldap) with Piwik login<br>and user manager. It is higly customizable and connected with the data piwik gets from the SSO server.
# Configuration
For the configuration following setting should be set in config/config.ini.php

```php
[shibboleth]
host = "yourldaphost"
port = ldapport
user = "ldapuser"
password = "ldappassword"
dn = "ldapdn"
logouturl = "ssologoutpgae" //Normally there is no logout with Shibboleth.
userkey = "REMOTE_USER" //$_SERVER key for the username.
```
