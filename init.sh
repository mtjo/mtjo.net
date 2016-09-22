#!/bin/bash
yum -y install subversion
#mount /dev/vdb1 /home
echo "install subversion"
echo "start subversion"
svnserve -d -r /home/svn/wzy/
echo "init SVN"
cp /etc/rc.local /etc/rc.local-backup
echo "#start SVN" >>/etc/rc.local

echo "svnserve -d -r /home/svn/wzy/" >>/etc/rc.local
cp /etc/fstab /etc/fstab-backup
echo "/dev/vdb1 /home ext4 defaults 0 0" >> /etc/fstab

