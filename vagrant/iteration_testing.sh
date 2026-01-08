#!/bin/bash

LOG_DIR="/tmp"

for i in {1..59} ; do 
    echo 'test cron' >> $LOG_DIR/test_cron.log 
    sleep 1 
done 