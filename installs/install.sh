#!/bin/sh

export http_proxy=http://192.168.46.3:8080
#wget http://pkgs.repoforge.org/pdftk/pdftk-1.44-1.el6.rf.x86_64.rpm
rpm -ivh pdftk-1.44-1.el6.rf.x86_64.rpm
