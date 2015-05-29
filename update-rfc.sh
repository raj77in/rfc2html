#!/bin/bash -
#===============================================================================
#
#          FILE: update-rfc.sh
#
#         USAGE: ./update-rfc.sh
#
#   DESCRIPTION:
#
#       OPTIONS: ---
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: Amit Agarwal (aka)
#  ORGANIZATION: http://blog.amit-agarwal.co.in
# Last modified: Fri May 29, 2015  12:23PM
#       CREATED: 04/23/2013 05:51:06 PM IST
#      REVISION:  ---
#===============================================================================

DATADIR=/srv/RFC/
MAILTO="your@email"

#### ---- No need to change anything below this.
cat <<EOF
To: $MAILTO
From: roamware@Ubuntu.roamware.com
Subject: [Cron] $(/bin/hostname --short) - $(date +%F) $sub
MIME-Version: 1.0
Content-Type: text/html
Content-Disposition: inline

EOF

getFile ()
{
    filename=${1##*/}
    cp $filename{,.backup}
    wget -o /dev/null -N  $1
    echo "Downloaded file $filename"
}	# ----------  end of function getFile  ----------
echo "Workign directory is $DATADIR"
cd $DATADIR


#### wget http://www.w3.org/Protocols/rfc2616/rfc2html.pl

getFile ftp://ftp.rfc-editor.org/in-notes/tar/RFC-all.tar.gz
tar xf RFC-all.tar.gz
echo "Extracted RFC-all.tar.gz"
