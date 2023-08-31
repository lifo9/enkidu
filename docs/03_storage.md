# Storage setup
We'll do this (semi) manually, as we have existing data on the disks. \
They were previously used in MDADM RAID1, with Luks encryption and LVM. \
We'll migrate them to ZFS mirror setup with encryption.

1. Install necessary packages.
   ```shell
   sudo apt install cryptsetup lvm2 mdadm gdisk rsync
   ```
2. Create mountpoint for one of the disks, from which we'll copy data.
   ```shell
   sudo mkdir /mnt/original
   ```
3. Open the original disk with mdadm in degraded mode.
   ```shell
   sudo mdadm --assemble --run /dev/md0 /dev/sda1
   ```
4. Unlock the disk.
   ```shell
   sudo cryptsetup luksOpen /dev/md0 original
   ```
5. Mount the disk.
   ```shell
   sudo mount /dev/mapper/VolGroupRaid-media /mnt/original
   ```
6. Identify ID of the other disk, which will be used for the ZFS pool and save it to `NEW_DISK_ID` variable.
    ```shell
    sudo ls -al /dev/disk/by-id/ | grep sdb
    NEW_DISK_ID="XYZ"
    ```
7. Remove partitions from the new disk. You may need to reboot afterwards for kernel to use the newly created partition table.
   ```shell
   sudo sgdisk --zap-all "/dev/disk/by-id/${NEW_DISK_ID}"
   ```
8. Create new ZFS pool for storage. We have to force creation as it contains a filesystem.
   ```shell
   sudo zpool create -f -O encryption=on -O keyformat=passphrase -m none tank "/dev/disk/by-id/${NEW_DISK_ID}"
   sudo zpool set cachefile=/etc/zfs/zpool.cache tank
   ```
9. Create datasets for the new pool.
   ```shell
   sudo zfs create -o mountpoint=/storage tank/storage

   sudo zfs create -o mountpoint=/storage/backup tank/storage/backup

   sudo zfs create -o mountpoint=/storage/media tank/storage/media

   sudo zfs create -o mountpoint=/storage/nextcloud tank/storage/nextcloud
   ```

10. Update zfs-list.cache.
    ```shell
    sudo touch /etc/zfs/zfs-list.cache/tank
    sudo zfs set canmount=on tank
    sudo chmod 600 /etc/zfs/zfs-list.cache/tank
    sudo  cat /etc/zfs/zfs-list.cache/tank
    ```

11. Rsync the original disk to the new pool.
    ```shell
    nohup sudo rsync --archive --hard-links --acls --xattrs --one-file-system --sparse --no-o --no-g --partial /mnt/original/nextcloud/ /storage/nextcloud &

    nohup sudo rsync --archive --hard-links --acls --xattrs --one-file-system --sparse --no-o --no-g --partial /mnt/original/media/ /storage/media &
    ```
12. Umount the original disk.
    ```shell
    sudo umount /mnt/original
    ```
13. Deactivate volume group.
    ```shell
    sudo vgchange -a n VolGroupRaid
    ```

14. Close the original disk.
    ```shell
    sudo cryptsetup luksClose original
    ```
15. Stop the degraded RAID.
    ```shell
    sudo mdadm --stop /dev/md0
    ```

16. Get old disk ID and save it to `OLD_DISK_ID` variable.
    ```shell
    sudo ls -al /dev/disk/by-id/ | grep sda
    OLD_DISK_ID="XYZ"
    ```

17. Remove partitions from the old disk. You may need to reboot afterwards for kernel to use the newly created partition table.
    ```shell
    sudo sgdisk --zap-all "/dev/disk/by-id/${OLD_DISK_ID}"
    ```

18. Remove now unnecessary packages.
    ```shell
    sudo apt purge mdadm cryptsetup lvm2 gdisk rsync && sudo apt autoremove
    ```

19. Add the original disk to newly created ZFS pool - acting like a mirror.
    ```shell
    sudo zpool attach tank "/dev/disk/by-id/${NEW_DISK_ID}" "/dev/disk/by-id/${OLD_DISK_ID}"
    ```

20. Apply ZFS configuration fixes.
    ```shell
    cd ../ansible/03_storage
    ansible-playbook site.yaml -i inventory.yaml
    ```

21. Regenerate initramfs.
    ```shell
    sudo update-initramfs -v -u -k all
    ```

22. Reboot the system to make sure everything works as expected.
    ```shell
    sudo reboot
    ```

🎉 You can continue to [K3S setup](04_k3s.md).
