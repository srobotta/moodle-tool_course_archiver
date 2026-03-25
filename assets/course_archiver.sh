#!/usr/bin/bash

# On the Moodle server, transfer all files and possible subdirectories, that
# do not exist in the backup dir of the server running this script.
# All new files are copied into the backup directory via rsync.
# The transfered files (the course backups .mbz) are put into a list in
# a temp file. This file is then used to display the tranfered files one by one
# so that these can be deleted on the remote server where they were copied from.
# SOURCE is the Moodle server and path where the course backup files are stored.
# TARGET is the backup directory on the server where this script is running, and
# where the course backup files are copied to.
SOURCE=moodle-server:/var/moodle-data/course_archiver/
TARGET=/mnt/backup/course_archive/

# Create the backup dir, when it doesn't exist yet.
if [ ! -d $TARGET ]; then
  mkdir -p $TARGET
  if [ $? -ne 0 ]; then
    echo "Error creating $TARGET"
    exit 1
  fi
fi

# Copy all files from the moodle server into the backup dir
# of this host.
tempfile=/tmp/course_archiver.$$
rsync -av $SOURCE $TARGET | grep .mbz > $tempfile

# No files found, then quit here.
if [ ! -f $tempfile ]; then
  exit
fi
size="$(wc -c <"$tempfile")"
if [ $size -eq 0 ]; then
  rm $tempfile
  exit
fi

# Display all copied files
echo "Copied course files:"
cat $tempfile

# Delete all files that we copied earlier on the remove server.
# Therefore, build correct host and path from SOURCE.
host=${SOURCE%:*}
path=${SOURCE#*:}
# Then loop over all files in the temp file that have been transferred.
while read file; do
  # On the server delete now the file we that was transfered before.
  echo "Delete ${host}:$path$file"
  # SSH is done a bit awkwardly because we must be sure that the command
  # returns a 0 code so that the loop is not stopped after the first file
  # has been deleted, leaving all other files on the remote server.
  ssh "$host" /bin/sh <<EOF
set +e
sudo -u www-data rm "$path$file"
exit 0
EOF
done < "$tempfile"
# Cleanup.
rm $tempfile
