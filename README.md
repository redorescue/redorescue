# Redo Rescue

<p align="center">
  <a href="http://redorescue.com/"><img width="100%" src="http://redorescue.com/images/header.png"></a>
</p>

[![Download Redo Rescue: Backup and Recovery](https://img.shields.io/sourceforge/dt/redobackup.svg)](https://sourceforge.net/projects/redobackup/files/latest/download) [![Download Redo Rescue: Backup and Recovery](https://img.shields.io/sourceforge/dw/redobackup.svg)](https://sourceforge.net/projects/redobackup/files/latest/download)


## About

**Redo Rescue** is a live CD/USB system that creates and restores snapshots of your system. Restore the image, even to a new blank drive, and recover in minutes from ransomware and viruses, deletions, hardware damage, and hackers.

The original since 2010&mdash;now with more Github!


## Screenshots

<div>
  <p align="center">
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/welcome.jpg"></a>&nbsp;
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/backup-progress.jpg"></a>&nbsp;
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/detailed-logs.jpg"></a>
  </p>
</div>

<p align="center"><b>For more screenshots and project details visit http://redorescue.com</b></p>

## Features

  * Over 2 million downloads
  * Free and open source software
  * Create a backup image in a few clicks
  * Live system; works on machines that won't even boot
  * Provides VNC access for remote assistance
  * Bare-metal recovery restores master boot record, partition table
  * Selectively restore certain parts
  * Optionally re-map original partitions to different places
  * Based on 64-bit Debian Linux
  * 400M ISO can be written to CD or USB
  * Error handling and low space warnings
  * Detailed logs can be copied to clipboard
  * Supports restoring images made with v1.0.4 release
  * Browser-based application with PHP backend
  * Beautiful user interface
  * System tools and diagnostic programs included in image
  * Unified backup file format with ability to add notes
  * Shared network drive search and detection
  * Support for various block devices
  * Read/write support for Samba/CIFS shares, SSH filesystems, and FTP servers


## Download

#### The latest 2.0 releases are still considered BETA. Please test, but don't use in production yet!

The latest ISO image can be downloaded from SourceForge:

[![Download Redo Rescue: Backup and Recovery](https://a.fsdn.com/con/app/sf-download-button)](https://sourceforge.net/projects/redobackup/files/latest/download)


## Warning

Redo Rescue relies on [sfdisk](https://manpages.debian.org/stretch/util-linux/sfdisk.8.en.html) to backup and restore partition tables, and [partclone](https://manpages.debian.org/stretch/partclone/partclone.8.en.html) to create and restore backups of the data on each partition. Both are considered very reliable but could contain unknown bugs.

Redo Rescue is designed to restore a backup image to the same system it was taken from. Even a byte-for-byte clone of a Windows drive to a target system that is nearly identical may fail to boot, regardless of the backup software used. Certain system changes can easily render a Windows, Mac, or Linux machine unbootable: changing hardware components, adding/removing/swapping disks, making significant configuration changes, or restoring to a different machine are all likely to cause boot issues. Similarly, swapping, moving, resizing, or reordering partitions will almost certainly render most operating systems unbootable. After such changes, an entire backup can be restored successfully (and all files are safely stored on the drive), yet the operating system may fail to boot. _This is not a limitation of the backup solution, but the result of changes to the system configuration._ **We strongly recommend creating a new backup image after changes are made to your system.**


## Build

To build an ISO image from within Debian Linux:

  1. `git clone https://github.com/redorescue/redorescue.git`
  2. `cd redorescue`
  3. `sudo ./make`

After building, it's easy to modify a file or install a package without rebuilding and downloading all the packages again:

  1. `sudo ./make changes`
  1. Make your changes to the live system image
  1. `exit` and the ISO will be updated automatically

Source code for previous releases can be found on [SourceForge](https://sourceforge.net/projects/redobackup/files/src/).


## License

**Redo Rescue** is released under the GNU GPLv3.


## Changelog

### Version 2.0.2 (2020-06-25)
  * Prevent application from hanging when mounting certain partition types
  * Moved to nginx for application web server to resolve mount issues
  * Support network shared folders that have a space in the share name
  * Quote passwords provided for SMB mounts
  * More user friendly descriptions on tabs of restore options
  * Added prominent warning regarding remapping to selective restore tab
  * Fixed several minor user interface quirks

### Version 2.0.1 (2020-06-19)
  * Added feature to verify the integrity of a backup image
  * Fixed bug that caused application to hang when mounting NTFS partitions
  * Improved make script with colorized output for better readability
  * Changed image file format to permit triple-digit numeric suffixes
  * Should now permit a compressed partition image to be up to 4 terabytes
  * Removed unusued assets that made Dependabot complain

### Version 2.0.0 (2020-06-11)
  * Based on 64-bit Debian stretch
  * More support for modern hardware
  * ISO naming convention changed to simply "redorescue-X.X.X.iso"
  * Newer, more reliable version of partclone suite
  * Complete rewrite of user interface and underlying logic
  * Enhanced UX via simplified interface with tooltips
  * Application now PHP+HTML+Javascript rather than Perl+GTK
  * Vastly improved error handling and reporting
  * Color-coded free space meter to monitor destination drive during backup
  * Toggle password visibility when mounting network shares
  * Support for SSH filesystems (now SMB/CIFS, SSH, and FTP supported)
  * Restore from drives with no partitions (e.g. CD/DVD)
  * New password-protected VNC server to allow remotely-assisted operation
  * Optional detailed log view with copy-to-clipboard button
  * Added support for restoring from older v1.0.X backup format
  * New unified backup file format (.redo) includes MBR and PT information
  * Optionally add user notes to a backup image
  * Bare metal restore: restores MBR, partition table, and selected partitions
  * Selective restore: preserves MBR and partition, restores selected parts
  * Added option to remap source images to new target partitions
  * ISO image (approx. 400M) can now be written directly to USB stick
  * Improved support for various block devices and drive types (e.g. USB)
  * Better handling of drive and partition identifiers
  * Version number now displayed at boot and at bottom of application
  * Many other bug fixes and features added

### Version 1.0.4 (2012-11-20)
  * Base upgrade to Ubuntu 12.04 LTS (Precise)
  * Percent complete now based on part sizes rather than total number of parts
  * Windows now have titlebars to ease minimizing, maximizing and closing
  * Time is now synced to localtime (hardware clock) after boot
  * Widget theme changed to Bluebird for Gtk3 compatibility
  * Now has a helpful beep to indicate when long processes are finished
  * Added alsamixergui to enable mixer button on volume control
  * Drive reset utility can now operate on multiple drives simultaneously
  * Removed synaptic and boot-repair packages to reduce image size

### Version 1.0.3 (2012-05-10)
  * Restore now overwrites MBR and partition table upon completion

### Version 1.0.2 (2012-01-03)
  * Updated to latest partclone stable binaries
  * Shorten dropdown menus with an ellipsis after certain character limit
  * Ubuntu Maverick repos for updates and backports added; base upgraded
  * Chromium browser launched with user data dir specified (to run as root)
  * Show time elapsed when backup/restore operations are completed
  * Added boot-repair tool for correcting any boot issues after restore
  * Added wget utility for easily downloading files from the command line
  * Show free space on destination drive while saving a backup
  * Warn if less than 1GB free on backup destination drive
  * Show an error if any of the partitions to restore do not exist
  * Allow spaces in network shared folders

### Version 1.0.1 (2011-08-09)
  * LVM2 support added
  * Fixed HFS+ bug that prevented the proper partclone tool from being called
  * Minor changes to boot menu
  * Safe mode boot option now prompts user to select a valid video mode

### Version 1.0.0 (2011-07-01)
  * Added the wodim package for command-line CD burning
  * Password boxes now display hidden characters when typed in
  * Increased boot delay for machines that are slow to display it
  * Changed default boot option to load the system into RAM with "toram"
  * Changed safe video mode to use "xforcevesa nomodeset"
  * Updated the boot help text to provide info about Ubuntu boot options
  * Removed enhanced security erase option in drive reset tool for reliability

### Version 0.9.9 (2011-06-10)
  * Added missing ntfs-3g package to allow saving backups to NTFS drives
  * Version number can be found in bottom left after booting into GUI

### Version 0.9.8 (2011-03-10)
  * Major platform shift; building from Ubuntu rather than xPUD in the future
  * Many base features not directly related to backup/restore have changed
  * Added boot menu option to check CD media for defects
  * Added performance enhancement section to /etc/smb.conf
  * Updated fsarchiver and partclone binaries to latest stable versions
  * Boot splash screen now displays version number for easy identification

### Version 0.9.7 (2010-09-22)
  * Added autorun.exe to help Windows users realize that a reboot is needed
  * Changed color of UI background from orange to soft blue
  * Copied VERSION and LICENSE files to root of CD-ROM for easier access

### Version 0.9.6 (2010-08-28)
  * Fixed: Backup required scanning net before specifying a share manually
  * Fixed: Verification for drive reset can detect success or failure
  * Fixed: Missing rsync CLI dependencies have been added to the live CD image
  * Modified the bookmarks, labels and links in the UI
  * Marked wireless features as unsupported in the UI (experimental only)
  * Default boot option uses the fbdev driver in 1024x768 (16-bit) mode
  * Removed unused boot modes (e.g. command line mode)
  * Boot screen wait time reduced to 5 seconds
  * All packages moved to the "core" image file for simplicity
  * Added the grsync graphical utility for incremental file transfers
  * Added the scp tool for secure transfer of files via SSH
  * Added the very powerful gnome-disk-utility (palimpsest)
  * Added support for encrypted volumes with cryptsetup
  * Added GUI lshw-gtk tool to easily identify computer hardware components
  * Added the baobab graphical disk usage tool

### Version 0.9.5 (2010-08-08)
  * Major speed improvements; backups and restores now 4x faster
  * Standalone gzip binary allows the compression level to be specified
  * Updated partclone to version 0.2.13
  * Added the smartmontools "smartctl" CLI for monitoring drive health
  * Back to using syslinux from the standard Ubuntu 9.10 repo version
  * Only one isolinux.cfg/syslinux.cfg file to maintain

### Version 0.9.4 (2010-08-02)
  * New option to manually specify a shared folder or FTP server
  * Allow retry if network mount fails or bad password is provided
  * Warning: New backup naming convention allows dashes, not underscores
  * Created /opt/backup to hold backup components (instead of using /opt/core)
  * ISO CD-ROM label changed to "Redo Backup"
  * Suppress umount error messages when they aren't really errors
  * Added testdisk_static for recovering partition tables and MBRs
  * Added rsync for copying files with a progress indicator
  * Default boot option now works with any VESA-compatible video card
  * Simplified boot menu focused on hardware support instead of languages
  * Added F1 option to boot menu to display helpful options and information
  * Cancel button kills any running backup/restore processes before exiting
  * Hotplug scripts removed at boot time to stop automounting (for gparted)
  * USB installer upgraded to syslinux-3.86, forcibly writes mbr.bin to device
  * USB installer now creates FAT32 partition and filesystem instead of FAT16
  * Optionally search for network shares on demand, rather than automatically
  * Compatibility fixes and UI improvements to factory drive reset tool
  * Added reiserfsprogs, reiser4progs and mcheck for more filesystem support

### Version 0.9.3 (2010-07-04)
  * Warning: Not interoperable with images from previous versions
  * Updated partclone to version 0.2.12
  * Save partclone error log to /partclone.log during each operation
  * Split backup images into 2GB files rather than saving one giant file
  * Added GZIP compression to reduce size of backup image
  * Backup saves first 32768 bytes rather than 512 when imaging MBR
  * Partition list saved to *.backup instead of *.redo
  * Fixed missing nmap dependencies so that local FTP servers are found
  * USB installation now detects if CD is in /dev/scd0 or /dev/sdc1
  * Stronger warning before overwriting all data to destination drive
  * Decision to abort restoration now aborts (continued either way before)
  * Abort restore if destination drive is smaller than the original
  * Do not allow partition being saved to be selected as the destination
  * Warn when restoring to the same drive the backup image is being read from
  * Minor graphic adjustment to title image
  * Removed kernel boot option "quiet" so users can see it is booting
  * Removed kernel boot option "noisapnp" (added by default in xPUD project)
  * Splash screen implemented on USB stick installations
  * Modified boot menu appearance and help text

### Version 0.9.2 (2010-06-24)
  * Initial release
