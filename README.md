# Login Shibboleth
Login Shibboleth replaces the default Piwik Login plug-in and offers the user a way to incorporate her/his existing Shibboleth with Piwik.

# Pre-Requirement
To use this plug-in you should have the following pre-requirements set.
- Your Shibboleth system is up and running and is connected to apache.
- You can access some data from $\_SERVER so, you can read user data from data.
- If you want to combine Shibboleth with LDAP, you should have php5-ldap or your distribution version of php-ldap installed.
- You are familiar with PHP coding, so you can change the custom parameters.
- You follow the same user model as Piwik. If you change the user model to contain some extra information you should also change this plug-in handling the way users are created.
- This plug-in support the following user information retrieval:
  - username (login)
  - email
  - alias (name or fullname)
  - token (random string length 32chars)
  - websites (access_level and websites id)

# Configuration
For the configuration following setting should be set in config/config.ini.php

## Datasource:

```
[datasource]  
datasource["alias"] = "shib"  
datasource["login"] = "shib"
datasource["email"] = "shib"
datasource["website"] = "shib, ldap"
datasource["superuser"] = "shib"
```

The datasource helps you to change the way this plug-in access the information need to generate or add users to Piwik. Normally Shibboleth (shib) should suffice. The software will look at the options in the order it is written so if you want Shibboleth to prevail add it first. If your first choice for an option in LDAP ad (ldap ) first. The MySQL database will automatically come to help of none of the option gives a result, which is most of the time improbable. if your User Model is different you can set other options here. If you use other resource means you can also define it here and the use the AuthLib class as parent class and extend it to your own Model.

## Shib:

```
[shib]
shib["login"] = "uid"
shib["alias"] = "fullName"
shib["email"] = "mail"
shib["superuser"] = "groupMembership"
shib["superuser_type"] = "string"
shib["superuser_param"] = "cn=RZ-Piwik-Admin"
shib["to_get_view"] = "groupMembership"
shib["to_get_view_type"] = "string"
shib["to_get_view_param"] = "cn=RZ-Piwik-View"
shib["to_get_admin"] = "groupMembership"
shib["to_get_admin_type"] = "string"
shib["to_get_admin_param"] = "cn=RZ-Piwik-View"
```

In shib you can set the Shibboleth parameter in which user data is caught. if you have some other settings it is possible to set them here. The information would be available in Shibboleth class.

## LDAP:

```
[ldap]
ldap["host"] = "ldaphost"
ldap["port"] = 636
ldap["user"] = "binduser"
ldap["password"] = "bindpassword"
ldap["dn"] = "binddn"
ldap["to_filter_view"] = "(userfilter)"
ldap["to_get_view"] = "website attr"
ldap["to_get_type_view"] = "string"
ldap["to_filter_admin"] = ""
ldap["to_get_admin"] = ""
ldap["to_get_type_admin"] = ""
```

The same applies here just like the Shibboleth settings above.

## Apache Settings
To exclude the public part of piwik, mainly the tracker and piwik.php from the Shibboleth Auth, the following settings have been set in apache VirtualHost.

###Apache 2.2
```
<LocationMatch "^/(?!piwik.js|piwik.php|opt_out.php)">
        AuthType shibboleth
        ShibRequireSession On
        require shibboleth
        require valid-user
        satisfy any
</LocationMatch>
<LocationMatch "/(piwik.js|piwik.php|opt_out.php)">
        satisfy all
        order allow,deny
        allow from all
</LocationMatch>
```
###Apache 2.4
```
<LocationMatch "^/(?!piwik.js|piwik.php|opt_out.php)">
        AuthType Shibboleth
        ShibrequestSetting requireSession 1
        Require valid-user
        satisfy all
</LocationMatch>
<LocationMatch "/(piwik.js|piwik.php|opt_out.php)">
        satisfy all
        order allow,deny
        allow from all
</LocationMatch>
```

# License
> The MIT License (MIT)

> Copyright (c) 2015 Pouyan Azari  
> Copyright (c) 2015 University of Würzburg

> Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

> The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
