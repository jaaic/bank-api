## Bank Api

A REST API to transfer funds from the sender's account to receiver's account

### How to setup?
clone the code to your machine
```
$ git clone git@github.com:tajawal/location-api.git
```

Make a copy of `.config.ini` and rename it to `.config.override.ini`, then put this line inside that file

```
[local:base]
```

#### ElasticSearch
By default elastic search not enabled inside dev-machine so you need to run it and attach it to container .
- Open makefile and add the following to `up:` command `dc-elasticsearch.yml` then run `make up`

#### Dependencies

Run this command to install project dependencies

```
$ composer install
```