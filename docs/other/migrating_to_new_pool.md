# Migrating to new pool
1. Attach new disks.
2. Run `sudo smartctl -t short /dev/sdX` on each new disk.
3. Verify that all disks are healthy with `sudo smartctl -a /dev/sdX`.
4. Create new ZFS pool:
   ```
   ls -l /dev/disk/by-id/ | awk '{print $9, $11}'

   sudo zpool create -O encryption=on -O keyformat=passphrase -m none tank_new mirror "/dev/disk/by-id/${DISK_1}" "/dev/disk/by-id/${DISK_2}" mirror "/dev/disk/by-id/${DISK_3}" "/dev/disk/by-id/${DISK_4}"...
   ```
5. Create snapshot of the old pool:
   ```
   sudo zfs snapshot -r tank@migration
   sudo zfs snapshot tank/storage/backup/kube@migration
   sudo zfs snapshot tank/storage/backup/kube/app_data@migration
   sudo zfs snapshot tank/storage/backup/kube/kube_data@migration
   ```
6. Migrate data from old pool to new pool (we cannot do it recursively due to encryption):
   ```
   sudo zfs send -w -p "tank/storage@migration" | sudo zfs receive -F "tank_new/storage" &

   sudo zfs send -w -p "tank/storage/backup@migration" | sudo zfs receive -F "tank_new/storage/backup" &

   sudo zfs send -w -p "tank/storage/backup/kube@migration" | sudo zfs receive -F "tank_new/storage/backup/kube" &

   sudo zfs send -w -p "tank/storage/backup/kube/app_data@migration" | sudo zfs receive -F "tank_new/storage/backup/kube/app_data" &

   sudo zfs send -w -p "tank/storage/backup/kube/kube_data@migration" | sudo zfs receive -F "tank_new/storage/backup/kube/kube_data" &

   sudo zfs send -w -p "tank/storage/nextcloud@migration" | sudo zfs receive -F "tank_new/storage/nextcloud" &

   sudo zfs send -w -p "tank/storage/media@migration" | sudo zfs receive -F "tank_new/storage/media" &
   ```
7. Fix encryptionroot, so that it points to `tank_new`:
   ```
   sudo zfs load-key tank_new/storage
   sudo zfs change-key -i tank_new/storage
   sudo zfs load-key tank_new/storage/backup
   sudo zfs change-key -i tank_new/storage/backup
   sudo zfs load-key tank_new/storage/backup/kube
   sudo zfs change-key -i tank_new/storage/backup/kube
   sudo zfs load-key tank_new/storage/backup/kube/app_data
   sudo zfs change-key -i tank_new/storage/backup/kube/app_data
   sudo zfs load-key tank_new/storage/backup/kube/kube_data
   sudo zfs change-key -i tank_new/storage/backup/kube/kube_data
   sudo zfs load-key tank_new/storage/media
   sudo zfs change-key -i tank_new/storage/media
   sudo zfs load-key tank_new/storage/nextcloud
   sudo zfs change-key -i tank_new/storage/nextcloud
   ```
8. Destroy `migration` snapshots in the `tank_new`:
   ```
   sudo zfs destroy -r tank_new/storage@migration
   ```
9. Drain k3s node (via Lens app).
10. Unmount the old pool:
    ```
    sudo zfs unmount tank/storage/nextcloud
    sudo zfs unmount tank/storage/media
    sudo zfs unmount tank/storage/backup/kube/kube_data
    sudo zfs unmount tank/storage/backup/kube/app_data
    sudo zfs unmount tank/storage/backup/kube
    sudo zfs unmount tank/storage/backup
    sudo zfs unmount tank/storage
    ```
11. Rename the new pool and mount it:
    ```
    sudo zpool export tank
    sudo zpool export tank_new
    sudo zpool import tank_new tank
    sudo zfs load-key tank
    sudo zfs mount tank/storage
    sudo zfs mount tank/storage/backup
    sudo zfs mount tank/storage/backup/kube
    sudo zfs mount tank/storage/backup/kube/app_data
    sudo zfs mount tank/storage/backup/kube/kube_data
    sudo zfs mount tank/storage/media
    sudo zfs mount tank/storage/nextcloud
    ```
12. Update zfs-list.cache.
    ```
    sudo touch /etc/zfs/zfs-list.cache/tank
    sudo zfs set canmount=on tank
    sudo chmod 600 /etc/zfs/zfs-list.cache/tank
    sudo  cat /etc/zfs/zfs-list.cache/tank
    ```
13. Regenerate initramfs:
    ```
    sudo update-initramfs -u -k all
    ```
14. Reboot.
15. Uncordon k3s node (via Lens app).
