#!/bin/bash
#Актуальная база данных лежит на MySQL:
mysqldump -h 192.168.0.7 -u root -pscout orbit_adserver_install > ./database.sql
