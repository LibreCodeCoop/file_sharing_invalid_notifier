# File sharing invalid notifier

Notify all invalid links by email

## Install

* Download this repository
* Put on server on your app folder
* Install the app

## How to use

Create the group that will contain all users that you want to notify:
```
occ group:add invalid
```
Add users that will receive notifications to created group:
```
occ group:add invalid admin
```
Create app setting with name of group that you will put all users that you want to nofify when a invalid link is found:
```
occ config:app:set file_sharing_invalid_notifier invalid_link_group --value=invalid
```

> PS: Only users with email will be notified.
> PS²: You will need configure the [email server](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/email_configuration.html#email) on your instance
