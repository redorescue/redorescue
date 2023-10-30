# Redo Rescue

<p align="center">
  <a href="http://redorescue.com/"><img width="100%" src="http://redorescue.com/images/header.png"></a>
</p>

[![Download Redo Rescue: Backup and Recovery](https://img.shields.io/sourceforge/dt/redobackup.svg)](https://sourceforge.net/projects/redobackup/files/latest/download) [![Download Redo Rescue: Backup and Recovery](https://img.shields.io/sourceforge/dw/redobackup.svg)](https://sourceforge.net/projects/redobackup/files/latest/download)


## About

**Redo Rescue** (also known as [Redo Backup](https://github.com/redobackup/redobackup)) is a live CD/USB system that creates and restores snapshots of your system. Restore the image, even to a new blank drive, and recover in minutes from ransomware and viruses, deletions, hardware damage, and hackers.

**This is the official Redo project developed since 2010 &mdash; now maintained on GitHub!**


## Screenshots

<div>
  <p align="center">
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/boot.jpg"></a>&nbsp;
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/welcome.jpg"></a>
  </p>
  <p align="center">
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/backup-progress.jpg"></a>&nbsp;
    <a href="http://redorescue.com"><img src="http://redorescue.com/images/screenshots/thumbs/detailed-logs.jpg"></a>
  </p>
</div>

<p align="center"><b>For more screenshots and project details visit http://redorescue.com</b></p>

## Features

  * Over 2.3 million downloads
  * Free and open source software
  * Create a backup image in a few clicks
  * Live system; works on machines that won't even boot
  * Provides VNC access for remote assistance
  * Bare-metal recovery restores master boot record, partition table
  * Selectively restore certain parts
  * Optionally re-map original partitions to different places
  * UEFI Secure Boot support
  * Based on 64-bit Debian Linux
  * ISO can be written to CD or USB
  * Error handling and low space warnings
  * Detailed logs can be copied to clipboard
  * Supports restoring images made with v1.0.4 release
  * Browser-based application with PHP backend
  * Beautiful user interface
  * System tools and diagnostic programs included in image
  * Unified backup file format with ability to add notes
  * Shared network drive search and detection
  * Support for various block devices
  * Read/write support for Samba/CIFS shares, NFS shares, SSH filesystems, and FTP servers


## Download

**The latest ISO image can be downloaded from SourceForge:**

[![Download Redo Rescue: Backup and Recovery](https://a.fsdn.com/con/app/sf-download-button)](https://sourceforge.net/projects/redobackup/files/latest/download)


## Examples

Redo Rescue can be used in countless ways to recover from disaster, replicate a system, or just set things back to how they were before. Here are some example use cases:

* You've installed and activated Windows, configured all the necessary drivers, and installed an office suite for a family member. Because of the time involved, you don't want to have to repeat this process again. Use Redo Rescue to save a backup image to a USB stick in case the hard drive crashes.

* A teacher has dozens of identical machines in her classroom that run the same Linux-based operating system. She can use Redo Rescue to make a snapshot image of the working system, so that if a student's system becomes unusable she can easily restore the system to working condition in minutes.

* A company laptop has many different software components that require tedious configuration, and its users are more likely to click on links to malware or viruses. Use Redo Rescue to resize the existing partitions, create a backup partition on the same drive, and save a backup image.

* An office server needs to be upgraded with all new hardware, but the old system needs to stay running while the replacement is prepared. Use Redo Rescue to create an image, restore it to new hardware, and then make the switch with minimal downtime, while preserving the old machine in case of failure.


## Warning

**Redo Rescue is designed to restore a backup image to the same system it was taken from.** Even a byte-for-byte clone of a Windows drive to a target system that is nearly identical may fail to boot, regardless of the backup software used. Certain system changes can easily render a Windows, Mac, or Linux machine unbootable: changing hardware components, adding/removing/swapping disks, making significant configuration changes, or restoring to a different machine are all likely to cause boot issues. Similarly, swapping, moving, resizing, or reordering partitions will almost certainly render most operating systems unbootable. After such changes, an entire backup can be restored successfully (and all files are safely stored on the drive), yet the operating system may fail to boot. _This is not a limitation of the backup solution, but the result of changes to the system configuration. We strongly recommend creating a new backup image after changes are made to your system._

Redo Rescue relies on [sfdisk](https://manpages.debian.org/stretch/util-linux/sfdisk.8.en.html) to backup and restore partition tables, and [partclone](https://manpages.debian.org/stretch/partclone/partclone.8.en.html) to create and restore backups of the data on each partition. Both are considered very reliable but could contain unknown bugs.


## Notes

* By default the system logs in as the `root` user with password `redo`.


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

**Redo Rescue** is released under the GNU GPLv3. Redo Rescue's distinctive logos and graphics are not released under the GNU GPLv3. If you would like to create a fork of Redo Rescue, you must create distinctly different graphic assets to identify it.
