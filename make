#!/bin/bash
#
# Redo Rescue: Backup and Recovery Made Easy <redorescue.com>
# Copyright (C) 2010-2020 Zebradots Software
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#

VER=2.0.1
BASE=stretch
ARCH=amd64
ROOT=rootdir
FILE=setup.sh
USER=redo

# Set colored output codes
red='\e[1;31m'
wht='\e[1;37m'
yel='\e[1;33m'
off='\e[0m'

# Show title
echo -e "\n$off---------------------------"
echo -e "$wht  REDO RESCUE ISO CREATOR$off"
echo -e "       Version $VER"
echo -e "---------------------------\n"

# Check: Must be root
if [ "$EUID" -ne 0 ]
	then echo -e "$red* ERROR: Must be run as root.$off\n"
	exit
fi

# Check: No spaces in cwd
WD=`pwd`
if [[ $WD == *" "* ]]
	then echo -e "$red* ERROR: Current absolute pathname contains a space.$off\n"
	exit
fi

# Action for "clean"
ACTION=$1
if [ "$ACTION" == "clean" ]; then
	rm -rf {image,$ROOT,*.iso}
	exit
fi

# Actions for build (default) and "changes"
if [ "$ACTION" != "changes" ]; then
	# Prepare debootstrap host environment
	echo -e "$yel* Building from scratch.$off"
	rm -rf {image,$ROOT,*.iso}
	CACHE=debootstrap-$BASE-$ARCH.tar.gz
	if [ -f "$CACHE" ]; then
		echo -e "$yel* $CACHE exists, extracting existing archive...$off"
		sleep 2
		tar zxvf $CACHE
	else 
		echo -e "$yel* $CACHE does not exist, running debootstrap...$off"
		sleep 2
		apt-get install debootstrap squashfs-tools grub-pc-bin \
			mtools grub-efi-amd64-bin syslinux \
			syslinux-common isolinux xorriso memtest86+
		rm -rf $ROOT; mkdir -p $ROOT
		debootstrap --arch=$ARCH --variant=minbase $BASE $ROOT
		tar zcvf $CACHE ./$ROOT	
	fi
else
	# Enter existing system shell to make changes
	echo -e "$yel* Updating existing image.$off"
fi

# Setup script (ALL): Base configuration
cat > $ROOT/$FILE <<EOL
#!/bin/bash

# System mounts
mount none -t proc /proc;
mount none -t sysfs /sys;
mount none -t devpts /dev/pts

# Set hostname
echo 'redorescue' > /etc/hostname
echo 'redorescue' > /etc/debian_chroot

# Set hosts
cat > /etc/hosts <<END
127.0.0.1	localhost
127.0.1.1	redorescue
::1		localhost ip6-localhost ip6-loopback
ff02::1		ip6-allnodes
ff02::2		ip6-allrouters
END

# Set default locale
cat >> /etc/bash.bashrc <<END
export LANG="C"
export LC_ALL="C"
END

# Export environment
export HOME=/root; export LANG=C; export LC_ALL=C;

EOL

# Setup script (BUILD): Install packages
if [ "$ACTION" != "changes" ]; then
if [ "$ARCH" == "i386" ]; then
	KERN="686"
else
	KERN="amd64"
fi
cat >> $ROOT/$FILE <<EOL
# Install packages
# Be sure to include "chromium-sandbox" for buster images with chromium
export DEBIAN_FRONTEND=noninteractive
apt install --no-install-recommends --yes \
	linux-image-$KERN live-boot systemd-sysv firmware-linux-free vim-tiny \
	xserver-xorg x11-xserver-utils xinit xterm openbox obconf obmenu \
	plymouth plymouth-themes compton libnotify-bin xfce4-notifyd beep \
	fdpowermon gir1.2-notify-0.7 laptop-detect pm-utils sudo dbus-x11 \
	network-manager-gnome fonts-lato xfce4-appfinder x11vnc pwgen slim \
	tint2 nitrogen gtk-theme-switch gtk2-engines numix-gtk-theme \
	gpicview mousepad lxappearance lxmenu-data lxrandr lxterminal volti \
	pcmanfm libfm-modules os-prober discover hdparm smartmontools lvm2 \
	gparted gnome-disk-utility gsettings-desktop-schemas baobab gddrescue \
	lshw-gtk testdisk curlftpfs nmap cifs-utils time openssh-client \
	rsync reiserfsprogs dosfstools ntfs-3g hfsutils reiser4progs sshfs \
	jfsutils smbclient wget partclone iputils-ping net-tools yad pigz \
	chromium php-cli iptables-persistent

# System settings
perl -p -i -e 's/^set compatible$/set nocompatible/g' /etc/vim/vimrc.tiny

# Prevent chromium "save password" prompts
mkdir -p /etc/chromium/policies/managed
cat > /etc/chromium/policies/managed/no-password-management.json <<END
{
    "AutoFillEnabled": false,
    "PasswordManagerEnabled": false
}
END

# Add regular user
useradd --create-home $USER --shell /bin/bash
adduser $USER sudo
echo '$USER:$USER' | chpasswd

# Prepare single-user system
echo 'root:$USER' | chpasswd
echo 'default_user root' >> /etc/slim.conf
echo 'auto_login yes' >> /etc/slim.conf
echo "Setting default plymouth theme..."
plymouth-set-default-theme -R redo
update-initramfs -u
ln -s /usr/bin/pcmanfm /usr/bin/nautilus

# Save space
rm -f /usr/bin/{rpcclient,smbcacls,smbclient,smbcquotas,smbget,smbspool,smbtar}
rm -f /usr/share/icons/*/icon-theme.cache
rm -rf /usr/share/doc
rm -rf /usr/share/man
EOL
fi

# Setup script: (UPDATE) Open shell to make changes
if [ "$ACTION" == "changes" ]; then
cat >> $ROOT/$FILE << EOL
echo -e "$yel>>> Opening interactive shell. Type 'exit' when done making changes.$off"
echo
bash
EOL
fi

# Setup script: (ALL) Clean up and exit
cat >> $ROOT/$FILE <<EOL
# Clean up and exit
apt-get autoremove && apt-get clean
rm -rf /var/lib/dbus/machine-id
rm -rf /tmp/*
rm -f /etc/resolv.conf
rm -f /etc/debian_chroot
rm -rf /var/lib/apt/lists/????????*
umount -lf /proc;
umount /sys;
umount /dev/pts
exit
EOL

# Copy plymouth theme before running setup script
echo -e "$yel* Copying assets to root directory...$off"
rsync -h --info=progress2 --archive \
	./overlay/$ROOT/usr/share/* \
	./$ROOT/usr/share/

# Copy /etc/resolv.conf before running setup script
cp /etc/resolv.conf ./$ROOT/etc/

# Run setup script inside chroot
chmod +x $ROOT/$FILE
echo
echo -e "$red>>> ENTERING CHROOT SYSTEM$off"
echo
sleep 2
chroot $ROOT/ /bin/bash -c "./$FILE"
echo
echo -e "$red>>> EXITED CHROOT SYSTEM$off"
echo
sleep 2
rm -f $ROOT/$FILE

# Prepare image
echo -e "$yel* Preparing image...$off"
rm -f $ROOT/root/.bash_history
rm -rf image redorescue-$VER.iso
mkdir -p image/{live,isolinux}
cp $ROOT/boot/vmlinuz* image/live/vmlinuz
cp $ROOT/boot/initrd* image/live/initrd
cp /boot/memtest86+.bin image/live/memtest
cp /usr/lib/ISOLINUX/isolinux.bin image/isolinux/
cp /usr/lib/syslinux/modules/bios/menu.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/vesamenu.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/hdt.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/ldlinux.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/libutil.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/libmenu.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/libcom32.c32 image/isolinux/
cp /usr/lib/syslinux/modules/bios/libgpl.c32 image/isolinux/
cp /usr/share/misc/pci.ids image/isolinux/

# Apply changes from overlay
echo -e "$yel* Applying changes from overlay...$off"
rsync -h --info=progress2 --archive \
	./overlay/* \
	.

# Fix permissions
chroot $ROOT/ /bin/bash -c "chown -R root: /etc /root"
chroot $ROOT/ /bin/bash -c "chown -R www-data: /var/www/html"

# Enable startup of Redo monitor service
chroot $ROOT/ /bin/bash -c "chmod 644 /etc/systemd/system/redo.service"
chroot $ROOT/ /bin/bash -c "systemctl daemon-reload; systemctl enable redo"

# Update version number
perl -p -i -e "s/\\\$VERSION/$VER/g" image/isolinux/isolinux.cfg
echo $VER > $ROOT/var/www/html/VERSION

# Compress live filesystem
echo -e "$yel* Compressing live filesystem...$off"
mksquashfs $ROOT/ image/live/filesystem.squashfs -e boot

# Create ISO image
mksquashfs $ROOT/ image/live/filesystem.squashfs -e boot
xorriso -as mkisofs -r \
	-J -joliet-long \
	-isohybrid-mbr /usr/lib/ISOLINUX/isohdpfx.bin \
	-partition_offset 16 \
	-A "Redo $VER" -volid "Redo $VER" \
	-b isolinux/isolinux.bin \
	-c isolinux/boot.cat \
	-no-emul-boot -boot-load-size 4 -boot-info-table \
	-o redorescue-$VER.iso \
	image

# All done
echo -e "$yel\nISO image saved:"
du -sh redorescue-$VER.iso
echo -e "$off\n"
echo
echo "Done."
echo
