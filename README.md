# LoginShibboleth

LoginShibboleth is the Shibboleth/LDAP login plug-in for Piwik.

# Installation

The plug-in can be directly downloaded from the Piwik Marketplace. For the last developer version (unstable), just clone this repository.     

**Caution**: This plug-in needs some configuration and will not work out of box. Read the configuration before activating the plug-in.

# Usage

Make sure your Shibboleth implementation is working as it should and you have `$_SERVER` parameters available.   

There is a very basic configuration needed to make this plug-in usable. This can be added to the `piwik.conf.ini`.
```
[LoginShibboleth]
shibboleth_user_login = "uid"
shibboleth_user_alias = "fullName"
shibboleth_user_email = "mail"
shibboleth_separator = ";"
shibboleth_superuser_groups = "cn=piwiksuper,ou=unit,o=org"
shibboleth_group = "groupMembership"
```
**Caution 1**: The SuperUser should be the member of `cn=piwiksuper,ou=unit,o=org`.   
**Caution 2**: This plug-in deactivates every other plug-in installed for the Login purpose. As a result after activation, you can only login through Shibboleth.

With these settings, it is safe to activate the plug-in and then try to set the other configuration for the view and admin users with the help of configuration panel which will be available to the SuperUser in Settings Menu.

For Detailed Installation scenarios please check the Wiki.

# TODO

- Complete the test cases
- Add caching capability
- Finishing the Wiki
- Have LDAP adapter as a separate plug-in
- Make the plugin work out of the box

# Contribute
If you find any bug or error in this product please fill it in github. Merge request in github will also be accepted, if suitable. For API documentation go here. Language updates can also be added. Take the `lang/en.json` as template.
