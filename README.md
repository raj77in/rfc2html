# rfc2html

Original idea and code from : [sf.net project](http://sourceforge.net/projects/rfc2html-php)

# Installation and configuration

## Setup of Cron

* First you need to configure the update-rfc.sh script and then add in crontab.
    + DATADIR - Directory where you would like your rfc's to be downloaded. Ideally this should be directory under your document root, so you can browse this.
    + MAILTO - Email address (your's preferably :) ) , so you get a mail when the cron is completed with results.
    + Add this script in cron for once a month or so with :

         ``* * 1 * * <path>/update-rfc.sh |/usr/sbin/sendmail -t``

## Setup of script

* Next, you need to download the jQuery datatables to the current folder along with the jquery js, with following commands:
    ``
    wget http://cdn.datatables.net/plug-ins/1.10.7/integration/jqueryui/dataTables.jqueryui.css
    wget //cdn.datatables.net/plug-ins/1.10.7/integration/jqueryui/dataTables.jqueryui.js
    wget http://code.jquery.com/jquery-2.1.4.min.js
    ``

* Now add the jquery js in index.php file.

Now you are all set to browse your rfc collection and viewer.


Please visit [my blog](http://blog.amit-agarwal.co.in "Amit Agarwals blog")

