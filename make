#!/bin/bash
#
# Redo Rescue: Backup and Recovery Made Easy <redorescue.com>
# Copyright (C) 2010-2023 Zebradots Software
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

VER=5.0.0
BASE=bookworm
ARCH=amd64
ROOT=rootdir
FILE=setup.sh
USER=redo
NONFREE=true

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
if [[ `pwd` == *" "* ]]
	then echo -e "$red* ERROR: Current absolute pathname contains a space.$off\n"
	exit
fi

# Get requested action
ACTION=$1

clean() {
	#
	# Remove all build files
	#
	rm -rf {image,scratch,$ROOT,*.iso}
	echo -e "$yel* All clean!$off\n"
	exit
}

prepare() {
	#
	# Prepare host environment
	#
	echo -e "$yel* Building from scratch.$off"
	rm -rf {image,scratch,$ROOT,*.iso}
	CACHE=debootstrap-$BASE-$ARCH.tar.gz
	if [ -f "$CACHE" ]; then
		echo -e "$yel* $CACHE exists, extracting existing archive...$off"
		sleep 2
		tar zxvf $CACHE
	else 
		echo -e "$yel* $CACHE does not exist, running debootstrap...$off"
		sleep 2
		# Legacy needs: syslinux, syslinux-common, isolinux, memtest86+
		apt-get install debootstrap squashfs-tools grub-pc-bin \
			grub-efi-amd64-signed shim-signed mtools xorriso \
			syslinux syslinux-common isolinux memtest86+
		rm -rf $ROOT; mkdir -p $ROOT
		debootstrap \
			--arch=$ARCH \
			--variant=minbase \
			$BASE $ROOT
		tar zcvf $CACHE ./$ROOT	
	fi

}

script_init() {
	#
	# Setup script: Base configuration
	#
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
}

script_build() {
	#
	# Setup script: Install packages
	#
	if [ "$ARCH" == "i386" ]; then
		KERN="686"
	else
		KERN="amd64"
	fi
	if [ "$BASE" == "bookworm" ]; then
		# Bookworm-specific PHP version and packages
		PHPV="8.2"
		PKGS="chromium-common chromium-sandbox volumeicon-alsa exfatprogs"
	elif [ "$BASE" == "bullseye" ]; then
		# Bullseye-specific PHP version and packages
		PHPV="7.4"
		PKGS="chromium-common chromium-sandbox volumeicon-alsa curlftpfs exfat-utils"
	elif [ "$BASE" == "buster" ]; then
		# Buster uses PHP 7.3
		PHPV="7.3"
		PKGS="chromium-common chromium-sandbox volti obmenu curlftpfs exfat-utils"
	else
		# Stretch uses PHP 7.0
		PHPV="7.0"
		PKGS="volti obmenu curlftpfs exfat-utils"
	fi
	cat >> $ROOT/$FILE <<EOL
# Install packages
export DEBIAN_FRONTEND=noninteractive
apt install --no-install-recommends --yes \
	\
	linux-image-$KERN live-boot systemd-sysv firmware-linux-free sudo \
        vim-tiny pm-utils iptables-persistent iputils-ping net-tools wget \
	openssh-client openssh-server rsync less \
	\
	xserver-xorg x11-xserver-utils xinit openbox obconf slim \
	plymouth plymouth-themes compton dbus-x11 libnotify-bin xfce4-notifyd \
	gir1.2-notify-0.7 tint2 nitrogen xfce4-appfinder xfce4-power-manager \
	gsettings-desktop-schemas lxrandr lxmenu-data lxterminal lxappearance \
	network-manager-gnome gtk2-engines numix-gtk-theme gtk-theme-switch \
	fonts-lato pcmanfm libfm-modules gpicview mousepad x11vnc pwgen \
	xvkbd \
	\
	beep laptop-detect os-prober discover lshw-gtk hdparm smartmontools \
	nmap time lvm2 gparted gnome-disk-utility baobab gddrescue testdisk \
	dosfstools ntfs-3g reiserfsprogs reiser4progs hfsutils jfsutils \
	smbclient cifs-utils nfs-common sshfs partclone pigz yad f2fs-tools \
	exfat-fuse btrfs-progs \
	\
	nginx php-fpm php-cli chromium $PKGS

# Modify /etc/issue banner
perl -p -i -e 's/^D/Redo Rescue $VER\nBased on D/' /etc/issue

# Set vi editor preferences
perl -p -i -e 's/^set compatible$/set nocompatible/g' /etc/vim/vimrc.tiny

# Use local RTC in Linux (via /etc/adjtime) and disable network time updates
systemctl disable systemd-timesyncd.service

# Disable SSH server and delete keys
systemctl disable ssh
rm -f /etc/ssh/ssh_host_*

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

# Configure nginx/php-fpm application server
perl -p -i -e 's/^user = .*$/user = root/g' /etc/php/$PHPV/fpm/pool.d/www.conf
perl -p -i -e 's/^group = .*$/group = root/g' /etc/php/$PHPV/fpm/pool.d/www.conf
perl -p -i -e 's/^ExecStart=(.*)$/ExecStart=\$1 -R/g' /lib/systemd/system/php$PHPV-fpm.service
cat > /etc/nginx/sites-available/redo <<'END'
server {
	listen		80 default_server;
	server_name	localhost;
	root		/var/www/html;
	index		index.php;
	location ~* \.php$ {
		fastcgi_pass	unix:/run/php/php$PHPV-fpm.sock;
		include		fastcgi_params;
		fastcgi_param	SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
		fastcgi_param	SCRIPT_NAME \$fastcgi_script_name;
	}
}
END
rm -f /etc/nginx/sites-enabled/default
ln -s /etc/nginx/sites-available/redo /etc/nginx/sites-enabled/
EOL
}

script_add_nonfree() {
	#
	# Setup script: Install non-free packages for hardware support
	#
	# Non-free firmware does not comply with the Debian DFSG and is
	# not included in official releases.  For more information, see
	# <https://www.debian.org/social_contract> and also
	# <http://wiki.debian.org/Firmware>.
	#
	# WARNING: Wireless connections are *NOT* recommended for backup
	# and restore operations, but are included for other uses.
	#
	cat >> $ROOT/$FILE <<EOL
echo "Adding non-free packages..."
# Briefly activate repos to install non-free firmware packages
perl -p -i -e 's/main$/main non-free non-free-firmware/' /etc/apt/sources.list
apt update --yes
# WARNING: Wireless connections are NOT recommended for backup/restore!
#
# To include firmware, uncomment or add packages as needed here in the
# make script to create a custom image.
#
apt install --yes \
	firmware-linux-nonfree \
	firmware-misc-nonfree \
	firmware-amd-graphics \
	amd64-microcode \
	intel-microcode
perl -p -i -e 's/ non-free non-free-firmware$//' /etc/apt/sources.list
apt update --yes
EOL
}

script_shell() {
	#
	# Setup script: Insert command to open shell for making changes
	#
	cat >> $ROOT/$FILE << EOL
echo -e "$red>>> Opening interactive shell. Type 'exit' when done making changes.$off"
echo
bash
EOL
}

script_exit() {
	#
	# Setup script: Clean up and exit
	#
	cat >> $ROOT/$FILE <<EOL
# Save space
rm -f /usr/bin/{rpcclient,smbcacls,smbclient,smbcquotas,smbget,smbspool,smbtar}
rm -f /usr/share/icons/*/icon-theme.cache
rm -rf /usr/share/doc
rm -rf /usr/share/man

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
}

chroot_exec() {
	#
	# Execute setup script inside chroot environment
	#
	echo -e "$yel* Copying assets to root directory...$off"
	# Copy assets before configuring plymouth theme
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
}

create_livefs() {
	#
	# Prepare to create new image
	#
	echo -e "$yel* Preparing image...$off"
	rm -f $ROOT/root/.bash_history
	rm -rf image redorescue-$VER.iso
	mkdir -p image/live

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
	chroot $ROOT/ /bin/bash -c "systemctl enable redo"

	# Update version number
	echo $VER > $ROOT/var/www/html/VERSION

	# Compress live filesystem
	echo -e "$yel* Compressing live filesystem...$off"
	mksquashfs $ROOT/ image/live/filesystem.squashfs -e boot
}

create_iso() {
	#
	# Create ISO image from existing live filesystem
	#
	if [ "$BASE" == "stretch" ]; then
		# Debian 9 supports legacy BIOS booting
		create_legacy_iso
	else
		# Debian 10+ supports UEFI and secure boot
		create_uefi_iso
	fi
}

create_legacy_iso() {
	#
	# Create legacy ISO image for Debian 9 (version 2.0 releases)
	#
	if [ ! -s "image/live/filesystem.squashfs" ]; then
		echo -e "$red* ERROR: The squashfs live filesystem is missing.$off\n"
		exit
	fi

	# Apply image changes from overlay
	echo -e "$yel* Applying image changes from overlay...$off"
	rsync -h --info=progress2 --archive \
		./overlay/image/* \
		./image/

	# Remove EFI-related boot assets
	rm -rf image/boot

	# Update version number
	perl -p -i -e "s/\\\$VERSION/$VER/g" image/isolinux/isolinux.cfg
	
	# Prepare image
	echo -e "$yel* Preparing legacy image...$off"
	mkdir image/isolinux
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

	# Create ISO image
	echo -e "$yel* Creating legacy ISO image...$off"
	xorriso -as mkisofs -r \
		-J -joliet-long \
		-isohybrid-mbr /usr/lib/ISOLINUX/isohdpfx.bin \
		-partition_offset 16 \
		-A "Redo $VER" -volid "Redo Rescue $VER" \
		-b isolinux/isolinux.bin \
		-c isolinux/boot.cat \
		-no-emul-boot -boot-load-size 4 -boot-info-table \
		-o redorescue-$VER.iso \
		image

	# Report final ISO size
	echo -e "$yel\nISO image saved:"
	du -sh redorescue-$VER.iso
	echo -e "$off"
	echo
	echo "Done."
	echo
}

create_uefi_iso() {
	#
	# Create ISO image for Debian 10 (version 3.0 releases)
	#
	if [ ! -s "image/live/filesystem.squashfs" ]; then
		echo -e "$red* ERROR: The squashfs live filesystem is missing.$off\n"
		exit
	fi

	# Apply image changes from overlay
	echo -e "$yel* Applying image changes from overlay...$off"
	rsync -h --info=progress2 --archive \
		./overlay/image/* \
		./image/

	# Remove legacy boot assets
	rm -rf image/isolinux

	# Update version number
	perl -p -i -e "s/\\\$VERSION/$VER/g" image/boot/grub/grub.cfg

	# Prepare boot image
	touch image/REDO
        cp $ROOT/boot/vmlinuz* image/vmlinuz
        cp $ROOT/boot/initrd* image/initrd
	mkdir -p {image/EFI/{boot,debian},image/boot/grub/{fonts,theme},scratch}
	cp /usr/share/grub/ascii.pf2 image/boot/grub/fonts/
	cp /usr/lib/shim/shimx64.efi.signed image/EFI/boot/bootx64.efi
	cp /usr/lib/grub/x86_64-efi-signed/grubx64.efi.signed image/EFI/boot/grubx64.efi
	cp -r /usr/lib/grub/x86_64-efi image/boot/grub/

	# Create EFI partition
	UFAT="scratch/efiboot.img"
	dd if=/dev/zero of=$UFAT bs=1M count=4
	mkfs.vfat $UFAT
	mcopy -s -i $UFAT image/EFI ::

	# Create image for BIOS and CD-ROM
	grub-mkstandalone \
		--format=i386-pc \
		--output=scratch/core.img \
		--install-modules="linux normal iso9660 biosdisk memdisk search help tar ls all_video font gfxmenu png" \
		--modules="linux normal iso9660 biosdisk search help all_video font gfxmenu png" \
		--locales="" \
		--fonts="" \
		"boot/grub/grub.cfg=image/boot/grub/grub.cfg"

	# Prepare image for UEFI
	cat /usr/lib/grub/i386-pc/cdboot.img scratch/core.img > scratch/bios.img

	# Create final ISO image
	xorriso \
		-as mkisofs \
		-iso-level 3 \
		-full-iso9660-filenames \
		-joliet-long \
		-volid "Redo Rescue $VER" \
		-eltorito-boot \
			boot/grub/bios.img \
			-no-emul-boot \
			-boot-load-size 4 \
			-boot-info-table \
			--eltorito-catalog boot/grub/boot.cat \
		--grub2-boot-info \
		--grub2-mbr /usr/lib/grub/i386-pc/boot_hybrid.img \
		-eltorito-alt-boot \
			-e EFI/efiboot.img \
			-no-emul-boot \
		-append_partition 2 0xef scratch/efiboot.img \
		-output redorescue-$VER.iso \
		-graft-points \
			image \
			/boot/grub/bios.img=scratch/bios.img \
			/EFI/efiboot.img=scratch/efiboot.img

	# Remove scratch directory
	rm -rf scratch

	# Report final ISO size
	echo -e "$yel\nISO image saved:"
	du -sh redorescue-$VER.iso
	echo -e "$off"
	echo
	echo "Done."
	echo
}


#
# Execute functions based on the requested action
#

if [ "$ACTION" == "clean" ]; then
	# Clean all build files
	clean
fi

if [ "$ACTION" == "" ]; then
	# Build new ISO image
	prepare
	script_init
	script_build
	if [ "$NONFREE" = true ]; then
		echo -e "$yel* Including non-free packages...$off"
		script_add_nonfree
	else
		echo -e "$yel* Excluding non-free packages.$off"
	fi
	script_exit
	chroot_exec
	create_livefs
	create_iso
fi

if [ "$ACTION" == "changes" ]; then
	# Enter existing system to make changes
	echo -e "$yel* Updating existing image.$off"
	script_init
	script_shell
	script_exit
	chroot_exec
	create_livefs
	create_iso
fi

if [ "$ACTION" == "boot" ]; then
	# Rebuild existing ISO image (update bootloader)
	create_iso
fi
