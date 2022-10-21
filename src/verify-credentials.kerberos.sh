#!/bin/bash

echo $2 | kinit $1
echo $?
