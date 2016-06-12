#!/bin/bash

if [ $# -gt 1 ]; then
    iterations=0
    max_wait=100
    while [ ${iterations} -lt ${max_wait} ]; do
        nc -vz $1 $2 2>&1 | grep -q open && break
        if [ ${iterations} -eq 0 ]; then
            echo "Waiting for service on $1:$2..."
        else 
            echo -n "."
        fi
        let "iterations++"
        sleep 1
    done

    if [ ${iterations} -eq ${max_wait} ]; then
        echo " failed!"
    elif [ ${iterations} -gt 1 ]; then
        echo ""
    fi
fi
